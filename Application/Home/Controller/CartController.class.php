<?php
namespace Home\Controller;

use Home\Controller\BackController;
class CartController extends BackController{
    
    //加入购物车
    public function add(){
           
        $cartModel=D('Cart');
        $goodsCart=I('post.');

        //判断商品是否有属性
        if(isset($goodsCart['goods_attr_id'])){
            //将属性ID升序排列,并拼接为字符串
            sort($goodsCart['goods_attr_id']);
            $goodsAttId=implode(',',$goodsCart['goods_attr_id']);
            
        }else {
            $goodsAttId = '';
        }
        //检查库存量
        $this->Goodsinventory($goodsCart['goods_id'],$goodsAttId,$goodsCart['goods_number']);
        
        $cartModel->addToCart($goodsCart['goods_id'],$goodsAttId,$goodsCart['goods_number']);
        echo json_encode(array('ok'=>1));
    }
    
    //购物车列表
    public function cartList(){
        //设置页面基本信息
        $this->setPageInfo('购物车','购物车','购物车',0,array('cart'),array('cart1'));
        $cartModel=D('Cart');
        $data=$cartModel->cartList();
        
        $this->assign('data',$data);
        $this->display();
    }
    
    //修改购物车中的商品数量
    public function ajaxGoodsNum(){
        $data=I('get.');
        
        //检查库存量
        $this->Goodsinventory($data['goods_id'],$data['goods_attr_id'],$data['goods_number']);
        
        $cartModel=D('Cart');
        $cartModel->CartNum($data['goods_id'],$data['goods_attr_id'],$data['goods_number']);
    }
    
    //检查商品库存量
    public function Goodsinventory($goods_id,$goods_attr_id,$goods_number){
        //检查库存量
        $where['goods_id']=$goods_id;
        if($goods_attr_id != ''){
            $where['goods_attr_id']=$goods_attr_id;
        }
        $data= M('GoodsNumber')->where($where)->find();
        if ($data['goods_number']<$goods_number){
            echo json_encode(array('ok'=>0,'error'=>'货源不足'));
            die();
        }
    }
}