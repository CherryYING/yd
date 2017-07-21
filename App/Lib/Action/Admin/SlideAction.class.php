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
			
			$cid=I('get.cid',1);
			
			$map=array('sid'=>0,'cid'=>$cid);
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
				$arr['posttime']=time();
				$rs=$db->add($arr);
				if($rs){
					$this->redirect('index',array('cid'=>$arr['cid']));
				}
			}
			$this->display();
		}
		//编辑
		public function edit(){
			$db=M('slide');
			$id=I('get.id');
			$info=$db->where(array('id'=>$id))->find();
			$this->assign('info',$info);
			if($arr=$this->_post()){
				$map=array('id'=>$id);
				$rs=$db->where($map)->data($arr)->save();
				$this->redirect('index',array('cid'=>$info['cid']));
			}
			$this->display();
		}
		//删除
		public function del(){
			M('slide')->delete(I('get.id'));
			$this->redirect('index');
		}
		
		/*
			 轮播图分类
		*/
		public function category(){
			import("@.ORG.Page");
			$db=M('slide_category');
			$map=array();
			$count = $db->where($map)->count();
			$Page = new Page($count,10);
			$list = $db->where($map)->order('id asc')->limit($Page->firstRow.','.$Page->listRows)->select();
			$show = $Page->show();
			$this->assign('show',$show);
			$this->assign('list',$list);
			$this->display();   
		}
		
		
}