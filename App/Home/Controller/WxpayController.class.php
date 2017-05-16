<?php
namespace Home\Controller;
use Think\Controller;
/**
 * ============================================================================
 * Author：高硕
 * ============================================================================
 * 微信支付控制器
 */
class WxpayController extends Controller {
   public function _initialize() {
		vendor('Log.log');
		vendor('Wxpay.Exception');
		vendor('Wxpay.Config');
		vendor('Wxpay.Data');
		vendor('Wxpay.Api');
		vendor('Wxpay.Notify');
		vendor('Wxpay.JsApiPay');
    }
    public function index($examid, $cost){
    	$tools = new \JsApiPay();
    	$openId = $tools->GetOpenid();
    	$input = new \WxPayUnifiedOrder();
    	$input->SetBody("点餐支付");
		$input->SetAttach("点餐支付");
		$input->SetOut_trade_no(date('YmdHis').$examid);
		$input->SetTotal_fee($cost);
		$input->SetTime_start(date("YmdHis"));
		$input->SetTime_expire(date("YmdHis", time() + 600));
		$input->SetGoods_tag("点餐支付");
		//$input->SetNotify_url(\WxPayConfig::NOTIFY_URL);
		$input->SetTrade_type("JSAPI");
		$input->SetOpenid($openId);
		$order = \WxPayApi::unifiedOrder($input);
		$jsApiParameters = $tools->GetJsApiParameters($order);
		return $jsApiParameters;
		//$this->jsApiParameters = $jsApiParameters;
		//$this->display();
    }
    public function notifyurl(){
		vendor('Wxpay.Notifyurl');
		$notify = new \PayNotifyCallBack();
		$notify->Handle(false);
		if($notify->getreturn()){
			$errormsg ='';
			$logHandler= new \CLogFileHandler("Public/logs/Wxpay/".date('Y-m-d').'.log');
			$log = \Log::Init($logHandler, 15);
			$data = $notify->getdata();
			$paydata['transaction_id'] = $data['transaction_id'];
			$paydata['openid'] = $data['openid'];
			$paydata['payment'] = 1;
			$paydata['total_fee'] = $data['total_fee'];
			$paydata['out_trade_no'] = $data['out_trade_no'];
			$paydata['time_end'] = timeformat($data['time_end']);
			$pay = M('pay')->add($paydata);
			if($pay){
				\Log::DEBUG("success to insert pay information to mysql:".json_encode($pay));
			}else{
				$errormsg = $errormsg."交易记录添加失败;";
				\Log::DEBUG("fail to insert pay information to mysql:".json_encode($pay));
			}
			$tryout = M('tryout');
			$user = M('user');
			$userinfo = $user->where('wechatid ="%s"',$data['openid'])->field('name')->select();
			if(empty($userinfo)){
				$errormsg = $errormsg."该微信号未登记用户名;";
				\Log::DEBUG("this wechatid is not register:".$data['openid']);
			}
			if($errormsg == ''){
				$tryoutdata['status'] = 1;
			}else{
				$tryoutdata['status'] = -1;
			}
			
			$tryoutdata['paytime'] = $paydata['time_end'];
			$tryoutdata['transaction_id'] = $data['transaction_id'];
			$tryoutdata['errormsg'] = $errormsg;
			$examid = substr($data['out_trade_no'], 14);
			$res = $tryout->where('id = %d AND wechatid = "%s"',$examid,$data['openid'])->save($tryoutdata);

			if($res){
				\Log::DEBUG("success to update pay information for user:".json_encode($userinfo));
			}else{
				$errormsg = $errormsg."更新用户交易记录失败，wechatid不匹配;";
				$tryoutdata['status'] = -1;
				$tryoutdata['errormsg'] = $errormsg;
				$res = $tryout->where('id = %d',$examid)->save($tryoutdata);
				
				\Log::DEBUG("fail to insert pay information for user:".$data['openid']);
			}
		}
    }
    public function refund(){
            $transaction_id = $_GET['transaction_id'];
            $total_fee = '1';
            $refund_fee = '1';
            $input = new \WxPayRefund();
            $input->SetTransaction_id($transaction_id);
            $input->SetTotal_fee($total_fee);
            $input->SetRefund_fee($refund_fee);
            $input->SetOut_refund_no(\WxPayConfig::MCHID.date("YmdHis"));
            $input->SetOp_user_id(\WxPayConfig::MCHID);
            $data = \WxPayApi::refund($input);
            $logHandler= new \CLogFileHandler("Public/logs/Wrefund/".date('Y-m-d').'.log');
            $log = \Log::Init($logHandler, 15);
            \Log::DEBUG("begain notify");
            \Log::DEBUG("refund data:" . json_encode($data));
            if($data["result_code"] == "SUCCESS"){
                $tryout = M('tryout');
                $data['status'] = 3;
                $res = $tryout->where('id ='.$_GET['examid'])->save($data);
                if($res){
                        $this->assign('jumpUrl',U('Admin/refund'));
                        \Log::DEBUG("result:退款成功");
                        $this->success('退款成功');
                }else{
                        $this->assign('jumpUrl',U('Admin/refund'));
                        \Log::DEBUG("result:退款失败");
                        $this->error('退款失败');
                }
            }else{
                $this->assign('jumpUrl',U('Admin/refund'));
                \Log::DEBUG("result:退款失败".$data['err_code_des']);
                $this->error($data['err_code_des']);
            }
    }
}