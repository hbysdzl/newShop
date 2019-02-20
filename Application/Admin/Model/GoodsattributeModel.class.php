<?php
namespace Admin\Model;
use Think\Model;
class GoodsattributeModel extends Model
{
	protected $insertFields = array('attr_name','attr_type','attr_option_values','type_id');
	protected $updateFields = array('attr_id','attr_name','attr_type','attr_option_values','type_id');
	protected $_validate = array(
		array('attr_name', 'require', '不能为空！', 1, 'regex', 3),
		array('attr_name', '1,30', '的值最长不能超过 30 个字符！', 1, 'length', 3),
		array('attr_type', 'number', '必须是一个整数！', 2, 'regex', 3),
		array('attr_option_values', '1,150', '的值最长不能超过 150 个字符！', 2, 'length', 3),
		array('type_id', 'require', '不能为空！', 1, 'regex', 3),
		array('type_id', 'number', '必须是一个整数！', 1, 'regex', 3),
	);
	public function search($typeID,$pageSize = 3)
	{
		/**************************************** 搜索 ****************************************/
		$where = array();
		//只取出当前商品类型下的所有属性
		 $where['type_id']=$typeID;
		if($attr_name = I('get.attr_name'))
			$where['attr_name'] = array('like', "%$attr_name%");
		if($attr_type = I('get.attr_type'))
			$where['attr_type'] = array('eq', $attr_type);
		/************************************* 翻页 ****************************************/
		$count = $this->alias('a')->where($where)->count();
		$page = new \Think\Page($count, $pageSize);
		// 配置翻页的样式
		$page->setConfig('prev', '上一页');
		$page->setConfig('next', '下一页');
		$data['page'] = $page->show();
		/************************************** 取数据 ******************************************/
		$data['data'] = $this->alias('a')->where($where)->group('a.attr_id')->limit($page->firstRow.','.$page->listRows)->select();
		return $data;
	}
	// 添加前
	protected function _before_insert(&$data, $option){
		//将可选值属性，统一转换为英文
		$data["attr_option_values"] = str_replace('，',',',$data["attr_option_values"]);
		
	}
	// 修改前
	protected function _before_update(&$data, $option){
		//将可选值属性，统一转换为英文
		$data["attr_option_values"] = str_replace('，',',',$data["attr_option_values"]);
	}
	// 删除前
	protected function _before_delete($option)
	{
		if(is_array($option['where']['attr_id']))
		{
			$this->error = '不支持批量删除';
			return FALSE;
		}
	}
	/************************************ 其他方法 ********************************************/
}