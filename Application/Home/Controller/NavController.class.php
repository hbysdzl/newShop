<?php

//导航
namespace Home\Controller;


class NavController extends BackController {


	
	//精品推荐
	public function refined() {
		 $this->setPageInfo("网上购物,网上商城,家电,手机,电脑,服装,居家,母婴,美妆,个护,食品,生鲜","快乐购-专业的综合网上购物商城，为您提供正品低价的购物选择、优质便捷的服务体验。商品来自全球数十万品牌商家，囊括家电、手机、电脑、服装、居家、母婴、美妆、个护、食品、生鲜等丰富品类，满足各种购物需求。","精品推荐",1,array('index'),array('index'));

		 //获取数据
		 $where['is_best'] = 1;
		 $where['is_on_sale'] = 1;
		 $where['is_delete'] = 0;
		 $newdata = M('goods')->field('goods_id,goods_name,logo,shop_price,market_price')->where($where)->order('addtime desc')->select();
		 $this->assign('newdata',$newdata);
		 $this->display();
	}

	//家居家电
	public function appliance() {
		 $this->setPageInfo("网上购物,网上商城,家电,手机,电脑,服装,居家,母婴,美妆,个护,食品,生鲜","快乐购-专业的综合网上购物商城，为您提供正品低价的购物选择、优质便捷的服务体验。商品来自全球数十万品牌商家，囊括家电、手机、电脑、服装、居家、母婴、美妆、个护、食品、生鲜等丰富品类，满足各种购物需求。","家居家电",1,array('index'),array('index'));

		 //获取分类数据
		 $cat_id = I('get.cat_id');
		 $jgoods = D('Goods')->category_goods($cat_id);
		 $this->assign('jgoods',$jgoods);
		 $this->display();
	}

	//食品健康
	public function food() {
		 $this->setPageInfo("网上购物,网上商城,家电,手机,电脑,服装,居家,母婴,美妆,个护,食品,生鲜","快乐购-专业的综合网上购物商城，为您提供正品低价的购物选择、优质便捷的服务体验。商品来自全球数十万品牌商家，囊括家电、手机、电脑、服装、居家、母婴、美妆、个护、食品、生鲜等丰富品类，满足各种购物需求。","食品健康",1,array('index'),array('index'));

		//获取分类数据
		 $cat_id = I('get.cat_id');
		 $sgoods = D('Goods')->category_goods($cat_id);
		
		 $this->assign('sgoods',$sgoods);
		 $this->display();
	}

	//品牌馆
	public function brand() {
		 $this->setPageInfo("网上购物,网上商城,家电,手机,电脑,服装,居家,母婴,美妆,个护,食品,生鲜","快乐购-专业的综合网上购物商城，为您提供正品低价的购物选择、优质便捷的服务体验。商品来自全球数十万品牌商家，囊括家电、手机、电脑、服装、居家、母婴、美妆、个护、食品、生鲜等丰富品类，满足各种购物需求。","品牌馆",1,array('index'),array('index'));

		 //获取品牌数据
		 $model = D('Goodsbrand');

		 /*----------定制分页---------- --*/

		 $count = $model->count();
		 $page = 6;
		 $pageModel = new \Think\Page($count,$page);

		 //定义样式
		 $pageModel->setConfig('prev','上一页');
		 $pageModel->setConfig('next','下一页');
		 $pageModel->setConfig('first','首页');
		 $pageModel->setConfig('last','末页');
		 $pageModel->rollPage = 3;

		 //偏移量
		 $offset = $pageModel->firstRow;

		 //链接
		 $pagestr = $pageModel->show();
		 $brand = $model->order('brand_id desc')->limit("{$offset}","{$page}")->select();

		 
		$this->assign('pagestr',$pagestr);
		$this->assign('brand',$brand);
		$this->display();
	}
}
