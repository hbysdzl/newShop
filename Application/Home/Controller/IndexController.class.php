<?php
// 首页
namespace Home\Controller;

class IndexController extends BackController {
    
    public function index(){
        
        

        $this->setPageInfo("网上购物,网上商城,家电,手机,电脑,服装,居家,母婴,美妆,个护,食品,生鲜","快乐购-专业的综合网上购物商城，为您提供正品低价的购物选择、优质便捷的服务体验。商品来自全球数十万品牌商家，囊括家电、手机、电脑、服装、居家、母婴、美妆、个护、食品、生鲜等丰富品类，满足各种购物需求。","快乐购首页",1,array('index'),array('index'));

        
        $memcache = new \Memcache();
        $memcache -> connect('127.0.0.1',11211);
        
        $goodsModel=D('Goods');
        
        //精品服饰
        $goods_best = unserialize($memcache -> get('goodsBest'));
        if ($goods_best === false) {
            $goods_best=$goodsModel->get_is_best();
            $memcache -> set('goodsBest',serialize($goods_best),0,14400);
        }
        
        //品类精选
        $goods_cat_list = unserialize($memcache -> get('goodsCatList'));

        if ($goods_cat_list === false) {
            $goods_cat=M('goodscategory');
            $goods_cat_list=$goods_cat->where('is_selection=1')->limit('10')->select();
            $memcache -> set('goodsCatList',serialize($goods_cat_list),0,14400);
        }
        


        //限时抢购
        $goods_promote=$goodsModel->get_is_promote('5');
        
        //限时抢购倒计时
        $goods_promote_time=$goodsModel->get_is_promote('10','desc');

        //今日发现
        $goods_new_day = unserialize($memcache -> get('goodsNewDay'));
        if ($goods_new_day === false) {
            $goods_new_day=$goodsModel->get_is_day();
            $memcache -> set('goodsNewDay',serialize($goods_new_day),0,3600);  
        }
          
        
        $this->assign('goods_promote_time', $goods_promote_time);
        $this->assign('goods_cat_list',$goods_cat_list);
        $this->assign('goods_new_day',$goods_new_day);
        $this->assign('goods_best',$goods_best);
        $this->assign('goods_promote',$goods_promote);
        $this->assign('goods_hot',$goods_hot);
	    $this->display();
    }
    
    public function test(){
        //测试
        $this->setPageInfo("测试","测试","测试",0,array('a','b','c'),array('d','e','f'));
        $this->display();
    }
}
