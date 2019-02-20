create table php2018_orderpath (
	id int unsigned not null primary key auto_increment,
	member_id int unsigned not null comment '会员id',
    true_name varchar(10) not null comment '收货人',
	province varchar(10)  not null comment '省份',
	city varchar(10) not null comment '城市',
	town varchar(10) not null comment '县区',
	address varchar(50) not null comment '详细地址',
	tel_phone varchar(15) not null comment '联系电话',
	addtime varchar(15) comment '添加时间',
	status enum('1','0') default '1' comment '状态' 
)engine=innodb charset=utf8;


 -- 新增会员表字段

 alter table php2018_member add sex enum('保密','男','女') default '保密' not null after nikname;

 alter table php2018_member add birthday varchar(15) comment '生日' after sex;

 alter table php2018_member modify sex enum('保密','男','女') default '保密' not null after nikname;



 alter table php2018_comment modify id int unsigned primary key auto_increment;

 alter table php2018_comment modify goods_id int unsigned after id;

 alter table php2018_comment modify member_id int unsigned after goods_id;

 alter table php2018_comment change content goods_evaluate text;

 alter table php2018_comment add serve_evaluate text after goods_evaluate;

 alter table php2018_comment add status enum('-1','0','1') default '1';


 -- 评论晒图表

 create table php2018_comment_pic(
 	id int unsigned primary key auto_increment,
 	img varchar(100) comment '图片',
 	status enum('0','-1','1') default '1' comment '状态'
 )engine=innodb default charset=utf8;


 alter table php2018_comment_pic add comment_id int unsigned not null after img;


 alter table php2018_comment_pic add goods_id int unsigned not null after comment_id;