<?php
namespace Admin\Model;
use Think\Model;
class AdminModel extends Model
{
	protected $insertFields = array('username','password','is_use','cpassword','role_id');
	protected $updateFields = array('admin_id','username','password','is_use','cpassword');
	protected $_validate = array(
		array('username', 'require', '账号不能为空！', 1, 'regex', 3),
	    array('username', '', '账号已存在，请重新输入！', 1, 'unique', 3),
		array('username', '1,30', '账号的值最长不能超过 30 个字符！', 1, 'length', 3),
		array('password', 'require', '密码不能为空！', 1, 'regex', 1),
	    array('cpassword', 'password', '密码不一致，请重新输入！', 1, 'confirm', 3),
		array('is_use', 'number', '是否启用 1：启用0：禁用必须是一个整数！', 2, 'regex', 3),
	);
	public function search($pageSize = 3)
	{
		/**************************************** 搜索 ****************************************/
		$where = array();
		if($username = I('get.username'))
			$where['username'] = array('like', "%$username%");
		if($is_use = I('get.is_use'))
			$where['is_use'] = array('eq', $is_use);
		/************************************* 翻页 ****************************************/
		$count = $this->alias('a')->where($where)->count();
		$page = new \Think\Page($count, $pageSize);
		// 配置翻页的样式
		$page->setConfig('prev', '上一页');
		$page->setConfig('next', '下一页');
		$data['page'] = $page->show();
		/************************************** 取数据 ******************************************/
		$data['data'] = $this->alias('a')->where($where)->group('a.admin_id')->limit($page->firstRow.','.$page->listRows)->select();
		return $data;
	}
	// 添加前
	protected function _before_insert(&$data, $option){
	    //密码加密入库
	   $data['password']=md5($data['password'].C('MD5_KEY'));
	}

	// 添加后
	protected function _after_insert(&$data, $option){
	    $roleId = I('post.role_id');
	    if($roleId) {
	        $arModel = M('AdminRole');
	        foreach ($roleId as $v)
	        {
	            $arModel->add(array(
	                'admin_id' => $data['admin_id'],
	                'role_id' => $v,
	            ));
	        }
	    }

	}
	// 修改前
	protected function _before_update(&$data, $option){
            //如果密码为空则不修改
            if(empty($data['password'])){
                unset($data['password']);
            }else {
                $data['password']=md5($data['password'].C('MD5_KEY'));
            }
            //如果是当前为超级管理员则必须为启用状态
            if ($option['where']['admin_id']==1 && $data['is_use']==0){
                $this->error="超级管理员必须为启用状态";
                return false;
            }
            //清除当前管理员原属角色
            $admin_role_model=M('AdminRole');
            $admin_role_model->where(array('admin_id'=>array('eq',$option['where']['admin_id'])))->delete();
            //将更新后的重新添加
            $roleID=I('post.role_id');
            if ( $roleID){
                foreach ($roleID as $v){
                    $admin_role_model->add(array(
                        'admin_id'=>$option['where']['admin_id'],
                        'role_id'=>$v
                    ));
                }
            }


	}
	// 删除前
	protected function _before_delete($option){
	    //超级管理员不可删除
	    if($option['where']['admin_id']==1){
	        $this->error="超级管理员无法删除！";
	        return false;
	    }
	    //将当前管理员所属角色一并删除
	    $admin_role_model=M('AdminRole');
	    $admin_role_model->where(array('admin_id'=>array('eq',$option['where']['admin_id'])))->delete();
		if(is_array($option['where']['admin_id']))
		{
			$this->error = '不支持批量删除';
			return FALSE;
		}
	}
	/************************************ 其他方法 ********************************************/
}