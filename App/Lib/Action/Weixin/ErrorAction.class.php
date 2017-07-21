
<?php
/*
	异常控制器-【微信商城】
*/
class ErrorAction extends Action{

	public function _initialize(){
		//parent::_initialize();
		if(!is_weixin()) {
		   //exit('此功能只能在微信浏览器中使用');
		}
		
		//系统配置信息
		$webinfo=M('cms_config')->find(1);
        $this->assign('webinfo',$webinfo);
		
	}
	
	public function err_sys(){
		$this->display();
	}
	
	/*
		账号禁用提示
	*/
	public function err_user(){
		$this->display();
	}
	
}