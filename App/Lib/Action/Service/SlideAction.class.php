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
			$map=array('sid'=>$this->service_id);
			$count = $db->where($map)->count();
			$Page = new Page($count,10);
			$list = $db->where($map)->order('id asc')->limit($Page->firstRow.','.$Page->listRows)->select();
			$show = $Page->show();
			$this->assign('show',$show);
			$this->assign('list',$list);
			$this->display();   
		}
		
		//添加
		public function add(){
			$db=M('slide');
			if($arr=$this->_post()){
				$arr['cid']=$arr['sid']=$this->service_id;
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
			if($arr=$this->_post()){
				$map=array('id'=>$id);
				$rs=$db->where($map)->data($arr)->save();
				$this->redirect('index',array('id'=>$id));
			}
			$this->display();
		}
		//删除
		public function del(){
			$id=I('get.id');
			M('slide')->delete($id);
			$this->redirect('index');
		}
		
}