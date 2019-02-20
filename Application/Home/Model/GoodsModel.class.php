<?php
namespace Home\Model;
use Think\Model;
use Think\Page;

class GoodsModel extends Model{
    //限时抢购
    public function get_is_promote($day,$sort='asc'){
        //获取当前时间
        $date=time();
        return $this->field('goods_id,goods_name,promote_price,logo,market_price,promote_end_time')->order("sort_num {$sort}")->where(array(
            'is_promote'=>array('eq',1),
            'is_on_sale'=>array('eq',1),
            'is_delete'=>array('eq',0),
            'promote_start_time'=>array('elt',$date),
            'promote_end_time'=>array('gt',$date),
            ))->limit("{$day}")->select();
    }
    
    //热卖商品
    public function get_is_hot(){
        return $this->field('goods_id,goods_name,shop_price,logo')->order('sort_num')->
             where(array(
                'is_hot'=>array('eq',1),
                'is_delete'=>array('eq',0),
                'is_on_sale'=>array('eq',1),
             ))->select();
    }
    
    //精品服饰
    public function get_is_best(){
        return $this->field('goods_id,goods_name,shop_price,logo,market_price')->order('sort_num')->
        where(array('is_best'=>array('eq',1),
                    'is_delete'=>array('eq',0),
                    'is_on_sale'=>array('eq',1),
                    'cat_id'   =>array('eq',6),
        ))->limit('5')->select();
    }
    
    //今日发现
    public function get_is_day(){
        return $this->field('goods_id,goods_name,shop_price,logo,market_price')->order('addtime desc')->
        where(array(
            'is_new'=>array('eq',1),
            'is_delete'=>array('eq',0),
            'is_on_sale'=>array('eq',1),
             
        ))->select();
    }
    
    //电脑办公推荐商品
    public function get_diannao($id){
        return $this->field('goods_id,goods_name,shop_price,logo')->order('sort_num')->
            where(array('is_best'=>array('eq',1),
                        'is_delete'=>array('eq',0),
                        'is_on_sale'=>array('eq',1),
                        'cat_id'=>array('in',$id)
             
        ))->select();
    }
    
    //计算会员价格
    
    public function getNumberPrice($goods_id){
        $now=time();
        //判断当前商品是否有促销价
        $price=$this->field('shop_price,is_promote,promote_price,promote_start_time,promote_end_time')->find($goods_id);
        
        if ($price['is_promote']==1 && $price['promote_start_time']<$now && $price['promote_end_time']>$now){
            //在促销期间，按促销价计算
            return $price['promote_price'];
        }
        
        if(!session('id')){
            //如果会员没有登录则使用本店价格
            return $price['shop_price'];
        }
        
        //计算会员价格
        $mpprice=M('NumberPrice')->field('price')->where(array('goods_id'=>array('eq',$goods_id),'level_id'=>array('eq',session('level_id'))))->find();
        
        if ($mpprice['price']!=0){
            return $mpprice['price'];
        }else {
            //如果没有会员价格则按当前会员级别的折扣率计算
            return $price['shop_price']*session('rate');
        }
        
    }
    //将属性ID转换为字符串
    public function goodsAttrvalue($goods_attr_id){
            if($goods_attr_id){
                $sql="SELECT GROUP_CONCAT(CONCAT(b.attr_name,':',a.attr_value) SEPARATOR '<br/>') as gastr FROM php2018_goods_attr as a LEFT JOIN
                php2018_goodsattribute as b on a.attr_id=b.attr_id WHERE a.id in(".$goods_attr_id.")";
                $res=$this->query($sql);
                return $res[0]['gastr'];
            }else {
                return '';
            }
    }
    
    
    //搜索商品
    public function search_goods($cat_id){
        
        //取出扩展分类下的商品ID
        $extgoods=M('GoodsCat')->field('GROUP_CONCAT(goods_id) goods_id')->where(array('cat_id='.$cat_id))->find();
        
        if($extgoods['goods_id']){
            $extGoodsData=" OR a.goods_id IN({$extgoods['goods_id']})";
        }else{
            $extGoodsData='';
        }
        
        $where=array(
            'a.cat_id'=>array('exp',"=$cat_id $extGoodsData"),
            'a.is_on_sale'=>array('eq',1),
            'a.is_delete'=>array('eq',0),
            );
        //按品牌搜索
        if($brand=I('get.brand')){
            $where['brand_id']=array('eq',$brand);
        }
        
        //按价格搜索
        if($price=I('get.price')){
           //将字符串转换为数组
           $price=explode('-',$price);
           $where['shop_price']=array('BETWEEN',$price[0].','.$price[1]);
        }
        
        $Goods=array();//存在分页及商品信息的数组
        //定制分页
        $totalRows=$this->alias('a')->where($where)->count();//获取总的记录数
        $page=new Page($totalRows,12);
        $page->setConfig('prev', '上一页');
        $page->setConfig('next', '下一页');
        $page->rollPage   = 5;// 分页栏每页显示的页数
        $startPage=$page->firstRow;//分页偏移量；
        $listPage=$page->listRows; //单页显示行数
        $pageData=$page->show();
        $Goods['page']=$pageData;
        
        //获取分类下的商品信息
        $Goods['data']=$this->alias('a')->field('goods_name,shop_price,sm_logo')->where($where)->limit($startPage.','.$listPage)->select();
        
        return $Goods;
    }



    //商品分类列表
    public function category_goods($cat_id) {

        //取出扩展分类下的商品id
        $extendGoods = M('GoodsCat')->field('GROUP_CONCAT(goods_id) goods_id')->where('cat_id='.$cat_id)->find();
        if ($extendGoods['goods_id'] == null) {
            $extendGoods = '';
        }else{
            $extendGoods = "or goods_id in ({$extendGoods['goods_id']})";
        }
        

        //拼凑条件
        $where['cat_id'] = array('exp',"=$cat_id $extendGoods");
        $where['is_on_sale'] = array('eq','1');
        $where['is_delete'] = array('eq','0');


        //定制分页
        $goods = array();
        $totalRows = $this->where($where)->count(); //总记录数
        $pageCount = 3;
        $pageModel = new Page($totalRows,$pageCount); //每页显示记录数
        $pageModel->setConfig('prev','上一页');
        $pageModel->setConfig('next','下一页');
        $pageModel->rollPage = 5;
        $pageModel->lastSuffix = false;
        $offset = $pageModel->firstRow;
        $pagesize = $pageModel->listRows;
        $goods['pagestr'] = $pageModel->show();
        $goods['goods'] = $this->field('goods_id,goods_name,shop_price,market_price,logo')->where($where)->order('addtime desc')->limit("{$offset}","{$pagesize}")->select();


        return $goods;
    }
}