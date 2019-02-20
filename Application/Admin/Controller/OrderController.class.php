<?php
/*
 *后台订单管理控制器
 * */

namespace Admin\Controller;
use Admin\Controller\BackController;

class OrderController extends BackController{
    
    //订单列表
    public function index(){
        
        $this->setBent('商品列表', ' ', ' ');
        
        //获取订单信息
        $data = M('order')->alias('t1')->field('t1.*,t2.true_name,t2.province,t2.city,t2.town,t2.address,t2.tel_phone,t3.email')->join('left join php2018_orderpath as t2 on t1.addr_id=t2.id left join php2018_member as t3 on t1.member_id=t3.id')->order('t1.addtime desc')->select();
        
        $this->assign('data',$data);
        $this->display();
    }
    
    //订单发货
    public function delive(){
        
        if(IS_GET){
            //显示模板
            $orderID=I('get.orderid');
            $this->setBent('订单详情', ' ', ' ');
            
            //获取订单信息
            $data = M('order')->alias('t1')->field('t1.*,t2.true_name,t2.province,t2.city,t2.town,t2.address,t2.tel_phone,t3.email')->join('left join php2018_orderpath as t2 on t1.addr_id=t2.id left join php2018_member as t3 on t1.member_id=t3.id')->where('t1.id='.$orderID)->find();


            //获取订单商品信息
            $orderGoods = M('OrderGoods')->alias('t1')->field('t1.*,t2.goods_name,t2.logo')->join('left join php2018_goods as t2 on t1.goods_id=t2.goods_id')->where('order_id='.$orderID)->select();
            
            $memcache = new \Memcache();
            $memcache->connect('127.0.0.1','11211');
            $selectKuai = $memcache->get('com');

            if($selectKuai === false) {
                //获取快递公司代号
                $url = "http://v.juhe.cn/exp/com?key=899fe70f6da4ecf9667c897618bc71c4";
                $result = file_get_contents($url);
                $selectKuai = json_decode($result,true);
                $memcache->set('com',$selectKuai,0,14400);
            }

            
            $this->assign('selectKuai',$selectKuai['result']);
            $this->assign('data',$data);
            $this->assign('orderGoods',$orderGoods);
            $this->display();
        }else{
        
            //发货
            $orderID=I('post.id');
            $orderModel=M('order');
            $info=$orderModel->field('post_status')->find($orderID);
            if($info['post_status'] > 1){
                $this->error('该订单已完成发货');
            }
            if($orderModel->create(I('post.'),2)){
                if($orderModel->save()){
                    $this->success('发货成功',U('index'));
                    die();
                }
            }
            $this->error($orderModel->getError());
        }
    }
}