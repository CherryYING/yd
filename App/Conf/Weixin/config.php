<?php
return array(
	'TMPL_PARSE_STRING'=>array(
		'__PUBLIC__'=>__ROOT__.'/Public/Weixin',
	),
	'URL_HTML_SUFFIX'		=>	'.html',
	'SHOW_PAGE_TRACE'        => false,   // 显示页面Trace信息
	'TMPL_ACTION_ERROR' => TMPL_PATH . 'Weixin/Public/jump.html', 		//错误页面	
	'TMPL_ACTION_SUCCESS' => TMPL_PATH . 'Weixin/Public/jump.html', 	//成功页面	
);