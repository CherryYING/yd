<?php
/*
	 平台状态管理
*/
class SysAction extends PublicAction{
	
	
	public function index(){
		$conf=M('sys_status')->find(1);
		$this->assign('conf',$conf);
		if($arr=$this->_post()){
			M('cms_config')->where('id=1')->save($arr);
			$this->success('设置成功！',U('index'));
		}else{
			$this->display();
		}
		
	}
	
	public function handle(){
		$db=M('sys_status');
		$conf=M('sys_status')->find(1);
		$mod=I('get.mod');
		switch($mod){
			case 'web_status':
				if($conf['web_status']==1){
					$db->where('id=1')->save(array('web_status'=>2));
				}else{
					$db->where('id=1')->save(array('web_status'=>1));
				}
			break;
		}
		$this->success('操作成功');
		
	}
	
	
}