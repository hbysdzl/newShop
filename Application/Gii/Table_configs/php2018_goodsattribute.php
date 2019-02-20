<?php
return array(
	'tableName' => 'php2018_goodsattribute',    // 表名
	'tableCnName' => '商品属性',  // 表的中文名
	'moduleName' => 'Admin',  // 代码生成到的模块
	'digui' => 0,             // 是否无限级（递归）
	'diguiName' => '',        // 递归时用来显示的字段的名字，如cat_name（分类名称）
	'pk' => 'attr_id',    // 表中主键字段名称
	/********************* 要生成的模型文件中的代码 ******************************/
	'insertFields' => "array('attr_name','attr_type','attr_option_values','type_id')",
	'updateFields' => "array('attr_id','attr_name','attr_type','attr_option_values','type_id')",
	'validate' => "
		array('attr_name', 'require', '不能为空！', 1, 'regex', 3),
		array('attr_name', '1,30', '的值最长不能超过 30 个字符！', 1, 'length', 3),
		array('attr_type', 'number', '必须是一个整数！', 2, 'regex', 3),
		array('attr_option_values', '1,150', '的值最长不能超过 150 个字符！', 2, 'length', 3),
		array('type_id', 'require', '不能为空！', 1, 'regex', 3),
		array('type_id', 'number', '必须是一个整数！', 1, 'regex', 3),
	",
	/********************** 表中每个字段信息的配置 ****************************/
	'fields' => array(
		'attr_name' => array(
			'text' => '属性名称',
			'type' => 'text',
			'default' => '',
		),
		'attr_type' => array(
			'text' => '属性类别',
			'type' => 'text',
			'default' => '0',
		),
		'attr_option_values' => array(
			'text' => '可选属性值',
			'type' => 'text',
			'default' => '',
		),
		'type_id' => array(
			'text' => '所属商品类型',
			'type' => 'text',
			'default' => '',
		),
	),
	/**************** 搜索字段的配置 **********************/
	'search' => array(
		array('attr_name', 'normal', '', 'like', ''),
		array('attr_type', 'normal', '', 'eq', ''),
	),
);