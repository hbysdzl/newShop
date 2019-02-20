<?php
namespace Home\Model;
use Think\Model;

class OrderModel extends Model{

   
    //自动验证表单
    protected $_validate=array(
        array('addr_id','require','请填写您的收货地址','0','regex'),
    );

    //提交之前
    protected function _before_insert(&$data, $options){
        //判断购物车中是否有商品
        $CartModel=D('Cart');
        $gaData=$CartModel->cartList();
        if(count(count($gaData))==0){
            $this->error="请选选择需要购买的商品才能下单";
            return false;
        }

        //加锁机制，避免高并发下单时库存量出错
        $this->tp=fopen('./order.lock','r');
        flock($this->fp, LOCK_EX);
        //循环购物车中的商品
        $bythis=session('bythis');
        $tp=0;
        foreach ($gaData as $k=>$v){
            //判断商品是否是被选中购买的商品
            if(!in_array($v['goods_id'].'-'.$v['goods_attr_id'],$bythis)){
                continue;
            }
            //计算总价格
            $tp+=$v['price']*$v['goods_number'];
        }
        //补全订单信息
        $data['member_id']=session('id');
        $data['addtime']=time();
        $data['total_price']=$tp;


    }

     //处理订单商品表及减少库存量
    protected function  _after_insert(&$data, $options){

        $bythis=session('bythis');
        //处理订单商品表并减少库存量
        $CartModel=D('Cart');
        $gaData=$CartModel->cartList();
        $gnModel=M('GoodsNumber');
        $ogModel=M('OrderGoods');

        foreach($gaData as $k=>$v){
            //未选中购买的商品无需处理
            if(!in_array($v['goods_id'].'-'.$v['goods_attr_id'],$bythis)){
                continue;
            }
            $where['goods_id']=$v['goods_id'];
            $where['goods_attr_id']=$v['goods_attr_id'];
            $rs=$gnModel->where($where)->setDec('goods_number',$v['goods_number']);
            if($rs===false){
                //回滚事物
               // mysql_query('ROLLBACK');
                return false;
            }

            //插入到订单商品表
            $where['order_id']=$data['id'];
            $where['member_id']=session('id');
            $where['goods_attr_str']=$v['goods_attr_str'];
            $where['goods_price']=$v['price'];
            $where['goods_number']=$v['goods_number'];
            $rs=$ogModel->add($where);
            if ($rs===false){
                //回滚事物
               // mysql_query('ROLLBACK');
                return false;
            }

        }

        //mysql_query('COMMIT');//提交事物
        //释放锁机制
        flock($this->fp,LOCK_UN);
        fclose($this->fp);
        //清空购物车中所选择的购买的商品并删除session中保存的选中数据
        $CartModel->clearDb();
        session('bythis',null);
    }

    //删除未支付订单中的商品
    public function delorderGoods($data) {

        //判断商品数量
        $ordergoodsModel = M('OrderGoods');

        $wheregoods['order_id'] = $data['order_id'];
        $wheregoods['status'] = '1';
        $count = $ordergoodsModel->where($wheregoods)->count();
        if($count <= 1){
            return '0';
            exit;
        }

        //删除商品
        $ordergoodsModel->where($data)->save(array('status'=>'0'));
        $goods = $ordergoodsModel->where($data)->find();
        $where['goods_id'] = $goods['goods_id'];
        if($goods['goods_attr_id'] != ''){
            $where['goods_attr_id'] = $goods['goods_attr_id'];
        }

        //减少总价格
        $ss = $this->where('id='.$data['order_id'])->setDec('total_price',$goods['goods_price']*$goods['goods_number']);
        //添加库存
        M('GoodsNumber')->where($where)->setInc('goods_number',$goods['goods_number']);

        return '1';
        
    }
    
}