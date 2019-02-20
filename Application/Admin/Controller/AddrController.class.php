<?php 

/***
	收货地址管理
**/

namespace Admin\Controller;

class AddrController extends BackController {

	//地址列表

	public function index() {

		//获取数据
		$data = M('orderpath')->alias('t1')->field('t1.*,t2.nikname')->join('left join php2018_member as t2 on t1.member_id=t2.id')->order('t1.addtime desc')->select();

		$this->assign('data',$data);
		$this->display();
	}


	//删除地址
	public function delete() {

		$id = I('get.id');
		$result = M('orderpath')->where('id='.$id)->save(array('status'=>'0'));
		
		if($result){
			$this->ajaxReturn(array('status'=>'1'));
		}else{
			$this->ajaxReturn(array('status'=>'0'));
		}
	}

	//查看地图
	public function map() {

		//获取地址信息
		$id = I('get.id');
		
		$path = M('orderpath')->find($id);

		$path = $path['province'].$path['city'].$path['town'].$path['address'];

		$this->assign('path',$path);

		$this->display();
	}
}