<?php
return array(
	'TMPL_PARSE_STRING'=>array(
		//'__PUBLIC__'=>__ROOT__.'/'.APP_NAME.'/Tpl/Admin/Public',
		'__PUBLIC__'=>__ROOT__.'/Public/Static',
	),
	'URL_HTML_SUFFIX'		=>	'.html',
	'SHOW_PAGE_TRACE'        => false,   // 显示页面Trace信息
	/**********************************RBAC******************************************/
	'RBAC_SUPERADMIN'		=>	'admin',
	'ADMIN_AUTH_KEY'		=>	'superadmin',
	'USER_AUTH_ON'			=>	true,
	'USER_AUTH_TYPE'		=>	1,
	'USER_AUTH_KEY'			=>	'uid',
	'NOT_AUTH_MODULE'		=>	'Index',
	'NOT_AUTH_ACTION'		=>	'addUserHandle,addRoleHandle,addNodeHandle,setAccess,artaddhandle,sortaddhandle',
	'RBAC_ROLE_TABLE'		=>	'tp_role',
	'RBAC_USER_TABLE'		=>	'tp_role_user',
	'RBAC_NODE_TABLE'		=>	'tp_node',
	'RBAC_ACCESS_TABLE'		=>	'tp_access'
);