<?php
namespace Admin\Controller;
use \Admin\Controller\BackController;
class NumberLevelController extends BackController
{
    public function add()
    {
    	if(IS_POST)
    	{
    		$model = D('Admin/NumberLevel');
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

		$this->setBent('添加会员', '会员列表', U('lst?p='.I('get.p')));
		$this->display();
    }
    public function edit()
    {
    	$level_id = I('get.level_id');
    	if(IS_POST)
    	{
    		$model = D('Admin/NumberLevel');
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
    	$model = M('NumberLevel');
    	$data = $model->find($level_id);
    	$this->assign('data', $data);

		$this->setBent('修改会员', '会员列表', U('lst?p='.I('get.p')));
		$this->display();
    }
    public function delete()
    {
    	$model = D('Admin/NumberLevel');
    	if($model->delete(I('get.level_id', 0)) !== FALSE)
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
    	$model = D('Admin/NumberLevel');
    	$data = $model->search();
    	$this->assign(array(
    		'data' => $data['data'],
    		'page' => $data['page'],
    	));

		$this->setBent('会员列表', '添加会员', U('add'));
    	$this->display();
    }
}