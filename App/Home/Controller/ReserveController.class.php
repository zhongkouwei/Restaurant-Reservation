<?php
namespace Home\Controller;
use Think\Controller;
/**
 * ============================================================================
 * Author：高硕
 * ============================================================================
 * 订餐控制器
 */
class ReserveController extends Controller {
    public function index(){
        // 通过微信打开该页面即可获得用户的信息，浏览器直接访问页面不会获得用户信息

        // $info数组中的openid就是wechat_id
        // getWechatInfo函数卸载App/Home/Common/function.php 中
        
        // if(empty($_SERVER['QUERY_STRING'])){
        //    header("Location:https://open.weixin.qq.com/connect/oauth2/authorize?appid=wx439a3874a5b28c4f&redirect_uri=http%3A%2F%2Fmyshworks.8866.org%2Fgaoshuo%2Findex.php%2FHome%2FReserve%2F&response_type=code&scope=snsapi_userinfo&state=#wechat_redirect");
        //    return;
        // }
        //$info = getWechatInfo();
        $wechatid               = "";
        
        $_SESSION['wechatid']=$wechatid;

        $restaurant_id = "1";		//通过GET获得餐馆id
        $user2 = M('restaurant');
        $restaurant_name  = $user2->field('name')->where('id = "%s"',$restaurant_id)->select();
        $_SESSION['restaurant_name'] = $restaurant_name;
        $_SESSION['restaurant_id'] =$restaurant_id;

        $user = M('client');
        $phone = $user->field('phone')->where('wechat_id ="%s"',$wechatid)->select();
        $client_id = $user->field('id')->where('wechat_id ="%s"',$wechatid)->select();
        $_SESSION['client_id'] = $client_id['0']['id'];        
        if(empty($phone)){
            //如果不存在，则跳转到绑定界面进行绑定
            $this->redirect('Reserve/bind');

        }else{
            //如果存在，将号码和当前日期，餐馆名和菜单传递过去。
            $this->assign('phone',$phone);
            $time = time();
            $date= date("20y-m-d",$time);
            $this->assign('date',$date);            
            $this->restaurant_name = $restaurant_name['0']['name']; 
            $users = M('dishes');
            $this->list = $users->where('restaurant_id = "%s"',$restaurant_id)->select();
            $this->display();
        }
         
    }



    public function bind(){
		$this->display();     
	}

    public function Binding(){

        if(!IS_POST) E('无法访问');
        $username = I('post.name');
        $phone = I('post.phone');
        $wechatid = $_SESSION['wechatid'];
        
        if (empty($username)||empty($phone)) {
            $this->error("值不能为空");
            $this->redirect('Reserve/bind');
        } else {
            $user=M("client");
            $user->name=$username;
            $user->phone=$phone;
            $user->wechat_id=$wechatid;
            $user->add();

            $this->redirect('Reserve/index');
        }
	}


    public function About(){
        //about函数用来查询时间段内的空闲桌子
        //if(!IS_POST) E('无法访问');
        
        $date=I('post.input_date');
        $phone=I('post.input_phone');
        $time=I('post.select');
        $cost=I('post.cost');
        $total=$_POST;
        //将已点的菜转换为JSON数组
        $id =array_keys($total);
        $arrlength=count($id);
        for($x=0;$x<$arrlength;$x++) {
            if ($id[$x]>0) {
                $i= $id[$x];
                $dish[$i]=$total[$i];
            }     
        }
    
        $data["date"] = $date;
        $data["time"] =$time;
        $data["phone"] = $phone;
        $data["dishes_id"] = json_encode($dish);
        $data["cost"] = $cost;
         // 数据
        $_SESSION['data'] = $data;

        //数据库中查询出已经预定的桌子并传递给页面
        $Reserve = M('detail');
        $condition['date'] = $date;
        $condition['time'] = $time;     
        $list = $Reserve->field('desk_id')->where($condition)->select();

        //查询分布图传递给页面
        $Restaurant = M('restaurant');
        $map=$Restaurant->field('desk_map')->where('id="%s"',$_SESSION['restaurant_id'])->select();
        $this->map=$map;
        $this->list=json_encode($list);
        $this->restaurant_name = $_SESSION['restaurant_name']['0']['name'];
        $this->display();
        
         
    }
    public function Reserve (){

        if(!IS_POST) E('无法访问');
        $deskid= I('post.deskid');//选座数组

        $User = M("detail");
        $data = $_SESSION['data']; 
        $data['desk_id'] = $deskid;
        $data['client_id'] = $_SESSION['client_id'];
        if (empty($deskid)) {
            $this->error('未选择座位！请重新选择');
            $this->redirect('Reserve/index');
        } else {
            if ($lastInsId = $User->add($data)) {
                $_SESSION['id'] = $lastInsId;
                $this->success('预约成功，请付款！');
                $weixin = A('weixin');
				$weixin ->send_templete_order($_SESSION['wechatid'], $_SESSION['restaurant_name'], $_SESSION['data']['cost'], $_SESSION['data']['time']);
            } else {
                $this->error('数据写入错误！请重新选择');
                $this->redirect('Reserve/index');
            }
        }       
    }

    public function Show(){
        $Restaurant = M('restaurant');
        $data=$Restaurant->where('id="%s"',$_SESSION['restaurant_id'])->select();
        $this->name=$data[0]["name"];
        $this->phone=$data[0]['phone'];
        $this->address=$data[0]['address'];
        $this->display();
    }
}


?>