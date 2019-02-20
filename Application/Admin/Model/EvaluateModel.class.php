<?php 

/****
*** 
*** 评价管理模型
***/

namespace Admin\Model;
use Think\Model;


class EvaluateModel extends Model {



	//定义表名
	protected $tableName = 'comment';


	//获取评论数据
	public function getdata($where) {

		$data = $this->alias('p')->field('p.*,g.goods_name,m.nikname')->join('left join php2018_goods as g on p.goods_id=g.goods_id left join php2018_member as m on p.member_id=m.id')->order('p.addtime desc')->where($where)->select();

		return $data;
	}


	//查看评价
	public function getdesc($id) {

		$result = $this->alias('p')->field('p.*,GROUP_CONCAT(i.img) as img')->join('left join php2018_comment_pic as i on p.id=i.comment_id')->where('p.id='.$id)->group('p.id')->find();

		 $result['img'] = explode(',', $result['img']);

		 return $result;		

	}


}