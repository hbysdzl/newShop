<?php
namespace Admin\Model;
use Think\Model;
class GoodsbrandModel extends Model
{
	protected $insertFields = array('brand_name','site_url');
	protected $updateFields = array('brand_id','brand_name','site_url');
	protected $_validate = array(
		array('brand_name', 'require', '品牌名称不能为空！', 1, 'regex', 3),
		array('brand_name', '1,45', '品牌名称的值最长不能超过 45 个字符！', 1, 'length', 3),
		array('site_url', 'require', '品牌网站地址不能为空！', 1, 'regex', 3),
		array('site_url', '1,150', '品牌网站地址的值最长不能超过 150 个字符！', 1, 'length', 3),
	);
	public function search($pageSize = 5)
	{
		/**************************************** 搜索 ****************************************/
		$where = array();
		if($brand_name = I('get.brand_name'))
			$where['brand_name'] = array('like', "%$brand_name%");
		/************************************* 翻页 ****************************************/
		$count = $this->alias('a')->where($where)->count();
		$page = new \Think\Page($count, $pageSize);
		// 配置翻页的样式
		$page->setConfig('prev', '上一页');
		$page->setConfig('next', '下一页');
		$data['page'] = $page->show();
		/************************************** 取数据 ******************************************/
		$data['data'] = $this->alias('a')->where($where)->group('a.brand_id')->limit($page->firstRow.','.$page->listRows)->select();
		return $data;
	}
	// 添加前
	protected function _before_insert(&$data, $option){
	    var_dump();
		if(isset($_FILES['logo']) && $_FILES['logo']['error'] == 0)
		{
			$ret = uploadOne('logo', 'brand/', array(
				array(150, 150, 2),
			));
			if($ret['img'] == 1)
			{
				$data['logo'] = $ret['images'][0];
				$data['sm_logo'] = $ret['images'][1];
			}
			else
			{
				$this->error = $ret['error'];
				return FALSE;
			}
		}
	}
	// 修改前
	protected function _before_update(&$data, $option)
	{
		if(isset($_FILES['logo']) && $_FILES['logo']['error'] == 0)
		{
			$ret = uploadOne('logo', 'brand/', array(
				array(150, 150, 2),
			));
			if($ret['img'] == 1)
			{
				$data['logo'] = C('IMG_URL').$ret['images'][0];
				$data['sm_logo'] = C('IMG_URL').$ret['images'][1];
			}
			else
			{
				$this->error = $ret['error'];
				return FALSE;
			}
			deleteImage(array(
				I('post.old_logo'),
				I('post.old_sm_logo'),

			));
		}
	}
	// 删除前
	protected function _before_delete($option)
	{
		if(is_array($option['where']['brand_id']))
		{
			$this->error = '不支持批量删除';
			return FALSE;
		}
		$images = $this->field('logo,sm_logo')->find($option['where']['brand_id']);
		deleteImage($images);
	}
	/************************************ 其他方法 ********************************************/
}