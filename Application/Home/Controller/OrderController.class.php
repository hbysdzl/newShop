<?php
namespace Home\Controller;
use Home\Controller\BackController;
class OrderController extends BackController{
    
    //订单列表
    public function orderList(){
          
         /*------将选中的商品保存到session中，如果没有选中商品则不可进去此页面----*/        
        $bythis=I('post.bythis');
        if(!$bythis){
            //从session中取出
            $bythis=session('bythis');
            if(!$bythis){
                $this->error('请选择需要购买的商品');
            }
        }else{
            session('bythis',$bythis);
        }
        
        //如果未登录则跳转到登录页面
        $m_id=session('id');
        if (!$m_id){
            session('returnUrl',U('Order/orderList'));
            redirect(U('Member/login'));
        }
        
        //获取收货地址信息
        $addrpath = M('orderpath')->where('member_id='.session('id'))->select(); 
        //获取购物车中的商品
        $cartModel=D('Cart');
        $data=$cartModel->cartList();
        //处理被选中购买的商品
        $arr=array();
        foreach ($data as $k=>$v){
              $_v=$v['goods_id'].'-'.$v['goods_attr_id'];
              if (in_array($_v,session('bythis'))){
                  $arr[]=$v;
              }
        }
        $this->assign('addrpath',$addrpath);
        $this->assign('arr',$arr);
        //设置页面信息
        $this->setPageInfo('订单列表','订单列表','订单列表',0,array('fillin'), array('cart2'));
        $this->display();
    }
    
    //下订单
    public function xiaOrder(){ 
        //开始下订单
        if(IS_POST){
            $orderModel=D('Order');
            if($orderModel->create($_POST)){
                if($orderID=$orderModel->add()){
                    echo json_encode(array('ok'=>1,'orderID'=>$orderID));
                    die();
                }
            }
           echo json_encode(array('ok'=>0,'error'=>$orderModel->getError())); 
        }
    }

    //下单成功页面
    public function orderSucceed() {
        //设置页面信息
        $this->setPageInfo('订单提交成功','订单提交成功','订单提交成功',0,'','');

        $order_id = I('get.order');
        //获取订单信息
        $orderModel=D('Order');
        $orderdesc = $orderModel->find($order_id);
        $this->assign('orderdesc',$orderdesc);
        $this->display();
    }


    //删除订单
    public function delorder() {
         $id = I('get.id');
         $orderModel=D('Order');
         $data['status'] = '-1';
         $res = $orderModel->where('id='.$id)->save($data);


         if($res){
            $this->ajaxReturn(array('status'=>'1'));
         }else{

            $this->ajaxReturn(array('status'=>'0','error'=>$orderModel->getError()));
         }
    }


    //删除未支付的订单中的商品
    public function delorderGoods() {
        $data = I('get.');

        $model = D('Order');

        $result = $model->delorderGoods($data);

        if($result == '1') {
            $this->ajaxReturn(array('status'=>'1'));
        }else if($result == '0'){
            $this->ajaxReturn(array('status'=>'0','error'=>'该订单只有一件商品,不可删除'));
        }else{
            $this->ajaxReturn(array('status'=>'-1','error'=>$model->getError()));
        }
    }

}