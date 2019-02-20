<?php
namespace Admin\Controller;
use \Admin\Controller\IndexController;
class RoleController extends BackController
{
    public function add(){
    	if(IS_POST){

    		$model = D('Admin/Role');
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
        $this->setBent('添加角色', '角色列表', U('lst?p='.I('get.p')));
        //获取所有权限信息
        $Pri=D('Privilege')->getTree();
        $this->assign('pri',$Pri);
		$this->display();
    }
    public function edit()
    {
    	$role_id = I('get.role_id');
    	if(IS_POST)
    	{
    		$model = D('Admin/Role');
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
    	$model = M('Role');
    	$data = $model->find($role_id);
    	$this->assign('data', $data);

		$this->setBent('修改角色', '角色列表', U('lst?p='.I('get.p')));
		//获取权限信息
		$Pri=D('Privilege')->getTree();
		$this->assign('pri',$Pri);
		//获取当前角色所拥有的权限ID
		$pri_id=M('RolePrivilege')->field('pri_id')->where(array('role_id'=>array('eq',I('get.role_id'))))->select();
		foreach ($pri_id as $k=>$v){
		    $pri_ids[]=$v['pri_id'];
		}

        $this->assign('pri_ids',$pri_ids);
		$this->display();
    }

    public function delete()
    {
        $model = D('Admin/Role');
    	if($model->delete(I('get.role_id', 0)) !== FALSE){

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
    	$model = D('Admin/Role');
    	$data = $model->search();

    	$this->assign(array(
    		'data' => $data['data'],
    		'page' => $data['page'],
    	));

		$this->setBent('列表', '添加角色', U('add'));
    	$this->display();
    }
}