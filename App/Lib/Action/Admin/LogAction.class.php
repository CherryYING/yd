<?php 
/*
	后台操作日志
*/
class LogAction extends PublicAction{
		public function _initialize(){
			parent::_initialize();
		}
		//操作日志列表
		public function index(){
			import("@.ORG.Page");
			$db=M('admin_action_log');
			$count = $db->count();
			$Page = new Page($count,20);
			$list = $db->order('id desc')->limit($Page->firstRow.','.$Page->listRows)->select();
			$show = $Page->show();
			$this->assign('show',$show);
			$this->assign('list',$list);
			$this->display();   
		}
		
		
		//删除
		public function del(){
			M('admin_action_log')->delete(I('get.id'));
			$this->redirect('index');
		}
		
}