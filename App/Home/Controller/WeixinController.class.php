<?php
namespace Home\Controller;
use Think\Controller;
/**
 * ============================================================================
 * Author：高硕
 * ============================================================================
 * 用来接收微信服务器的消息进行处理。
 * 微信控制器
 */
class WeixinController extends Controller {

    /*
    * 微信服务器消息处理入口
    *
    */
    public function index(){
    	$options = array(
			'token'=>'weixin', //填写你设定的key
        	'encodingaeskey'=>'encodingaeskey' //填写加密用的EncodingAESKey，如接口为明文模式可忽略
		);
		$weObj = new \Org\Util\Wechat($options);
		$this->write_data($weObj);	//将收到的消息写入数据库
		//$weObj->valid();//明文或兼容模式可以在接口验证通过后注释此句，但加密模式一定不能注释，否则会验证失败
		$type = $weObj->getRev()->getRevType();
		//logtext($type);
		switch($type) {
			case $weObj::MSGTYPE_TEXT:
					$content = $weObj ->getRev()->getRevContent();
					$reply = $this->text_reply($content);
					$weObj->text($reply)->reply();
					exit;
					break;
			case $weObj::MSGTYPE_EVENT:
					break;
			case $weObj::MSGTYPE_IMAGE:
					break;
			case $weObj::MSGTYPE_VOICE:
					break;
			case $weObj::MSGTYPE_LOCATION:
					$array = $weObj->getRev()->getRevGeo();
					$reply = $this->location_reply($array);
                    $weObj->news($reply)->reply();
					break;
			default:
					$weObj->text("你发的啥玩意~[嘿哈]")->reply();
		}
    }

    /**
    * 回复文本消息
    * @param string $text
    */
    public function text_reply($text){
        // 如果收到表情，回复表情[嘿哈][嘿哈][嘿哈][嘿哈][嘿哈]
        if(!empty(strstr($text, '[')) || !empty(strstr($text, '/:'))){
            return "[嘿哈][嘿哈][嘿哈][嘿哈][嘿哈]";
        }

        // 收到 吃饭 点餐 等回复
        if(!empty(strstr($text, '饭')) || !empty(strstr($text, '餐')) || !empty(strstr($text, '饿'))){
            return "发送您的位置，可获得附近餐厅[嘿哈]\n===============================\n您在“重庆”，推荐餐馆有： [嘿哈]\n===============================\n或直接访问<a href = 'http://gscoder.cn/Reserve/index.php'>主页</a>自己选择[嘿哈]";
        }

        return $text;
    }

    /**
    * 回复地理位置消息
    * @param array $array  $weObj->getRev()->getRevGeo();
    * @return array 图文消息结构
    */
    public function location_reply($array){
        $x  = $array['x'];
        $y  = $array['y'];
        $scale  = $array['scale'];
        $label  = $array['label'];

        $description = "您所在的经度：".$x."，纬度：".$y."。您在：".$label;

        $reply = array(
                                "0"=>array(
                                        'Title'=>'图文',
                                        'Description'=>'summary text',
                                        'PicUrl'=>'http://pic33.nipic.com/20130916/3420027_192919547000_2.jpg',
                                        'Url'=>'http://www.domain.com/1.html'
                                    )
                            );

        return $reply;
    }

    /**
    * 将每次接收的消息写入数据库
    * @param object $weObj = new Wechat($options)
    */
    public function write_data($weObj){
    	$Message = M('message');
    	$data['FROM_ID']	= $weObj ->getRev()->getRevFrom();
    	$data['TO_ID']		= $weObj ->getRev()->getRevTo();
    	$data['MESSAGE_TYPE']		= $weObj->getRev()->getRevType();
    	// 根据消息类型写入不同内容
    	switch ($data['MESSAGE_TYPE']) {
    	 	case $weObj::MSGTYPE_TEXT:
    	 		$data['CONTENT']	= $weObj ->getRev()->getRevContent();
    	 		break;
    	 	
    	 	case $weObj::MSGTYPE_IMAGE:
				$value	= $weObj ->getRev()->getRevPic();
				$data['CONTENT']	=$value['picurl'];
				break;

			case $weObj::MSGTYPE_VOICE:
				$value	= $weObj ->getRev()->getRevVoice();
				$data['CONTENT']	=$value['format'];
				break;  

			case $weObj::MSGTYPE_LOCATION:
				$value = $weObj ->getRev()->getRevGeo();
				$data['CONTENT']	= $value['label'];
				break;

    	 	default:
    	 		$data['CONTENT']	="无";
    	 		break;
    	 } 	
    	$data['TIME']		= date('y-m-d h:i:s',time());
    	$Message ->add($data);	
    }


/**========================================= 主动接口 可被调用=====================================**/
	/**
	 * 私有接口：获取$weObj
	 *
	 */
	 private function get_weObj(){
		$options = array(
            'token'=>'weixin', //填写你设定的key
            'encodingaeskey'=>'encodingaeskey', //填写加密用的EncodingAESKey，如接口为明文模式可忽略
			'appid'=>'wxf5c7d2fab5e607a0', // 高级功能调用的appid
			'appsecret'=>'96a950177fbaab6d787c5073bad73f72' // 高级功能调用的appsecret
        );
        $weObj = new \Org\Util\Wechat($options);
		return $weObj;		 
	 }

