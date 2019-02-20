<?php
namespace Admin\Model;
use Think\Model;
class GoodscategoryModel extends Model
{
	protected $insertFields = array('cat_name','parent_id','is_selection');
	protected $updateFields = array('cat_id','cat_name','parent_id','is_selection');
	protected $_validate = array(
		array('cat_name', 'require', '分类名称不能为空！', 1, 'regex', 3),
		array('cat_name', '1,30', '分类名称的值最长不能超过 30 个字符！', 1, 'length', 3),
		array('parent_id', 'number', '上级分类的ID，0：代表顶级必须是一个整数！', 2, 'regex', 3),
	);
	/************************************* 递归相关方法 *************************************/
	public function getTree()
	{
		$data = $this->select();
		return $this->_reSort($data);
	}
	private function _reSort($data, $parent_id=0, $level=0, $isClear=TRUE)
	{
		static $ret = array();
		if($isClear)
			$ret = array();
		foreach ($data as $k => $v)
		{
			if($v['parent_id'] == $parent_id)
			{
				$v['level'] = $level;
				$ret[] = $v;
				$this->_reSort($data, $v['cat_id'], $level+1, FALSE);
			}
		}
		return $ret;
	}
	public function getChildren($cat_id)
	{
		$data = $this->select();
		return $this->_children($data, $cat_id);
	}
	private function _children($data, $parent_id=0, $isClear=TRUE)
	{
		static $ret = array();
		if($isClear)
			$ret = array();
		foreach ($data as $k => $v)
		{
			if($v['parent_id'] == $parent_id)
			{
				$ret[] = $v['cat_id'];
				$this->_children($data, $v['cat_id'], FALSE);
			}
		}
		return $ret;
	}
	/************************************ 其他方法 ********************************************/
	public function _before_delete($option)
	{
		// 先找出所有的子分类
		$children = $this->getChildren($option['where']['cat_id']);
		// 如果有子分类都删除掉
		if($children)
		{
			$children = implode(',', $children);
			$this->execute("DELETE FROM php2018_goodscategory WHERE cat_id IN($children)");
		}
	}
	
	//添加之前
	public function _before_insert(&$data, $options){
	    
	    //处理筛选是属性
	    $search_attr=I('post.search_attr_id');
	    $arr=array();
	    foreach ($search_attr as $k=>$v){
	       if ($v==''){
	           unset($v);
	       }else {
	           $arr[]=$v;
	       }
	    }
	    $data['search_attr_id']=implode(',',$arr);
	    
	}
	
	//修改之前
	public function _before_update(&$data, $options){
	    //处理筛选是属性
	    $search_attr=I('post.search_attr_id');
	    $arr=array();
	    foreach ($search_attr as $k=>$v){
	        if ($v==''){
	            unset($v);
	        }else {
	            $arr[]=$v;
	        }
	    }
	    $data['search_attr_id']=implode(',',$arr);
	}
}