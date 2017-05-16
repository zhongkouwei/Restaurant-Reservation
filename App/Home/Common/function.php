<?php

  function p($val){
    dump($val,1,'pre');
  }
  /**
   *
   * 写入本地日志文件
   * 默认Log.txt
   * @param string $text 要写入的信息
   */
  function logtext($text){
    $time =date('y-m-d h:i:s',time());
    file_put_contents('log.txt',$time."  ".$text."\n",FILE_APPEND);   
  }
  function order_no($examid){
    //return date('Ymd').substr(implode(NULL, array_map('ord', str_split(substr(uniqid(), 7, 13), 1))), 0, 8).$examid;
    return date('YmdHis').$examid;
  }
  function getWechatInfo(){
    vendor("wechat.wechat");
    $options = array(
        'token'=>'weixin', //填写你设定的key
        'encodingaeskey'=>'jgNF3nRdTqwauhqiCcJcLxkiwEDLeYl3PdtF2lBMtQn', //填写加密用的EncodingAESKey
        'appid'=>'wx439a3874a5b28c4f', //填写高级调用功能的app id, 请在微信开发模式后台查询
        'appsecret'=>'99f3dc0e04ed8a5dae12ee29e3e847cc' //填写高级调用功能的密钥
      );
      $weObj = new Wechat($options);
      $accesstoken = $weObj->getOauthAccessToken();
      $freshtoken = $weObj->getOauthRefreshToken($accesstoken['refresh_token']);
      $userwechat = $weObj->getOauthUserinfo($accesstoken['access_token'], $freshtoken['openid']);
      return $userwechat;
  }

    function isWechat(){
      if ( strpos($_SERVER['HTTP_USER_AGENT'], 'MicroMessenger')) {
            return true;
        }else{
            return false;
        }
    }
    function timeformat($time){
    return substr($time,0,4).'-'.substr($time,4,2).'-'.substr($time,6,2).' '.substr($time,8,2).':'.substr($time,10,2).':'.substr($time,12,2);
  }
    function paynotify(){
        vendor('Log.log');
    }
?>
