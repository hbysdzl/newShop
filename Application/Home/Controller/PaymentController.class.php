<?php
namespace Home\Controller;
use Home\Controller\BackController;


//订单支付管理控制器
class PaymentController extends BackController{
    
    //订单提交
    public function payIndex(){
     
        header("Content-type: text/html; charset=utf-8");
        //获取订单信息
        $id=I('get.order');
        $res=M('order')->find($id);

        //判断订单是否过期
        if($res['addtime']+2100 < time()) {
            $res['post_status'] = '-1';
            M('order')->save($res);

            $this->orderPast($id);
            $this->redirect('Cart/cartList',' ',1,'订单已超时，请重新购买！');
        }



        /*---------实现支付宝支付--------*/
        require_once './Api/alipay/config.php';
        require_once './Api/alipay/pagepay/service/AlipayTradeService.php';
        require_once './Api/alipay/pagepay/buildermodel/AlipayTradePagePayContentBuilder.php';
        
        //商户订单号，商户网站订单系统中唯一订单号，必填
        $out_trade_no = $res['id'];
        
        //订单名称，必填
        $subject ='测试商品';
        
        //付款金额，必填
        $total_amount =$res['total_price'];
        
        //商品描述，可空
        $body = '欢迎光临!';
        
        //构造参数
        $payRequestBuilder = new \AlipayTradePagePayContentBuilder();
        $payRequestBuilder->setBody($body);
        $payRequestBuilder->setSubject($subject);
        $payRequestBuilder->setTotalAmount($total_amount);
        $payRequestBuilder->setOutTradeNo($out_trade_no);
        
        $aop = new \AlipayTradeService($config);
        
        /**
         * pagePay 电脑网站支付请求
         * @param $builder 业务参数，使用buildmodel中的对象生成。
         * @param $return_url 同步跳转地址，公网可以访问
         * @param $notify_url 异步通知地址，公网可以访问
         * @return $response 支付宝返回的信息
         */
        $response = $aop->pagePay($payRequestBuilder,$config['return_url'],$config['notify_url']);
        //输出表单
        var_dump($response);
       
        
    }
    
    //付款成功后回跳的地址
    public function AlipayReturnUrl(){
        header("Content-type: text/html; charset=utf-8");
        /* *
         * 功能：支付宝页面跳转同步通知页面
         * 版本：2.0
         * 修改日期：2017-05-01
         * 说明：
         * 以下代码只是为了方便商户测试而提供的样例代码，商户可以根据自己网站的需要，按照技术文档编写,并非一定要使用该代码。
    
         *************************页面功能说明*************************
         * 该页面可在本机电脑测试
         * 可放入HTML等美化页面的代码、商户业务逻辑程序代码
         */
        require_once("./Api/alipay/config.php");
        require_once './Api/alipay/pagepay/service/AlipayTradeService.php';
    
    
        $arr=$_GET;
        $alipaySevice = new \AlipayTradeService($config);
        $result = $alipaySevice->check($arr);
        /* 实际验证过程建议商户添加以下校验。
         1、商户需要验证该通知数据中的out_trade_no是否为商户系统中创建的订单号，
         2、判断total_amount是否确实为该订单的实际金额（即商户订单创建时的金额），
         3、校验通知中的seller_id（或者seller_email) 是否为out_trade_no这笔单据的对应的操作方（有的时候，一个商户可能有多个seller_id/seller_email）
         4、验证app_id是否为该商户本身。
         */
        if($result) {//验证成功  
            $out_trade_no = htmlspecialchars($_GET['out_trade_no']);//商户订单号
            $trade_no = htmlspecialchars($_GET['trade_no']);//支付宝交易号
            
            //——请根据您的业务逻辑来编辑代码
            //将订单表设置为已支付的状态
            $order=M('order');
              $save['post_status']=1;
              $save['pay_method'] = 'alipay';
              $save['alipaid']=$trade_no;
            $order->where('id='.$out_trade_no)->save($save);
            
            $url=U('Center/orderLst');            
            echo "恭喜您支付成功！<br />订单号：".$out_trade_no."<br/>支付宝交易号：".$trade_no."<br/><a href='".$url."'>点击前往我的订单！</a>";
        }
        else {
            //验证失败
            echo "支付失败，请稍后重试！";
        }
    
    }


    //订单过期处理
    protected function orderPast($id) {

        $model = M('OrderGoods');
        //处理订单商品表
        $data['status'] = '0';
        $model->where('order_id='.$id)->save($data);

        //处理库存表
        $goods = $model->field('goods_id,goods_attr_id,goods_number')->where('order_id='.$id)->select();
        $numberModel = M('GoodsNumber');
        foreach ($goods as $k => $v) {
            $where['goods_id'] = $v['goods_id'];
            
            //如果没有属性就取消属性条件
            if ($v['goods_attr_id'] != '') {
               $where['goods_attr_id'] = $v['goods_attr_id'];
            }
            $numberModel->where($where)->setInc('goods_number',$v['goods_number']);
        }

    }
   
}