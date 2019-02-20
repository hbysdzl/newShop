<?php
//商品详情控制器
namespace Home\Controller;

use Home\Controller\BackController;
use DoctrineTest\InstantiatorTestAsset\ArrayObjectAsset;
class GoodsController extends BackController{
    
    //商品详情页面
    public function goods($goods_id){
      
        //获取商品基本信息
        $goods=M('goods')->find($goods_id);       
        //设置页面信息
        $this->setPageInfo($goods['seo_keyword'],$goods['seo_description'],$goods['goods_name'].'-详情',0,array('goods','common','jqzoom'), array('goods','jqzoom-core'));
        
        //设置面包屑
        $position = D('Goodscategory')->position($goods['cat_id']);

        //获取商品相册信息
        $goods_pic=M('GoodsPics')->where(array('goods_id'=>array('eq',$goods_id)))->select();
        
        //获取商品单选属性信息
        $attrModel=M('GoodsAttr');
        $gaattr=$attrModel->alias('a')->field('a.*,b.attr_name,b.attr_type')->join('left join php2018_goodsattribute as b on a.attr_id=b.attr_id')
                 ->where(array('a.goods_id'=>array('eq',$goods_id),'b.attr_type'=>array('eq',1)))->select();
        //将二维数组转三维
        $goods_attr=array();
        foreach ($gaattr as $k=>$v){
           $goods_attr[$v['attr_name']][]=$v;
        }
        
        //获取商品唯一属性信息
        $w_attr=$attrModel->alias('a')->field('a.*,b.attr_name,b.attr_type')->join('left join php2018_goodsattribute as b on a.attr_id=b.attr_id')
        ->where(array('a.goods_id'=>array('eq',$goods_id),'b.attr_type'=>array('eq',0)))->select();

       //品牌
        $brand = M('goods')->alias('t1')->field('t1.brand_id,t2.brand_name')->join('left join php2018_goodsbrand as t2 on t1.brand_id=t2.brand_id')->find($goods_id);

      
        /*------------为您推荐-----------*/

        //查出当前商品的扩展分类
        $catids = M('GoodsCat')->field('GROUP_CONCAT(cat_id) cat_id')->where('goods_id='.$goods_id)->find();
        
        //查出扩展类下的所有商品id
        $goodsids = M('GoodsCat')->field('GROUP_CONCAT(goods_id) goods_id')->where(array('cat_id'=>array('in',$catids['cat_id'])))->find();
        
        //拼凑条件
        $extends = "or goods_id in ({$goodsids['goods_id']})";
        
        $where['cat_id'] = array('exp',"={$goods['cat_id']} $extends");
        $where['is_on_sale'] = array('eq','1');
        $where['is_delete'] = array('eq','0');
        $where['goods_id'] = array('neq',$goods_id);
        $recommendGoods = M('goods')->field('goods_id,goods_name,market_price,shop_price,logo')->where($where)->limit('10')->select();


        //获取评价
        $evaluateModel = D('Comment');
        $evaluate = $evaluateModel->selComment($goods_id);

        $this->assign('evaluate',$evaluate);
        $this->assign('brand',$brand);
        $this->assign('recommendGoods',$recommendGoods);
        $this->assign('w_attr',$w_attr);
        $this->assign('goods_attr',$goods_attr);
        $this->assign('goods_pic',$goods_pic);
        $this->assign('goods',$goods);
        $this->assign('position',$position);
        $this->display();
    }
    
    
    //计算会员价格+最近浏览
    
    public function ajaxGetPrice(){
        /*----------------计算会员价格-------------------*/
        $goods_id=I('get.goods_id');
        $priceModel=D('Goods');
       
        
        /*----------------最近浏览----------------------*/        
      //在COOKIE中存放一个数组，保存最近浏览的10件商品的ID （数组需序列化）
      $recentDisplay=isset($_COOKIE['recentDisplay'])? unserialize($_COOKIE['recentDisplay']):array();
        
      //把浏览过的商品ID放到数组的最前面 并需要去重
      array_unshift($recentDisplay, $goods_id);      
      $recentDisplay=array_unique($recentDisplay);
      
      if(count($recentDisplay)>10){
          $recentDisplay=array_slice($recentDisplay,0,10);
      }
      
      //把处理好的数组序列化保存到COOKIE中
      $aMoth=30*86400;//有效期一个月
      setcookie('recentDisplay',serialize($recentDisplay),time() + $aMoth,'/');
      echo  $priceModel->getNumberPrice($goods_id);
    }
    
    //获取最近浏览商品
    public function ajaxgetRecentlygoods(){
        //先从cookie中取出浏览商品的ID
        $recentDisplay=isset($_COOKIE['recentDisplay'])? unserialize($_COOKIE['recentDisplay']):array();
        
        if ($recentDisplay){
            $recentDisplay_str=implode(',' , $recentDisplay);
            $recentModel=M('Goods');
            $goods=$recentModel->field('goods_id,goods_name,sm_logo')->where(array('goods_id'=>array('in',$recentDisplay_str)))->select();
            echo json_encode($goods);
        
        }
    }
    
    
    //ajax商品评论表单
    public function ajaxGetComment(){
        $ret=array('login'=>0);
        $m_id=session('id');
        if ($m_id){
            $ret['login']=1;
        }
        
        echo json_encode($ret);
    }
}