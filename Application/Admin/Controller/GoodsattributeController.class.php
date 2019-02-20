<?php
namespace Admin\Controller;
use \Admin\Controller\IndexController;
class GoodsattributeController extends BackController
{
    public function add($type_id)
    {
    	if(IS_POST)
    	{
    		$model = D('Admin/Goodsattribute');
    		if($list=$model->create(I('post.'), 1))
    		{
    			if($model->add())
    			{
    				$this->success('添加成功！', U('lst?p='.I('get.p').'&type_id='.$list['type_id']));
    				exit;
    			}
    		}
    		$this->error($model->getError());
    	}

		$this->setBent('添加商品属性', '商品属性列表', U('lst?p='.I('get.p').'&type_id='.$type_id));
		//获取所有商品类型名称
		$type=M('Goodstype')->select();
		//var_dump($type);
		$this->assign('type',$type);
		$this->display();
    }
    public function edit()
    {
    	$attr_id = I('get.attr_id');
    	if(IS_POST)
    	{
    		$model = D('Admin/Goodsattribute');
    		if($list=$model->create(I('post.'), 2))
    		{
    			if($model->save() !== FALSE)
    			{
    				$this->success('修改成功！', U('lst?type_id='. $list['type_id'], array('p' => I('get.p', 1))));
    				exit;
    			}
    		}
    		$this->error($model->getError());
    	}
    	$model = M('Goodsattribute');
    	$data = $model->find($attr_id);
    	$this->assign('data', $data);
    	//获取所有商品类型名称
    	$type=M('Goodstype')->select();
    	$this->assign('type',$type);
		$this->setBent('修改商品属性', '商品属性列表', U('lst?p='.I('get.p').'&type_id='.$data['type_id']));
		$this->display();
    }
    public function delete($attr_id){
         //获取当前商品的类型ID
         $list=M('Goodsattribute')->find($attr_id);
    	$model = D('Admin/Goodsattribute');
    	if($model->delete(I('get.attr_id', 0)) !== FALSE)
    	{
    		$this->success('删除成功！', U('lst?type_id='.$list['type_id'], array('p' => I('get.p', 1))));
    		exit;
    	}
    	else
    	{
    		$this->error($model->getError());
    	}
    }
    public function lst($type_id){
    	$model = D('Admin/Goodsattribute');
    	$data = $model->search($type_id);
    	$this->assign(array(
    		'data' => $data['data'],
    		'page' => $data['page'],
    	));

		$this->setBent('商品属性列表', '添加商品属性', U('add?type_id='.$type_id));
		//获取所有商品类型名称
		$type=M('Goodstype')->select();
		//var_dump($type);
	    $this->assign('type',$type);
	    //获取当前商品类型ID
	    $this->assign('type_id',$type_id);
        $this->display();
    }
}