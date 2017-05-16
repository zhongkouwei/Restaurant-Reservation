<?php
namespace Home\Controller;
use Think\Controller;
/**
 * ============================================================================
 * Author：高硕
 * ============================================================================
 * 基础控制器
 */
class CommonController extends Controller {
    public function _initialize(){
        if(!isset($_SESSION['restaurantid']) || !isset($_SESSION['restaurantname']) ){
        	$this->assign("jumpUrl",U("Login/index"));
        	$this->error('请先登录');
        }
    }

}