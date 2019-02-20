<?php
namespace Admin\Controller;
use \Admin\Controller\BackController;
class PrivilegeController extends BackController
{
    public function add()
    {
    	if(IS_POST)
    	{
    		$model = D('Admin/Privilege');
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
		$parentModel = D('Admin/Privilege');
		$parentData = $parentModel->getTree();
		$this->assign('parentData', $parentData);

		$this->setBent('添加权限', '权限列表', U('lst?p='.I('get.p')));
		$this->display();
    }
    public function edit()
    {
    	$pri_id = I('get.pri_id');
    	if(IS_POST)
    	{
    		$model = D('Admin/Privilege');
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
    	$model = M('Privilege');
    	$data = $model->find($pri_id);
    	$this->assign('data', $data);
		$parentModel = D('Admin/Privilege');
		$parentData = $parentModel->getTree();
		$children = $parentModel->getChildren($pri_id);
		$this->assign(array(
			'parentData' => $parentData,
			'children' => $children,
		));

		$this->setBent('修改权限', '权限列表', U('lst?p='.I('get.p')));
		$this->display();
    }
    public function delete()
    {
    	$model = D('Admin/Privilege');
    	if($model->delete(I('get.pri_id', 0)) !== FALSE)
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
    	$model = D('Admin/Privilege');
		$data = $model->getTree();
    	$this->assign(array(
    		'data' => $data,
    	));

		$this->setBent('权限列表', '添加权限', U('add'));
    	$this->display();
    }
}