<?php
/*
 * 会员中心 显示订单信息控制器
 * */

namespace Home\Controller;
use Home\Controller\BackController;

class CenterController extends BackController{
 
        //订单列表
        public function orderLst(){
            
            //判断如果会员未登录则调回登陆页面
            if(!session('id')){
                $this->success('请先登陆',U('Member/login'));
                return ;
            }
            
            //设置页面基本信息
            $this->setPageInfo('我的订单','我的订单','会员中心',0,array('cart'),array('cart1'));
            
            //获取当前会员的订单信息
            $user_id=session('id');
            $orderModel=M('order');
            $orderwhere['member_id'] = $user_id;
            $orderwhere['status'] = '1';
            $orderlist=$orderModel->where($orderwhere)->select();

            
            //获取订单商品信息
            $ordergoods = M('order_goods')->alias('t1')->field('t1.*,t2.goods_name,t2.logo')->join('left join php2018_goods as t2 on t1.goods_id=t2.goods_id')->where($orderwhere)->select();

            foreach ($orderlist as $k => $v) {
                foreach ($ordergoods as $k1 => $v1) {
                    if($v1['order_id'] == $v['id']){
                        $orderlist[$k]['chlid'][] = $v1;
                    }
                }
            }

            $this->assign('order_list',$orderlist);

            $this->display();
        }
        
        /**
         * 订单物流状态
         * */
        public function express(){
            $orderID=I('get.orderid');
            if(!$orderID){
                $this->error('参数错误');
            }
            $orderModel=M('order');
            $info=$orderModel->field('com,no')->find($orderID);
            //调用快递接口
            $key='899fe70f6da4ecf9667c897618bc71c4';
            $url="http://v.juhe.cn/exp/index?key=".$key."&com=".$info['com']."&no=".$info['no'];
            $data=file_get_contents($url);
            
            $data=json_decode($data,true);
            
            //将数组倒序
            $data = array_reverse($data['result']['list']);

            $this->assign('data',$data);
           
            $this->display();
            
        }


        //确认收货
        public function confirm() {

            if(IS_POST){

                $data = I('post.');
                $result = M('order')->save($data);

                if($result) {
                    echo '1';
                }else{
                    echo '0';
                }


            }else{
                $orderid = I('get.orderid');
                //获取订单信息
                $order = M('order')->alias('o')->field('o.*,a.true_name,a.province,a.city,a.town,a.address,a.tel_phone')->join('left join php2018_orderpath as a on o.addr_id=a.id')->where('o.id='.$orderid)->find();
            
                //获取商品信息
                $goods = M('OrderGoods')->alias('o')->field('o.*,g.goods_name,g.logo,g.promote_start_time,g.promote_end_time')->join('left join php2018_goods as g on o.goods_id=g.goods_id')->where('o.order_id='.$orderid)->select();

                $this->assign('goods',$goods);
                $this->assign('order',$order);
                $this->display();
            }
        }

        //收货地址管理
        public function newaddr() {
            $model = D('Orderpath');
            if(IS_POST){
                //新增
                
                if($model->create(I('post.'))){
                    $model->addtime = time();
                    $model->member_id = session('id');
                    if($model->add()){
                        $this->ajaxReturn(array('status'=>'1'));
                    }
                }
                
                $this->ajaxReturn(array('status'=>'0','error'=>$model->getError()));

            }else{

                //设置页面基本信息
                $this->setPageInfo('我的收货地址','我的收货','会员中心',0,array('cart'),array('cart1'));

                //获取当前会员已有数据
                $addrdata = $model->where('member_id='.session('id'))->select();
                $this->assign('addrdata',$addrdata);
                $this->display();
            }
            
        }


        //商品收藏
        public function userindex() {

            //设置页面基本信息
            $this->setPageInfo('我的收藏','我的收藏','会员中心',0,array('cart'),array('cart1'));
            $this->display();

        }


        //个人资料
        public function usersub() {

            if(IS_POST){

                $data = I('post.');
                
                if($data['face'] == ''){
                    unset($data['face']);
                }

                $result = M('member')->save($data);

                if($result === false){
                    $this->ajaxReturn(array('status'=>'0'));
                }else{
                    $this->ajaxReturn(array('status'=>'1'));
                }
            }else{

                //设置页面基本信息
                $this->setPageInfo('个人资料','个人资料','会员中心',0,array('cart'),array('cart1'));
                $this->display();
            }
             

            
        }

        //头像上传
        public function uploadFace() {

           // $file = $_FILES['file'];
            $result = UploadOne('file','memberface/');

            if($result['img'] == 1){

                $this->ajaxReturn(array('status'=>'1','filepath'=>C('IMG_URL').$result['images'][0]));
            }else{
                $this->ajaxReturn(array('status'=>'0','error'=>$result['error']));
            }
            
        }


        //退款申请
        public function usertui() {

            //设置页面基本信息
            $this->setPageInfo('退款申请','退款申请','会员中心',0,array('cart'),array('cart1'));
            $this->display();
        }


}