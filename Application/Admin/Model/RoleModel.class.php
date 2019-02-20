<?php
namespace Admin\Model;
use Think\Model;
class RoleModel extends Model
{
	protected $insertFields = array('role_name','pri_name');
	protected $updateFields = array('role_id','role_name','pri_name');

	protected $_validate = array(
		array('role_name', 'require', '角色名称不能为空！', 1, 'regex', 3),
	    array('role_name', '', '角色名称已存在，请重新输入！', 1, 'unique', 3),
		array('role_name', '1,30', '角色名称的值最长不能超过 30 个字符！', 1, 'length', 3),
	);
	public function search($pageSize = 3)
	{
		/**************************************** 搜索 ****************************************/
		$where = array();
		/************************************* 翻页 ****************************************/
		$count = $this->alias('a')->where($where)->count();
		$page = new \Think\Page($count, $pageSize);
		// 配置翻页的样式
		$page->setConfig('prev', '上一页');
		$page->setConfig('next', '下一页');
		$data['page'] = $page->show();
		/************************************** 取数据 ******************************************/
		//$data['data'] = $this->alias('a')->where($where)->group('a.role_id')->limit($page->firstRow.','.$page->listRows)->select();
		$data['data'] = $this->alias('a')->field('a.*,GROUP_CONCAT(c.pri_name) pri_name')->
		              join('LEFT JOIN php2018_role_privilege b ON a.role_id=b.role_id LEFT JOIN
		               php2018_privilege c ON b.pri_id=c.pri_id')->where($where)->group('a.role_id')->
		              limit($page->firstRow.','.$page->listRows)->select();

		return $data;
	}
	// 添加后
	protected function _after_insert(&$data, $option){
	    //接收权限ID
	    $pri_id=I('post.pri_name');
	    if($pri_id){
	        $pri_role=D('role_privilege');//实例化角色权限模型
	        foreach ($pri_id as $k=>$v){//写入到中间表
	            $pri_role->add(array(
	                'pri_id'=>$v,
	                'role_id'=>$data['role_id']
	            ));
	        }
	    }

	}
	// 修改前
	protected function  _before_update(&$data, $option){
      $rol=I('post.');
	  $role_pri=M('RolePrivilege');
	  //删除原有权限
	  $role_pri->where(array('role_id'=>array('eq',$rol['role_id'])))->delete();
	   //重新添加
	 // $pri=I('post.pri_name');
	   if ($rol['pri_name']){
	       foreach ($rol['pri_name'] as $k=>$v){
	           $role_pri->add(
	               array('role_id'=>$rol['role_id'],
	                      'pri_id'=>$v
	               ));
	       }
	   }


	}
	// 删除前
	protected function _before_delete($option){
        //判断是否有管理员属于这个角色
        $arModel=M('AdminRole');//实例化管理员角色表
        $num=$arModel->where(array('role_id'=>array('eq',$option['where']['role_id'])))->count();
        if ($num>1){
            $this->error="有管理员属于这个角色，无法删除";
            return false;
        }
        //将当前角色所属的权限一并删除
        $prModel=M('RolePrivilege');//实例化角色权限表
        $prModel->where(array('role_id'=>array('eq',$option['where']['role_id'])))->delete();
	    if(is_array($option['where']['role_id']))
	    {
	        $this->error = '不支持批量删除';
	        return FALSE;
	    }
	}
	/************************************ 其他方法 ********************************************/
}