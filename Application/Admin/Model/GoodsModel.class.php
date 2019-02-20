<?php
namespace Admin\Model;
use Think\Model;
class GoodsModel extends Model
{
	protected $insertFields = array('goods_name','cat_id','brand_id','market_price','shop_price','jifen','jyz','jifen_price','is_promote','promote_price','promote_start_time','promote_end_time','is_hot','is_new','is_best','is_on_sale','seo_keyword','seo_description','type_id','sort_num','is_delete','goods_desc');
	protected $updateFields = array('goods_id','goods_name','cat_id','brand_id','market_price','shop_price','jifen','jyz','jifen_price','is_promote','promote_price','promote_start_time','promote_end_time','is_hot','is_new','is_best','is_on_sale','seo_keyword','seo_description','type_id','sort_num','is_delete','goods_desc');
	protected $_validate = array(
		array('goods_name', 'require', '商品名称不能为空！', 1, 'regex', 3),
		array('goods_name', '1,45', '商品名称的值最长不能超过 45 个字符！', 1, 'length', 3),
		array('cat_id', 'require', '主分类不能为空！', 1, 'regex', 3),
		array('market_price', 'currency', '市场价必须是货币格式！', 2, 'regex', 3),
		array('shop_price', 'currency', '本店价必须是货币格式！', 2, 'regex', 3),
		array('jifen', 'number', '赠送积分必须是一个整数！', 1, 'regex', 3),
		array('jyz', 'number', '赠送经验值必须是一个整数！', 1, 'regex', 3),
		array('jifen_price', 'number', '需要的积分数必须是一个整数！', 1, 'regex', 3),
		array('seo_keyword', '1,150', 'seo优化[搜索引擎【百度、谷歌等】优化]_关键字的值最长不能超过 150 个字符！', 2, 'length', 3),
		array('seo_description', '1,150', 'seo优化[搜索引擎【百度、谷歌等】优化]_描述的值最长不能超过 150 个字符！', 2, 'length', 3),
		array('sort_num', 'number', '排序数字必须是一个整数！', 2, 'regex', 3),
		array('is_delete', 'number', '是否放到回收站：1：是，0：否必须是一个整数！', 2, 'regex', 3),
	);
	public function search($pageSize = 5,$del=0)
	{
		/**************************************** 搜索 ****************************************/
		$where = array();
		$where['is_delete']=$del;
		if($goods_name = I('get.goods_name'))
			$where['goods_name'] = array('like', "%$goods_name%");
		if($cat_id = I('get.cat_id'))
			$where['cat_id'] = array('eq', $cat_id);
		if($brand_id = I('get.brand_id'))
			$where['brand_id'] = array('eq', $brand_id);
		$shop_pricefrom = I('get.shop_pricefrom');
		$shop_priceto = I('get.shop_priceto');
		if($shop_pricefrom && $shop_priceto)
			$where['shop_price'] = array('between', array($shop_pricefrom, $shop_priceto));
		elseif($shop_pricefrom)
			$where['shop_price'] = array('egt', $shop_pricefrom);
		elseif($shop_priceto)
			$where['shop_price'] = array('elt', $shop_priceto);
		$is_hot = I('get.is_hot');
		if($is_hot != '' && $is_hot != '-1')
			$where['is_hot'] = array('eq', $is_hot);
		$is_new = I('get.is_new');
		if($is_new != '' && $is_new != '-1')
			$where['is_new'] = array('eq', $is_new);
		$is_best = I('get.is_best');
		if($is_best != '' && $is_best != '-1')
			$where['is_best'] = array('eq', $is_best);
		$is_on_sale = I('get.is_on_sale');
		if($is_on_sale != '' && $is_on_sale != '-1')
			$where['is_on_sale'] = array('eq', $is_on_sale);
		if($type_id = I('get.type_id'))
			$where['type_id'] = array('eq', $type_id);
		$addtimefrom = I('get.addtimefrom');
		$addtimeto = I('get.addtimeto');
		if($addtimefrom && $addtimeto)
			$where['addtime'] = array('between', array(strtotime("$addtimefrom 00:00:00"), strtotime("$addtimeto 23:59:59")));
		elseif($addtimefrom)
			$where['addtime'] = array('egt', strtotime("$addtimefrom 00:00:00"));
		elseif($addtimeto)
			$where['addtime'] = array('elt', strtotime("$addtimeto 23:59:59"));
		/************************************* 翻页 ****************************************/
		$count = $this->alias('a')->where($where)->count();
		$page = new \Think\Page($count, $pageSize);
		// 配置翻页的样式
		$page->setConfig('prev', '上一页');
		$page->setConfig('next', '下一页');
		$data['page'] = $page->show();
		/************************************** 取数据 ******************************************/
		$data['data'] = $this->field('a.*,IFNULL(SUM(b.goods_number),0) gn')->alias('a')->join('LEFT JOIN php2018_goods_number as b ON a.goods_id=b.goods_id')->order('addtime desc')->where($where)->group('a.goods_id')->limit($page->firstRow.','.$page->listRows)->select();
		return $data;
	}
	// 添加前
	protected function _before_insert(&$data, $option){
	    //存入添加时间
	    $data['addtime']=time();
	    
		if(isset($_FILES['logo']) && $_FILES['logo']['error'] == 0)
		{
			$ret = uploadOne('logo', 'goods/', array(
				array(150, 150, 2),
			));
			if($ret['img'] == 1)
			{
				$data['logo'] = $ret['images'][0];
				$data['sm_logo'] = $ret['images'][1];
			}
			else
			{
				$this->error = $ret['error'];
				return FALSE;
			}
		}
	}
	//添加后
	protected function _after_insert($data, $options){
	    //接收类别扩展分类入库
	    $ext_cat=I('post.ext_cat_id');
        $goods_cat=M('GoodsCat');
        foreach ($ext_cat as $k=>$v){
            if($v==''){
                continue;
            }
            $goods['goods_id']=$data['goods_id'];
            $goods['cat_id']=$v;
            $goods_cat->add($goods);
        }
        //接收商品会员级别信息入库
        if($ml=I('post.ml')){
            $number_price=M('NumberPrice');
            foreach ($ml as $k=>$v){
                $number_price->add(
                    array(
                        'goods_id'=>$data['goods_id'],
                        'level_id'=>$k,
                        'price'=>$v
                    )
                 );
            }
        }
        //接收商品属性入库
        $ga = I('post.ga');
        $ap = I('post.attr_price');
        if($ga)
        {
            $gaModel = M('GoodsAttr');
            foreach ($ga as $k => $v)
            {
                foreach ($v as $k1 => $v1)
                {
                    if(empty($v1))
                        continue ;
                        $price = isset($ap[$k][$k1]) ? $ap[$k][$k1] : '';
                        $gaModel->add(array(
                            'goods_id' => $data['goods_id'],
                            'attr_id' => $k,
                            'attr_value' => $v1,
                            'attr_price' => $price,
                        ));
                }
            }
        }
        //接收商品相册图片入库
        if( hasImage('pics')){//判断表单是否为空
            //将批量上传的数组信息改造为单文件上传
            $pics=array();
            foreach ($_FILES['pics']['name'] as $k=>$v){
                $pics['name']=$v;
                $pics['type']=$_FILES['pics']['type'][$k];
                $pics['tmp_name']=$_FILES['pics']['tmp_name'][$k];
                $pics['error']=$_FILES['pics']['error'][$k];
                $pics['size']=$_FILES['pics']['size'][$k];
                $picss[]=$pics;
                }
                $_FILES=$picss;
                $img=M('GoodsPics');
                foreach ($picss as $k=>$v){
                    $ret=UploadOne($k,'goods/',array(array(150,150)));
                    if($ret['img']==1){//上传成功
                        $img->add(
                            array('pic'=>$ret['images'][0],
                                'sm_pic'=> $ret['images'][1],
                                'goods_id'=>$data['goods_id']
                            ));
                }
            }
        }
	}
	// 修改前
	protected function _before_update(&$data, $options){
	    
	    $data['promote_start_time']=strtotime(I('post.promote_start_time'));
	    $data['promote_end_time']=strtotime(I('post.promote_end_time'));
	   
	    //判断用户是否修改商品类型
	    if($data['type_id']!=I('post.oldtype_id')){
	        
	        //将原属性删除
	        M('GoodsAttr')->where('goods_id='.$options['where']['goods_id'])->delete();
	    } 
	   
		if(isset($_FILES['logo']) && $_FILES['logo']['error'] == 0){
			$ret = uploadOne('logo', 'goods/', array(
				array(150, 150, 2),
			));
			if($ret['img'] == 1){
				$data['logo'] = $ret['images'][0];
				$data['sm_logo'] = $ret['images'][1];
			}
			else{
				$this->error = $ret['error'];
				return FALSE;
			}
			deleteImage(array(
				I('post.old_logo'),
				I('post.old_sm_logo'),

			));
		}
	}
	//修改后
	protected function _after_update(&$data, $options){
	    //商品扩展分类的更新
	    $cat=I('post.ext_cat_id');
	    $goodsCat=M('GoodsCat');
	    $goodsCat->where(array('goods_id'=>array('eq',$options['where']['goods_id'])))->delete();
	    foreach ($cat as $k=>$v){
	        if ($v==''){
	            continue;
	        }
	        $data['goods_id']=$options['where']['goods_id'];
	        $data['cat_id']=$v;
	        $goodsCat->add($data);
	    }
	    //商品的会员价格更新
	    $price=I('post.ml');
	    $priceNumber=M('NumberPrice');
	    $priceNumber->where(array('goods_id'=>array('eq',$options['where']['goods_id'])))->delete();
	    foreach ($price as $k=>$v){
	        if($v==''){
	            continue;
	        }
	        $data['goods_id']=$options['where']['goods_id'];
	        $data['level_id']=$k;
	        $data['price']=$v;
	        $priceNumber->add($data);
	    }
	    //商品图片的更新
	    if( hasImage('pics')){//判断表单是否为空
	        //将批量上传的数组信息改造为单文件上传
	        $pics=array();
	        foreach ($_FILES['pics']['name'] as $k=>$v){
	            $pics['name']=$v;
	            $pics['type']=$_FILES['pics']['type'][$k];
	            $pics['tmp_name']=$_FILES['pics']['tmp_name'][$k];
	            $pics['error']=$_FILES['pics']['error'][$k];
	            $pics['size']=$_FILES['pics']['size'][$k];
	            $picss[]=$pics;
	        }
	        $_FILES=$picss;
	        $img=M('GoodsPics');
	        foreach ($picss as $k=>$v){
	            $ret=UploadOne($k,'goods/',array(array(150,150)));
	            if($ret['img']==1){//上传成功
	                $img->add(
	                    array('pic'=>$ret['images'][0],
	                        'sm_pic'=> $ret['images'][1],
	                        'goods_id'=>$data['goods_id']
	                    ));
	            }
	        }
	    }
	    //----------------------------------商品属性的更新处理-------------------------------------*/
	    //处理新属性
	    $ga=I('post.ga');
	    $pri=I('post.pir');
	    $attrModel=M('GoodsAttr');
	    foreach ($ga as $k1=>$v1){
	        
	       foreach ($v1 as $k2=>$v2){
	           $attr['goods_id']=$options['where']['goods_id'];
	           $attr['attr_id']=$k1;
	           $attr['attr_value']=$v2;
	           $attr['attr_price']=isset($pri[$k1][$k2])?$pri[$k1][$k2] :'';
	           $attrModel->add($attr);
	       }
	    }
	    
	    //处理旧属性
	    $oldga=I('post.old_ga');
	    $oldpri=I('post.old_pir');
	    $attrModel=M('GoodsAttr');
	    foreach ($oldga as $k1=>$v1){
	         
	        foreach ($v1 as $k2=>$v2){
	            $attrfile['id']=$k2;
                $attrfile['attr_id']=$k1;    	           
	            $attrfile['attr_value']=$v2;
	            $attrfile['attr_price']=isset($oldpri[$k1][$k2])?$oldpri[$k1][$k2] :'';
	           
	            $attrModel->save($attrfile);
	        }
	    }
	    
	}
	// 删除前
	protected function _before_delete($option)
	{
		if(is_array($option['where']['goods_id']))
		{
			$this->error = '不支持批量删除';
			return FALSE;
		}
		$images = $this->field('logo,sm_logo')->find($option['where']['goods_id']);
		deleteImage($images);
		
		//扩展分类的删除
		M('GoodsCat')->where('goods_id='.$option['where']['goods_id'])->delete();
		
		//会员价格的删除
		M('NumberPrice')->where('goods_id='.$option['where']['goods_id'])->delete();
		//商品属性的删除
		M('GoodsAttr')->where('goods_id='.$option['where']['goods_id'])->delete();
		//商品相册的删除
		$imgsModel=M('GoodsPics');
		$imgs=$imgsModel->field('pic,sm_pic')->where('goods_id='.$option['where']['goods_id'])->select();
		//现将硬盘图片删除
		foreach ($imgs as $v){
		    deleteImage($v);
		}	
		$imgsModel->where('goods_id='.$option['where']['goods_id'])->delete();
	}
	
}