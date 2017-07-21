<?php
class FriendLinkAction extends PublicAction{
			
    //添加友情链接
	public function add(){
		$db=M('cms_friendlink');
		if($data=$this->_post()){
			$res=$db->data($data)->add();
			if($res){
			  $this->redirect('index');  
			}
		}
		$this->display();
	}
        //编辑友情链接
	public function edit(){
		$db=M('cms_friendlink');
		$id=I('get.id');
		if($data=$this->_post()){
		   $db->where(array('id'=>$id))->save($data); 
		   $this->redirect('index');  
		}
		$info=$db->find($id);
		$this->assign('info',$info);
		$this->display();
	}
        //删除友情链接
	public function del(){
		$db=M('cms_friendlink');
		$id=I('get.id');
		$res=$db->delete($id); 
		if($res){
			$this->redirect('index');  
		}
	}
	//友情链接列表
	public function index(){
		$db=M('cms_friendlink');
		import("@.ORG.Page");
		$count =$db->count();
		$Page = new Page($count,10);	
		$show = $Page->show();
		$this->assign('show',$show);
		$list=$db->order('linklist asc')->limit($Page->firstRow.','.$Page->listRows)->select();   
		$this->assign('list',$list);
		$this->display();
	}
}