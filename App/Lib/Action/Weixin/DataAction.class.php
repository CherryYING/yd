<?php

/*
	数据检测
*/

class DataAction extends Action{
	
	public function _initialize(){
		import('@.ORG.Page');
	}
	/*
		代理购买列表
	*/
	public function index(){
		$db=M('agent_order');
		$count = $db->where($map)->count();
		$Page = new Page($count,20);
		$Page->setConfig('prev', '上一页');
		$Page->setConfig('next', '下一页');
		$Page->setConfig('theme',"%upPage% %downPage%");
		$page = $Page->show();
		$this->assign('page',$page);
		
		$list=$db->where($map)->order('id desc')->limit($Page->firstRow.','.$Page->listRows)->select();
		
		foreach($list as $key=>$val){
			$list[$key]['user']=M('wechat_user')->where(array('id'=>$val['uid']))->find();
		}
		
		$this->assign('list',$list);
		
		$this->display();
	}
	
	
	/*
		商品订单列表
	*/
	public function order(){
		$db=M('order_info');
		$count = $db->where($map)->count();
		$Page = new Page($count,20);
		$Page->setConfig('prev', '上一页');
		$Page->setConfig('next', '下一页');
		$Page->setConfig('theme',"%upPage% %downPage%");
		$page = $Page->show();
		$this->assign('page',$page);
		
		$list=$db->where($map)->order('id desc')->limit($Page->firstRow.','.$Page->listRows)->select();
		
		foreach($list as $key=>$val){
			$list[$key]['user']=M('wechat_user')->where(array('id'=>$val['uid']))->find();
		}
		
		$this->assign('list',$list);
		
		$this->display();
	}
	
	

	
}