<?php
//后台登录操作控制器
namespace Admin\Model;
use Think\Model;
use Think\Verify;
class LoginModel extends Model{
    protected $tableName = 'admin';
      //定义提交登录表单信息的验证规则
    public  $_login_validate=array(
        array('username','require','用户名不能为空',1),
        array('password','require','密码不能为空',1),
    );

    public function login(){
        //获取用户提交的用户名和密码
        $username=$this->username;
        $password=$this->password;
        //查对数据库查对
       $user=$this->where(array('username'=>array('eq',$username)))->find();

       if($user){//判断是否有账号
           if($user['admin_id']==1 || $user['is_use']==1){//判断是否处于启用状态（超级管理员不可禁用）
               if ($user['password']==md5($password.C('MD5_KEY'))){//判断密码是否正确
                       //把用户ID和用户名存储到session中
                       session('admin_id',$user['admin_id']);
                       session('username',$user['username']);
                       return true;
               }else{
                   $this->error='密码错误！';
                   return false;
               }
           }else {
               $this->error='该账号已禁用';
               return false;
           }
       }else{
           $this->error='用户名不存在';
           return false;
       }
    }
}
