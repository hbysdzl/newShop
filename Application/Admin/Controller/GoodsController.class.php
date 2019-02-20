<?php
namespace Admin\Controller;
use \Admin\Controller\BackController;
use DoctrineTest\InstantiatorTestAsset\ArrayObjectAsset;
class GoodsController extends BackController
{
    public function add()
    {
    	if(IS_POST){
    	   
    		$model = D('Admin/Goods');
    		if($data=$model->create(I('post.'), 1)){
          //将促销时间转换为时间戳
        $data['promote_start_time'] = (string)strtotime($data['promote_start_time']);
        $data['promote_end_time'] = (string)strtotime($data['promote_end_time']);
       
    			if($id = $model->add($data))
    			{
    				$this->success('添加成功！', U('lst?p='.I('get.p')));
    				exit;
    			}
    		}
    		$this->error($model->getError());
    	}
		$this->setBent('添加商品', '商品列表', U('lst?p='.I('get.p')));
		//获取所有商品分类
		$cate=D('Goodscategory')->getTree();
		$this->assign('cate',$cate);
		//获取所有品牌信息
		$brand=D('Goodsbrand')->select();
		$this->assign('brand',$brand);
		//获取所有会员信息
        $level=D('NumberLevel')->select();
        $this->assign('level',$level);
		//获取所有的商品类型
		$types=M('Goodstype')->select();
		$this->assign('types',$types);
		$this->display();
    }
    
    public function edit(){
    	$goods_id = I('get.goods_id');
    	if(IS_POST){
    	  /* echo "<pre>";
    	   var_dump(I('post.'));
    	   die();*/
    		$model = D('Admin/Goods');
    		if($data=$model->create(I('post.'), 2)){
    			if($model->save() !== FALSE){
    			    
    				$this->success('修改成功！', U('lst', array('p' => I('get.p', 1))));
    				exit;
    			}
    		}
    		$this->error($model->getError());
    	}
    	$model = M('Goods');
    	$data = $model->find($goods_id);
    	$this->assign('data', $data);
 
		$this->setBent('修改商品', '商品列表', U('lst?p='.I('get.p')));
		//获取商品分类信息
		$cat=D('Goodscategory')->getTree();
		$this->assign('cat',$cat);
		//获取商品品牌信息
		$brand=M('Goodsbrand')->select();
		$this->assign('brand',$brand);
		//获取所有商品类型信息
		$type=M('goodstype')->select();
		$this->assign('type',$type);
		//获取指定修改的商品信息
		$editgoods=M('goods')->find($goods_id);
		$this->assign('editgoods',$editgoods);
		//获取指定商品扩展类别信息
		$cats=M('GoodsCat')->where(array('goods_id'=>array('eq',$goods_id)))->select();
	    $this->assign('cats',$cats);
	     //获取的会员级别信息
	    $level=M('NumberLevel');
        $level_price=$level->alias('a')->JOIN('LEFT JOIN php2018_number_price as b on a.level_id=b.level_id')->where(array('b.goods_id'=>array('eq',$goods_id)))->select();
	    $this->assign('level',$level_price);
	    //获取当前商品的相册图片
	    $imgs=M('GoodsPics')->where(array('goods_id'=>array('eq',$goods_id)))->select();
	    $this->assign('images',$imgs);
	    //获取当前商品的属性信息
	    $AttrModel=M('GoodsAttr');
	    $attr=$AttrModel->alias('a')->order('a.attr_id')->join('LEFT JOIN php2018_goodsattribute as b on a.attr_id=b.attr_id')->where(array('a.goods_id'=>array('eq',$goods_id)))->select();
	    /*----------------获取当前商品不存在的（后新添加的属性）----------------------*/
	    //获取当前商品的属性ID
	    foreach ($attr as $k=>$v){
	        $attr_id[]=$v['attr_id'];
	    }
	    $attr_id=array_unique($attr_id);
	    
	    //获取当前类型新增的属性
	    $newModel=M('goodsattribute');
	    $newattr=$newModel->where(array('type_id'=>array('eq',$editgoods['type_id']),'attr_id'=>array('not in',$attr_id)))->select();
	   //新属性合并到当前类型商品属性中
	   $newattr=$newattr==null? array():$newattr;
	    $attr=array_merge($attr,$newattr);
	   // echo "<pre>";
	    //var_dump($attr);
	    //die();
	    $this->assign('attr',$attr);
	    
	    $this->display();
    }
    // AJAX删除图片
    public function ajaxDelImage(){
    	$picId = I('get.pic_id');
    	$gpModel = M('GoodsPics');
    	// 先取出图片的路径
    	$pic = $gpModel->field('pic,sm_pic')->find($picId);
    	// 把图片从硬盘上删除
    	 deleteImage($pic);
    	// 再从数据库中把图片的数据也删除掉
    	$gpModel->delete($picId);
    }
    
