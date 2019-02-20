<?php
namespace Home\Model;
use Think\Model;

class ReplyModel extends Model{
    
    //自动验证表单
    protected $_validate=array(
                array('content','require','回复内容不能为空',1,'regex',1)    
    );
    
    //添加之前
    protected function _before_insert(&$data, $options){
                $data['addtime']=time();
                $data['member_id']=session('id');
    }
}