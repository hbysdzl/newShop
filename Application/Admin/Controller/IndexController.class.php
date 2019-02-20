<?php
//后台首页控制器
namespace Admin\Controller;
use Admin\Controller;
class IndexController extends BackController{
    public function Index(){
         $this->display();
    }
    public function top(){
        $this->display();
    }
    public function menu(){
        $adminId=session('admin_id');
        if($adminId==1){
            $pri=M('privilege')->select();
        }else {
            $sql="select b.* from php2018_role_privilege a LEFT JOIN php2018_privilege b
                on a.pri_id=b.pri_id LEFT JOIN php2018_admin_role c on a.role_id=c.role_id
                WHERE c.admin_id=".$adminId;
            $pri=M()->query($sql);
        }
        //获取所有权限的前两级
        foreach ($pri as $k=>$v){
            if($v['parent_id']==0){//顶级权限
                foreach ($pri as $k1=>$v1){
                    if ($v1['parent_id']==$v['pri_id']){
                        $v['child'][]=$v1;
                    }
                }
                $btn[]=$v;
            }

        }
        $this->assign('btn',$btn);
        $this->display();
    }
    public function main(){
        $this->display();
    }
    //退出登录
    public function unsetLogin(){
        session('admin_id',null);
        session('username',null);
        $this->redirect('Login/login');
    }
}