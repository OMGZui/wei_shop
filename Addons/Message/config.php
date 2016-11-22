<?php
return array(
/*	'random'=>array(//配置在表单中的键名 ,这个会是config[random]
		'title'=>'是否开启随机:',//表单的文字
		'type'=>'radio',		 //表单的类型：text、textarea、checkbox、radio、select等
		'options'=>array(		 //select 和radion、checkbox的子选项
			'1'=>'开启',		 //值=>文字
			'0'=>'关闭',
		),
		'value'=>'1',			 //表单的默认值
	),*/
	'title'=>array(
		'title'=>'标题',
		'type'=>'text',
		'value'=>'留言板',
		'tip'=>'微信中回复的图文消息标题'
	),
	'cover'=>array(
		'title'=>'封面',
		'type'=>'picture',
		'value'=>'',
		'tip'=>'微信中回复的图文消息封面图片'
	),
	'desc'=>array(
		'title'=>'描述',
		'type'=>'textarea',
		'value'=>'请在这里给我留言',
		'tip'=>'微信中回复的图文消息描述'
	),
);
					