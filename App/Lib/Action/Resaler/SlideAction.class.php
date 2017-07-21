<?php 
/*
	轮播图片管理
*/
class	SlideAction extends PublicAction{
		public function _initialize(){
			parent::_initialize();
		}
		//轮播图列表
		public function index(){
			import("@.ORG.Page");
			$db=M('slide');
			$count = $db->count();
			$Page = new Page($count,10);
			$list = $db->order('id asc')->limit($Page->firstRow.','.$Page->listRows)->select();
			$show = $Page->show();
			$this->assign('show',$show);
			$this->assign('list',$list);
			$this->display();   
		}
		
		//添加
		public function add(){
			$db=M('slide');
			if($this->isPost()){
				$arr=$this->_post();
				$arr['cid']=1;
				$arr['posttime']=time();
				$rs=$db->data($arr)->add();
				if($rs){
					$this->redirect('index');
				}
			}
			$this->display();
		}
		//编辑
		public function edit(){
			$db=M('slide');
			$id=I('get.id');
			$info=$db->find($id);
			$this->assign('info',$info);
			if($this->isPost()){
				$map=array('id'=>$id);
				$arr=$this->_post();
				$arr['cid']=1;
				$rs=$db->where($map)->data($arr)->save();
				if($rs){
					$this->redirect('index',array('id'=>$id));
				}
			}
			$this->display();
		}
		//删除
		public function del(){
			M('slide')->delete(I('get.id'));
			$this->redirect('index');
		}
		
}