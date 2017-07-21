<?php
//红包参数设置
class RedpackAction extends PublicAction{
	public function index(){
		$conf=M('redpack_config')->find(1);
		$this->assign('conf',$conf);
		$this->display();
	}
	
	public function confHandle(){
		$data=$_POST;
		if(M('redpack_config')->where('id=1')->save($data)){
			$this->success('设置成功！');
		}else{
			$this->error('设置失败！');
		}
	}
}