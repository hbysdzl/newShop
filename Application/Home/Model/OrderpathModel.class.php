<?php 

//收货地址

namespace Home\Model;
use Think\Model;

class OrderpathModel extends Model {
	

	//自动验证
	protected $_validate = array(
				array('true_name','require','收货人姓名不能为空','0','regex'),
				array('province','require','请选择省份','0','regex'),
				array('city','require','请选择城市','0','regex'),
				array('town','require','请选择县/区','0','regex'),
				array('address','require','地址不能为空','0','regex'),
				array('tel_phone','require','联系电话不能为空','0','regex'),
				array('tel_phone','number','手机号码格式有误','0','regex')
			);
	
}