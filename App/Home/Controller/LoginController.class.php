<?php
namespace Home\Controller;
use Think\Controller;
class LoginController extends Controller {
    public function index(){
        $this->title = "登录";
        layout(false);
        session_unset();
        session_destroy();
        $this->display();
    }
    public function login(){
        if(!IS_POST) E('无法访问');
        $username = I('post.username');
        $password = I('post.password');
        $info = M('admin')->where(array("user" => $username))->find();
        if(!$info || $info['password'] != $password){
            echo "密码错误";die();
            $this->error('帐号或密码错误');
        }
        session('loginname',$info['user']);
        session('restaurantid',$info['id']);
        $this->redirect('Admin/index');
    }
    public function logout(){
        session_unset();
        session_destroy();
        $this->redirect('Login/index');
    }
}
