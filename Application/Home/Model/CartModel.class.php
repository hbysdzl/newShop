<?php
namespace Home\Model;
use Think\Model;

class CartModel extends Model{
    
    //加入购物车
    public function addToCart($goods_id,$goods_attr_id,$goods_number){
        
           $m_id=session('id');
           //如果会员登录了则加入到数据库中，否则加入到Cookie中
           if($m_id){
               $data['goods_id']=$goods_id;
               $data['goods_attr_id']=$goods_attr_id;
               $data['member_id']=$m_id;
               
               //判断商品是否已经存在
               $has=$this->where($data)->find();
               if ($has){
                   $this->where('id='.$has['id'])->setInc('goods_number',$goods_number);
               }else {
                   $data['goods_number']=$goods_number;
                   $this->add($data);
               }
               
              
           }else {
               
              //先从Cookie中去出购物车的数组
              $cart=isset($_COOKIE['cart'])?unserialize($_COOKIE['cart']):array();
              //把商品放入到这个数组中
              $key=$goods_id.'-'.$goods_attr_id;
              
              if(isset($cart[$key])){
                  $cart[$key]+=$goods_number;
              }else {
                  $cart[$key]=$goods_number;
              }
              
              //重新存回COOKIE中
              $aMoth=30*86400;
              setcookie('cart',serialize($cart),time()+$aMoth,'/');
           }
    }
    
    //购物车列表
    public function cartList(){
        $m_id=session('id');
        if ($m_id){
            $_cart=$this->where(array('member'=>array('eq',$m_id)))->select();
            
        }else{
            $_cart_=isset($_COOKIE['cart'])? unserialize($_COOKIE['cart']):array();
            
            //将COOKIE中购物车商品转换为和数据库一样的二维数组结构
            $_cart=array();
            
            foreach ($_cart_ as $k=>$v){
                    $_k=explode('-',$k);//将商品ID及属性ID转换为数组
                    $_cart[]=array(
                        'goods_id'=>$_k[0],
                        'goods_attr_id'=>$_k[1],
                        'goods_number'=>$v,
                        'member_id'=>0
                    );
            } 
        }
        
        /*----------------------根据购物车中的商品ID及属性ID获取商品的详细信息-----------------------*/
        $goodsModel=D('Goods');
        foreach ($_cart as $k=>$v){
            $ginfo=$goodsModel->field('goods_name,sm_logo')->find($v['goods_id']);
            $_cart[$k]['goods_name']=$ginfo['goods_name'];
            $_cart[$k]['sm_logo']=$ginfo['sm_logo'];
            //计算商品价格
            $_cart[$k]['price']=$goodsModel->getNumberPrice($v['goods_id']);
            //把商品属性ID转化为商品属性字符串
            $_cart[$k]['goods_attr_str']=$goodsModel->goodsAttrvalue($v['goods_attr_id']);
        }
        
        return $_cart;
    }
    
    //会员登录成功后将购物车的商品由COOKIE转移到数据库
    public function MoveDataDb(){
        $m_id=session('id');
        if ($m_id){
            //从COOKIE取出购物车数据
           $cart=isset($_COOKIE['cart'])?unserialize($_COOKIE['cart']):array();
           if ($cart){
               //循环转移到数据库中
               foreach ($cart as $k=>$v){
                   $_k=explode('-',$k);
                   $this->addToCart($_k[0],$_k[1],$v);
               }
               
              //清空COOKIE
              setcookie('cart','',time()-1,'/');
           }
        }
    }
    
    //修改购物车中商品的数量
    public function CartNum($goods_id,$goods_attr_id,$goods_number){
                $m_id=session('id');
                if ($m_id){
                    //如果会员已登录则更新数据库
                    $where['member_id']=$m_id;
                    $where['goods_id']=$goods_id;
                    $where['goods_attr_id']=$goods_attr_id; 
                    if($goods_number==0){
                        $this->where($where)->delete();
                    }else{
                        
                        $this->where($where)->setField('goods_number',$goods_number);
                    }
                    
                    
                }else{
                    //未登录则更新Cookie
                    $cart=isset($_COOKIE['cart'])?unserialize($_COOKIE['cart']):array();
                    if ($cart){
                        $key=$goods_id.'-'.$goods_attr_id;
                        
                            if ($goods_number==0){
                                unset($cart[$key]);
                            }else {
                                
                                $cart[$key]=$goods_number; 
                            }   
                        $moth=30*86400;
                        setcookie('cart',serialize($cart),time()+$moth,'/');
                    }
                }
    }
    
    //清除购物车中已经购买的商品
    public function clearDb(){
        $bythis=session('bythis');
        foreach ($bythis as $k=>$v){
            $_v=explode('-',$v);
            $where['goods_id']=$_v[0];
            $where['goods_attr_id']=$_v[1];
            $this->where($where)->delete();
        }   
    }
}