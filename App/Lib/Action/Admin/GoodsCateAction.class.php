<?php 
class GoodsCateAction extends PublicAction{
		public $catelist;
		public function _initialize(){
			parent::_initialize();
			$this->catelist=M('goods_category')->select();
			$this->assign('catelist',$this->catelist);
		}
		//分类列表
		public function index(){
			header("content-type:text/html;charset=utf-8");
			import("@.ORG.Page");
			$db = M('goods_category');
			/*$count = $db->count();
			$Page = new Page($count,10);
			$list = $db->order('id DESC')->limit($Page->firstRow.','.$Page->listRows)->select();
			$show = $Page->show();*/
			$_list=$db->order('list asc')->select();
			$list=order($_list);
			$this->assign('show',$show);
			$this->assign('list',$list);
			$this->display(); 
		}
		//添加分类
		public function add(){
			$db=M('goods_category');
			if($this->isPost()){
				$arr=$this->_post();
				$arr['posttime']=time();
				$rs=$db->data($arr)->add();
				if($rs){
					$this->redirect('index');
				}
			}
			$this->display();
		}
		//编辑分类
		public function edit(){
			$db=M('goods_category');
			$id=I('get.id');
			$brand_info=$db->find($id);
			$this->assign('info',$brand_info);
			if($this->isPost()){
				$map=array('id'=>$id);
				$arr=$this->_post();
				$rs=$db->where($map)->data($arr)->save();
				if($rs){
					$this->redirect('index',array('id'=>$id));
				}
			}
			$this->display();
		}
		//删除分类
		public function del(){
			M('goods_category')->delete(I('get.id'));
			$this->redirect('index');
		}
		
		public function status(){
			$id=I('get.id');
			$info=M('goods_category')->where(array('id'=>$id))->find();
			if($info['is_tui']==1){
				M('goods_category')->where(array('id'=>$id))->save(array('is_tui'=>0));
			}else{
				M('goods_category')->where(array('id'=>$id))->save(array('is_tui'=>1));
			}
			$this->redirect('index');
		}
		
}