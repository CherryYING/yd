<?php
/*
	内容管理-【PC商城】
*/
class CmsAction extends BaseAction{
	 
	public function _initialize(){
		parent::_initialize();
	}
	
	/*
		文章内页
	*/
	public function read(){
		$db=M("cms_article");
		$sort=M("cms_sort");
		$id=I('get.id');
		$info=$db->where(array('id'=>$id))->find();
		if(empty($info)){
			$this->error('访问内容不存在！');
		}
		$this->assign('info',$info);
		$this->display();
	}
	
	/*
		文章列表
	*/
	public function lists(){
		$db=M('cms_article');
		$fid=I('get.fid');
		$map=array('fid'=>$fid);
		$info=M('cms_sort')->find($fid);
		$this->assign('info',$info);
		$list=$db->where($map)->order('id')->select();
		$this->assign('list',$list);
		$this->display();
	}	
	
}