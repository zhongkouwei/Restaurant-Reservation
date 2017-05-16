<?php
namespace Home\Controller;
use Think\Controller;
/**
 * ============================================================================
 * Author：高硕
 * ============================================================================
 * 后台管理控制器
 */
class AdminController extends CommonController {
    public $restaurantinfo;
    public function _initialize(){
        if(!isset($_SESSION['restaurantid'])){
            $this->assign("jumpUrl",U("Login/index"));
            $this->error('请先登录');
        }else{
            if(empty($this->restaurantinfo)){
            $this->restaurantinfo = M('restaurant')->where('id='.$_SESSION['restaurantid'])->select();
        }
        }
    }
    public function index(){
        layout(adminlayout);
        $current_ip = get_client_ip();
        $this->ip = $current_ip;
        $this->loginname = $_SESSION['loginname'];
        $this->detail = $this->restaurantinfo;
        $this->display();
    }
    public function fixinfo(){
        if(empty($_POST)){
            $this->error('禁止访问！');
        }
        $data['phone'] = I('post.phone');
        $data['address'] = I('post.address');
        $data['detail'] = I('post.detail');
        $restaurant = M('restaurant');
        $fixinfo = $restaurant->where('id='.$_SESSION['restaurantid'])->save($data);
        if($fixinfo){
            $this->assign("jumpUrl",U("Admin/index"));
            $this->success("修改成功");
        }else{
            $this->assign("jumpUrl",U("Admin/index"));
            $this->error("未发生改动或修改失败");
        }
    }
    public function reserve(){
        layout(adminlayout);
        $this->restaurantname = $_SESSION['restaurantname'];
        $this->restaurantdetail = $this->restaurantinfo;
        $this->detail = M('detail')->select();
        $condition['time']  = '1';
        $condition['date']  = date('Y-m-d');
        $condition['desk_id']  = array('neq','');
        $list = M('detail')->where($condition)->select();
        $this->list = json_encode($list);
        $this->display();
    }
    /*
    * 菜单管理
    *
    */
    public function dishes(){
        layout(adminlayout);
        $this->restaurantname = $_SESSION['restaurantname'];
        $this->restaurantdetail = $this->restaurantinfo;
        $dishesAll = M('dishes');
        $this->dishes = $dishesAll->where("restaurant_id=".$_SESSION["restaurantid"])->select();
        $this->display();
    }

    /*
    * 微信消息管理
    *
    */
    public function wxmessage(){
        layout(adminlayout);
        $messageAll = M('message');
        $this->message = $messageAll->select();
        $this->display();
    }

    /*
     * 店铺管理
     */
    public function manage(){
        layout(adminlayout);
        $this->display();
    }
    /*
     * 菜品管理
     */
    public function dishes_manage(){
        layout(adminlayout);
        $this->display();
    }
    /*
     * 添加菜
     */
    public function adddishes(){
        if(empty($_POST)){
            $this->error('禁止访问！');
        }

        $data['name'] = I('post.name');
        $data['price'] = I('post.price');
        $data['detail'] = I('post.detail');
        $data['type'] = I('post.type');
        if($_FILES['photo']['name']){
            $data['image'] = '/Uploads/';
        }
        $data['restaurant_id'] = I('session.restaurantid');
        $dishes = M('dishes');
        $adddishes = $dishes->add($data);
        if($adddishes){
            if($_FILES['photo']['name']){
                $upload = new \Think\Upload();
                $upload->maxSize   = 3145728 ;
                $upload->exts      = array('jpg', 'gif', 'png', 'jpeg');
                $upload->rootPath  = './Uploads/saved/'; 
                $upload->autoSub = false;
                $upload->saveName = $adddishes;
                $info   =   $upload->upload();
                if(!$info) {
                    $this->assign("jumpUrl",U("Admin/dishes"));
                    $this->error($upload->getError());
                }else{
                    $image = new \Think\Image();
                    $file_path = './Uploads/saved/'.$info[photo]['savename'];
                    $file_mini = './Uploads/'.$adddishes.'.png';
                    $image->open($file_path);
                    $image->thumb(400,400,\Think\Image::IMAGE_THUMB_CENTER)->save($file_mini);
                    $this->assign("jumpUrl",U("Admin/dishes"));
                    $this->success('添加菜色成功！');
                }
            }else{
                $this->assign("jumpUrl",U("Admin/dishes"));
                $this->success('提示未添加图片');
            }
        }else{
            $this->assign("jumpUrl",U("Admin/dishes"));
            $this->error('添加菜色失败！');
        }


    }
    function ajaxreserve(){
        //ajaxReturn(数据,'提示信息',状态)
        $condition['time']  = I('get.time');
        $condition['date']  = I('get.date');
        $condition['desk_id']  = array('neq','');
        $list = M('detail')->where($condition)->select();
        $this->ajaxReturn($list);
    }
	
	/*
	 * 回复微信消息
	 *
	 */
	public function reply(){
		var_dump($_POST);
        die();
		
	}
}