    //Ajax删除指定属性
    public function ajaxdeleteattr($gaid){
        $model=M('GoodsAttr');
        if(false==$model->delete($gaid)){
            echo "删除失败";
        }else{
            echo "删除成功";
        }
    }
    
    //商品放入回收站
    public function recycle($goods_id){
       $model=M('goods');
       if($model->where('goods_id='.$goods_id)->setField('is_delete',1)){
           $this->success('回收成功!',U('lst', array('p' => I('get.p', 1) )));
       }else {
           $this->error('回收失败');
       }
        
    }
    
    //商品回收站列表
    public function huishou(){
        $model = D('Admin/Goods');
        $data = $model->search(5,1);
        $this->assign(array(
            'data' => $data['data'],
            'page' => $data['page'],
        ));
        
        $this->setBent('回收列表', '商品列表', U('lst'));
        $this->display();
    }
    
    //商品还原
    public function yuan($goods_id){
        $model=M('goods');
        if($model->where('goods_id='.$goods_id)->setField('is_delete',0)){
            $this->success('还原成功!',U('huishou', array('p' => I('get.p', 1) )));
        }else {
            $this->error('还原失败');
        }
    }
    //商品删除
    public function delete()
    {
    	$model = D('Admin/Goods');
    	if($model->delete(I('get.goods_id', 0)) !== FALSE)
    	{
    		$this->success('删除成功！', U('huishou', array('p' => I('get.p', 1))));
    		exit;
    	}
    	else
    	{
    		$this->error($model->getError());
    	}
    }
    public function lst()
    {
    	$model = D('Admin/Goods');
    	$data = $model->search();
    	$this->assign(array(
    		'data' => $data['data'],
    		'page' => $data['page'],
    	));

		$this->setBent('商品列表', '添加商品', U('add'));
    	$this->display();
    }
    
    //商品库存量
    public function goodsNumber($goods_id){
        
        $num_model=M('GoodsNumber');
        if (IS_POST){
            $goods_attr_id=I('post.goods_attr_id');
            $goods_number=I('post.goods_number');
            $id=I('post.id');
            //计算数组的比例
            $rate=count($goods_attr_id)/count($goods_number);
            $_i= 0;
         
            //循环每一条库存量入库
            foreach ($goods_number as $k=>$v){
               //每次循环的属性ID存入数组中 
               $_arr=array();
               for ($i=0;$i<$rate;$i++){
                   $_arr[]=$goods_attr_id[$_i];
                   $_i++;
               }
               
              //将取得的属性ID进行升序排列并拼接为字符串
              sort($_arr);
              $_arr=implode(',',$_arr);
              $data['goods_id']=$goods_id;
              $data['goods_number']=$v;
              $data['goods_attr_id']=$_arr;
              $data['id']=$id[$k];
              if($data['id']){
                  $num_model->save($data);
              }else {
                  $num_model->add($data);
              }
              
            }
           $this->success('保存成功');
           die();
        }
       $sql="SELECT a.* , b.attr_name from php2018_goods_attr as a LEFT JOIN php2018_goodsattribute as b on a.attr_id=b.attr_id 
            WHERE a.attr_id in(SELECT attr_id from php2018_goods_attr WHERE goods_id=".$goods_id." GROUP BY attr_id HAVING COUNT(*)>1) and a.goods_id=".$goods_id." ORDER BY a.attr_id";
       $model=M();
       $arr=$model->query($sql);
       //将二位数组转换为三位数组
       $attr=array();
       foreach ($arr as $k=>$v){
           $attr[$v['attr_id']][]=$v;
       }
       
       //获取已经设置过库存量的数据
       $number=$num_model->where('goods_id='.$goods_id)->select();
       $this->assign('number',$number);
       $this->setBent('商品库存量', '商品列表', U('lst'));
       $this->assign('attr',$attr);
       $this->display();
      
    }

    //Ajax获取属性请求
    public function ajaxGetAttr(){
        $typeId=I('get.type_id');
        //获取指定类型下的属性
        $attrData=M('Goodsattribute')->where(array('type_id'=>array('eq',$typeId)))->select();
        echo json_encode($attrData);
    }
}