    /**
     * 主动接口：设置菜单
     * $array
     */
    public function create_menu(){
        $weObj = $this->get_weObj();
        $data = array (
           'button' => array (
             0 => array (
               'name' => '扫码',
               'sub_button' => array (
                   0 => array (
                     'type' => 'scancode_waitmsg',
                    'name' => '扫码带提示',
                     'key' => 'rselfmenu_0_0',
                   ),
                   1 => array (
                     'type' => 'scancode_push',
                     'name' => '扫码推事件',
                     'key' => 'rselfmenu_0_1',
                   ),
               ),
             ),
             1 => array (
               'name' => '发图',
               'sub_button' => array (
                   0 => array (
                     'type' => 'pic_sysphoto',
                     'name' => '系统拍照发图',
                     'key' => 'rselfmenu_1_0',
                   ),
                   1 => array (
                     'type' => 'pic_photo_or_album',
                     'name' => '拍照或者相册发图',
                     'key' => 'rselfmenu_1_1',
                   )
               ),
             ),
             2 => array (
               'type' => 'location_select',
               'name' => '发送位置',
               'key' => 'rselfmenu_2_0'
             ),
           ),
       );
        $weObj -> createMenu($data);
    }
	
	/*
	 * 主动接口：发送模板消息：已预约
	 *
	 */
	public function send_templete_order($openid, $restaurant, $price, $time){
		$weObj = $this->get_weObj();
		$data = array(
			'touser'=>$openid,
			'template_id'=>"el7PXjc7LEqUQzcyhz4vJRM7GCfSKGaoLciWhp9TwVY",
			'url'=>"http=>//weixin.qq.com/download",
			'topcolor'=>"#FF0000",
			'data'=>array(
				'restaurant'=>array(
					'value' => $restaurant,
					'color' => "#173177"
				),
				'price' => array(
					'value' => $price,
					'color' => "#173177"
				),
				'time' => array(
					'value' => $time,
					'color' => "#173177"
				)
			)
		);
		$weObj->sendTemplateMessage($data);
	}
	
	/*
	 *主动接口：发送模板消息：已消费
	 *
     */
	public function send_templete_spend($openid, $restaurant, $price, $time){
		$weObj = $this->get_weObj();
		$data = array(
			'touser'=>$openid,
			'template_id'=>"el7PXjc7LEqUQzcyhz4vJRM7GCfSKGaoLciWhp9TwVY",
			'url'=>"http=>//weixin.qq.com/download",
			'topcolor'=>"#FF0000",
			'data'=>array(
				'restaurant'=>array(
					'value' => $restaurant,
					'color' => "#173177"
				),
				'price' => array(
					'value' => $price,
					'color' => "#173177"
				),
				'time' => array(
					'value' => $time,
					'color' => "#173177"
				)
			)
		);
		$weObj->sendTemplateMessage($data);
	}
	
	/*
	 *主动接口：发送模板消息：退款
	 *
     */
	public function send_templete_cancel($openid, $restaurant, $price, $time){
		$weObj = $this->get_weObj();
		$data = array(
			'touser'=>$openid,
			'template_id'=>"el7PXjc7LEqUQzcyhz4vJRM7GCfSKGaoLciWhp9TwVY",
			'url'=>"http=>//weixin.qq.com/download",
			'topcolor'=>"#FF0000",
			'data'=>array(
				'restaurant'=>array(
					'value' => $restaurant,
					'color' => "#173177"
				),
				'price' => array(
					'value' => $price,
					'color' => "#173177"
				),
				'time' => array(
					'value' => $time,
					'color' => "#173177"
				)
			)
		);
		$weObj->sendTemplateMessage($data);
	}

}