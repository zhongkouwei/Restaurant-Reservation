<?php
namespace Home\Controller;
use Think\Controller;
/**
 * ============================================================================
 * Author：高硕
 * ============================================================================
 * 商家列表控制器
 */
class ShopsController extends Controller {
	public function index(){
		$Restaurant = M('restaurant');
		$this->myAddress = "重庆";
		$this->list = $Restaurant ->select();
		$this->display();     
	}
}
?>