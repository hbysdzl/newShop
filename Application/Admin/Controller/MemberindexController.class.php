<?php 

//会员管理控制器

namespace Admin\Controller;

class MemberindexController extends BackController {


	//会员列表

	public function index() {

		//查出数据
		$memberModel = M('member');

		$where['status'] = '1';
		//搜索
		if(I('get.member_name') != ''){
			$where['nikname'] = array('like','%'.I('get.member_name').'%'); 
		}
		
		$data = $memberModel->where($where)->order('addtime desc')->select();
        
        //获取会员级别数据
        $level = M('number_level')->select();

        foreach ($data as $k => $v) {
        	foreach ($level as $k1 => $v1) {
        		if ($v['jyz'] >= $v1['bottom_num'] && $v['jyz'] <= $v1['top_num']) {
        			
        			$data[$k]['level'] = $v1['level_name'];
        			$data[$k]['rate'] = $v1['rate'];

        		}
        	}
        }

		$this->assign('data',$data);
		$this->display();
	}

	//会员删除
	public function delete() {

		$id = I('get.id');
		$memberModel = M('member');
		$result = $memberModel->where('id='.$id)->save(array('status'=>'-1'));

		if($result){
			$this->ajaxReturn(array('status'=>'1'));
		}else{
			$this->ajaxReturn(array('status'=>'0'));
		}
	}

}