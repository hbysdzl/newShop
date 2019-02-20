<?php
namespace Admin\Controller;
use \Admin\Controller\BackController;
class AdminController extends BackController
{
    public function add()
    {
    	if(IS_POST)
    	{
    		$model = D('Admin/Admin');
    		if($model->create(I('post.'), 1))
    		{
    			if($id = $model->add())
    			{
    				$this->success('添加成功！', U('lst?p='.I('get.p')));
    				exit;
    			}
    		}
    		$this->error($model->getError());
    	}
            //获取所有角色列表
        $role=M('Role')->select();
        $this->assign('roles',$role);
		$this->setBent('添加管理员', '管理员列表', U('lst?p='.I('get.p')));
		$this->display();
    }
    public function edit(){
    	$admin_id = I('get.admin_id');
    	//判断当前用户角色是否有权限修改
    	$adminID=session('admin_id');
    	if ($adminID>1 && $admin_id!=$adminID){//如果为普通管理员则无权修改
    	    $this->error('无修改权限');
    	}
    	if(IS_POST)
    	{
    		$model = D('Admin/Admin');
    		if($model->create(I('post.'), 2))
    		{
    			if($model->save() !== FALSE)
    			{
    				$this->success('修改成功！', U('lst', array('p' => I('get.p', 1))));
    				exit;
    			}
    		}
    		$this->error($model->getError());
    	}
    	$model = M('Admin');
    	$data = $model->find($admin_id);
    	$this->assign('data', $data);
        $this->setBent('修改管理员', '管理员列表', U('lst?p='.I('get.p')));
        //获取所有的角色列表
		$role=M('Role')->select();
		$this->assign('roles',$role);
		//获取当前管理员所属角色的ID
		$roleID=M('AdminRole')->where(array('admin_id'=>array('eq',$admin_id)))->select();
		foreach ($roleID as $v){
		    $roleids[]=$v['role_id'];
		}
		$this->assign('roleids', $roleids);
		$this->display();
    }

    public function delete(){
        //判断当前用户角色是否有权限删除
        $adminID=session('admin_id');
        if ($adminID>1){//如果为普通管理员则无权删除
            $this->error('无删除权限');
        }
    	$model = D('Admin/Admin');
    	if($model->delete(I('get.admin_id', 0)) !== FALSE)
    	{
    		$this->success('删除成功！', U('lst', array('p' => I('get.p', 1))));
    		exit;
    	}
    	else
    	{
    		$this->error($model->getError());
    	}
    }
    public function lst()
    {
    	$model = D('Admin/Admin');
    	$data = $model->search();
    	$this->assign(array(
    		'data' => $data['data'],
    		'page' => $data['page'],
    	));

		$this->setBent('管理员列表', '添加管理员', U('add'));
    	$this->display();

    }
    //Ajax请求
    public function AjaxUpdateIsuse(){
        $adminId=I('get.id');
        $model=M('Admin');
        $info=$model->find($adminId);
        if($info['is_use']==1){
            $model->where(array('admin_id'=>array('eq', $adminId)))->setField('is_use',0);
            echo 0;
        }else {
            $model->where(array('admin_id'=>array('eq', $adminId)))->setField('is_use',1);
            echo 1;
        }

    }
}