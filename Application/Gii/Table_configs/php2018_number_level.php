<?php
return array(
	'tableName' => 'php2018_number_level',    // 表名
	'tableCnName' => '会员',  // 表的中文名
	'moduleName' => 'Admin',  // 代码生成到的模块
	'digui' => 0,             // 是否无限级（递归）
	'diguiName' => '',        // 递归时用来显示的字段的名字，如cat_name（分类名称）
	'pk' => 'level_id',    // 表中主键字段名称
	/********************* 要生成的模型文件中的代码 ******************************/
	'insertFields' => "array('level_name','bottom_num','top_num','rate')",
	'updateFields' => "array('level_id','level_name','bottom_num','top_num','rate')",
	'validate' => "
		array('level_name', '1,30', '的值最长不能超过 30 个字符！', 2, 'length', 3),
		array('bottom_num', 'number', '必须是一个整数！', 2, 'regex', 3),
		array('top_num', 'number', '必须是一个整数！', 2, 'regex', 3),
		array('rate', 'number', '必须是一个整数！', 2, 'regex', 3),
	",
	/********************** 表中每个字段信息的配置 ****************************/
	'fields' => array(
		'level_name' => array(
			'text' => '会员名称',
			'type' => 'text',
			'default' => '',
		),
		'bottom_num' => array(
			'text' => '积分下限',
			'type' => 'text',
			'default' => '0',
		),
		'top_num' => array(
			'text' => '积分上限',
			'type' => 'text',
			'default' => '0',
		),
		'rate' => array(
			'text' => '折扣率',
			'type' => 'text',
			'default' => '100',
		),
	),

);