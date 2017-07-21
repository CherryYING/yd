<?php 
/*
	邮件控制器
*/
class EmailAction extends PublicAction{
		/*public function _initialize(){
			parent::_initialize();
		}*/
		
		public function index(){
			$db=M('email_config');
			$info=$db->find(1);
			$this->assign('info',$info);
			if($arr=$this->_post()){
				$db->where(array('id'=>1))->save($arr);
				$this->redirect('index');
			}
			$this->display();    
		}
		
		
		
}