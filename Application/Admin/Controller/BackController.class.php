<?php
namespace Admin\Controller;
use Think\Controller;
class BackController extends Controller{
    public function __construct(){
        //验证当前是否处于登录状态
        $adminId=session('admin_id');
        if(!$adminId){
            $this->redirect('Login/login');
        }
        parent::__construct();//强制调用父类的构造方法
        //验证当前管理员的访问权限
        //获取当前访问的页面url
        //$url=MODULE_NAME.'/'.CONTROLLER_NAME.'/'.ACTION_NAME;
        $where='module_name="'.MODULE_NAME.'" and controller_name="'.CONTROLLER_NAME.'" and action_name="'.ACTION_NAME.'"';
        if (CONTROLLER_NAME=="Index"){
            //任何角色均可访问
            return true;
        }
        if ($adminId==1){
            $sql="select count(*) has from php2018_privilege where ".$where;//超级管理员
        }else {
            $sql = 'SELECT COUNT(a.role_id) has
			  FROM php2018_role_privilege a
			   LEFT JOIN php2018_privilege b ON a.pri_id=b.id
			   LEFT JOIN php2018_admin_role c ON a.role_id=c.role_id
			    WHERE c.admin_id='.$adminId.' AND '.$where;
        }
        $db=M();
        $pri=$db->query($sql);
        if($pri[0]['has']<1){
            $this->error('无访问权限');
        }
    }

     //模板布局调用
     protected function setBent($title,$btnName,$bitlink){
            $this->assign('_title',$title);
            $this->assign('_btnName',$btnName);
            $this->assign('_bitlink',$bitlink);
     }
}