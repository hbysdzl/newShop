<?php
namespace Admin\Controller;
use \Admin\Controller\BackController;
class GoodscategoryController extends BackController
{
    public function add()
    {
    	if(IS_POST)
    	{
    		$model = D('Admin/Goodscategory');
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
		$parentModel = D('Admin/Goodscategory');
		$parentData = $parentModel->getTree();
		$this->assign('parentData', $parentData);
        //获取所有的商品类型制作下拉框
        $goodstypeModel=M('goodstype');
        $goodstype=$goodstypeModel->select();
        $this->assign('goodstype',$goodstype);
		$this->setBent('添加商品分类', '商品分类列表', U('lst?p='.I('get.p')));
		$this->display();
    }
    public function edit()
    {
    	$cat_id = I('get.cat_id');
    	if(IS_POST)
    	{
    		$model = D('Admin/Goodscategory');
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
    	$model = M('Goodscategory');
    	$data = $model->find($cat_id);
    	$this->assign('data', $data);
		$parentModel = D('Admin/Goodscategory');
		$parentData = $parentModel->getTree();
		$children = $parentModel->getChildren($cat_id);
		if($data['search_attr_id']){
		    //获取对应的属性名称记录
		   $attrData=M('goodsattribute')->field('attr_id,attr_name,type_id')->where(array('attr_id'=>array('in',$data['search_attr_id'])))->select();
		   $this->assign('attrData',$attrData); 
		}
		//获取所有的商品类型制作下拉框
		$goodstypeModel=M('goodstype');
		$goodstype=$goodstypeModel->select();
		
		$this->assign(array(
			'parentData' => $parentData,
			'children' => $children,
		    'goodstype'=>$goodstype
		));

		$this->setBent('修改商品分类', '商品分类列表', U('lst?p='.I('get.p')));
		$this->display();
    }
    public function delete()
    {
    	$model = D('Admin/Goodscategory');
    	if($model->delete(I('get.cat_id', 0)) !== FALSE)
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
    	$model = D('Admin/Goodscategory');
		$data = $model->getTree();
    	$this->assign(array(
    		'data' => $data,
    	));

		$this->setBent('商品分类列表', '添加商品分类', U('add'));
    	$this->display();
    }
}