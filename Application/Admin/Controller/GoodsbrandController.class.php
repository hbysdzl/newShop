<?php
namespace Admin\Controller;
use \Admin\Controller\BackController;
class GoodsbrandController extends BackController
{
    public function add()
    {
    	if(IS_POST)
    	{
    		$model = D('Admin/Goodsbrand');
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

		$this->setBent('添加品牌', '品牌列表', U('lst?p='.I('get.p')));
		$this->display();
    }
    public function edit()
    {
    	$brand_id = I('get.brand_id');
    	if(IS_POST)
    	{
    		$model = D('Admin/Goodsbrand');
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
    	$model = M('Goodsbrand');
    	$data = $model->find($brand_id);
    	$this->assign('data', $data);

		$this->setBent('修改品牌', '品牌列表', U('lst?p='.I('get.p')));
		$this->display();
    }
    public function delete()
    {
    	$model = D('Admin/Goodsbrand');
    	if($model->delete(I('get.brand_id', 0)) !== FALSE)
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
    	$model = D('Admin/Goodsbrand');
    	$data = $model->search();
    	$this->assign(array(
    		'data' => $data['data'],
    		'page' => $data['page'],
    	));

		$this->setBent('品牌列表', '添加品牌', U('add'));
    	$this->display();
    }
}