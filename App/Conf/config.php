<?php
/*
	Admin【总后台】
	Resaler【分销商】
	Service【服务商】
	Weixin【微信商城】
*/
return array(
	'APP_GROUP_LIST'	=>'Admin,Index,Weixin,Resaler,Service',//设置分组
	'DEFAULT_GROUP'		=>'Index',//设置默认组
	'APP_GROUP_MODEL'	=>1,		//开启独立分组
	'TAGLIB_LOAD'		=>true,
	//'APP_AUTOLOAD_PATH'	=>'@.TagLib',
	//'TAGLIB_BUILD_IN'	=>'Cx,Tree',
	//'APP_GROUP_PATH'	=>'Admin,Index',//独立分组文件夹名称
	'URL_MODEL' 			=>	0,
	'TMPL_ACTION_ERROR' => TMPL_PATH . 'Public/error.html', 	//错误页面	
    'TMPL_ACTION_SUCCESS' => TMPL_PATH . 'Public/success.html', //成功页面
	//===========================================================//
	'DB_TYPE'               => 'mysql',			// 数据库类型
    'DB_HOST'               => 'localhost',		// 服务器地址
    'DB_NAME'               => 'yd_wdkl',       // 数据库名
    'DB_USER'               => 'root',			// 用户名
    'DB_PWD'                => 'root',			// 密码
    'DB_PORT'               => '3306',			// 端口
    'DB_PREFIX'             => 'tp_',			// 数据库表前缀
    'DB_CHARSET'            => 'utf8',			// 数据库编码默认采用utf8
	'SHOW_PAGE_TRACE'		=>	true,			// 开启页面Trace
	'TMPL_L_DELIM'			=>	'{tp#',			//设置左定界符
	'TMPL_R_DELIM'			=>	'}',			//设置右定界符
	'SHOW_ERROR_MSG'        => 	false,			// 显示错误信息
	'URL_HTML_SUFFIX'		=>	'.html',		//设置伪静态后缀名
	
	'TOKEN_ON'=>true,  // 是否开启令牌验证
    'TOKEN_NAME'=>'__hash__',    // 令牌验证的表单隐藏字段名称
    'TOKEN_TYPE'=>'md5',  //令牌哈希验证规则 默认为MD5
    'TOKEN_RESET'=>true,  //令牌验证出错后是否重置令牌 默认为true
	
	//============================微信配置==============================//
	'WECHAT_APPID'=>'',
	'WECHAT_APPSECRET'=>'',
	'WECHAT_MCHID'=>'',			//商户id
	'WECHAT_PARTNERKEY'=>'',	//支付秘钥	
	
	//=============================================================//	
);