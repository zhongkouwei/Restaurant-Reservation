<?php
namespace Home\Controller;
use Think\Controller;
/**
 * ============================================================================
 * Author：高硕
 * ============================================================================
 * 首页控制器
 */
class IndexController extends Controller {
    public function index(){
    	$this->display();
    }
    public function dishes(){
    	$this->feature = M('dishes')->where("restaurant_id=1 and type = 1")->select();
    	$this->dishes = M('dishes')->where("restaurant_id=1 and type = 0")->select();
    	//p($this->feature);
    	$this->display();
    }
}
