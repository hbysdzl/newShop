<?php 

/***
****评价管理
****/

namespace Admin\Controller;

class EvaluateController extends BackController {


	//评价列表
	public function index() {

		//搜索
		$where['p.status'] = '1';
		$res = I('get.goods_name');
		if(isset($res)){
			$where['g.goods_name'] = array('like','%'.$res.'%');
		}

		$model = D('Evaluate');
		$data = $model->getdata($where);

		$this->assign('data',$data);
		$this->display();
	}

	//查看评价
	public function view_desc() {

		if (IS_POST) {
			
			$data = I('post.');
			$data['addtime'] = time();
			$replyModel = M('reply');
			if($replyModel->add($data)) {
				echo '1';
			}else{
				echo '0';
			}
		}else{
			
			$id = I('get.id');
			$model = D('Evaluate');
			$data = $model->getdesc($id);

			//判断当前评价是否有回复
			$where['member_id'] = $data['member_id'];
			$where['comment_id'] = $data['id'];
			$is = M('reply')->where($where)->find();


			$this->assign('is',$is);
			$this->assign('data',$data);
			$this->display();
		}
		
	}


}