<?php
namespace Home\Model;
use Think\Model;

class CommentModel extends Model{
    
    //发表评论时允许提交的字段
    protected $insertFields=array('grade','content','goods_id');
    
    //自动验证表单
    protected $_validate=array(
        array('grade', 'require','请选择评分',1,'regex',1),
        array('content','require', '评价内容不能为空',1,'regex',1)
    );
    
    
    public function commentAdd($data) {
        $data['member_id'] = cookie('members')['id'];
        
        //计算平均分值
        $data['grade'] = round(($data['desc_grade'] + $data['serve_grade'] + $data['wuliu_grade'])/3,1);
        $data['addtime'] = time();
        if(isset($data['img'])){
            $pic = $data['img'];
        }

        //处理为完成状态
        M('order')->where('id='.$data['order_id'])->save(array('post_status'=>'4'));

        unset($data['desc_grade'],$data['serve_grade'],$data['wuliu_grade'],$data['img']);

        $res = $this->add($data);

         //将评论晒图入库
        if(isset($pic)){
            $picModel = M('CommentPic');
            foreach ($pic as $k => $v) {
                $picModel->add(array('img'=>$v,'comment_id'=>$res,'goods_id'=>$data['goods_id']));
            }
        }

        return $res;
    }


    //商品评论记录
    public function selComment($goods_id) {

        //评论内容
        $result = $this->alias('p')->field('p.*,m.nikname,m.face')->join('left join php2018_member as m on p.member_id=m.id')->where('goods_id='.$goods_id)->order('addtime desc')->select();

        //评论图
        $commentImg = M('CommentPic')->where('goods_id='.$goods_id)->select();

        foreach ($result as $k => $v) {
            foreach ($commentImg as $k1 => $v1) {
               if ($v1['comment_id'] == $v['id']) {
                    $result[$k]['pic'][] = $v1;
               }
            }
        }

        //商家客服回复
        $reply = M('reply')->select();

        foreach ($result as $k => $v) {
            foreach ($reply as $k1 => $v1) {
                if ($v1['comment_id'] == $v['id']) {
                    
                    $result[$k]['reply'] = $v1['content'];
                }
            }
        }
        return $result;
    }

    
} 