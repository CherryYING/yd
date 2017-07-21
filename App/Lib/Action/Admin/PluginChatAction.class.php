<?php
/*
	对话管理
*/
class PluginChatAction extends PublicAction{


	public function _initialize(){
		parent::_initialize();
		import("@.ORG.Page");
	}
	public function index(){
		$db=M('plugin_chat');
		$map=array();
		
		$so_key=I('get.key');
		$so_val=I('get.val');
		
		$begin_time=strtotime(I('get.begin_time'));
		$end_time=strtotime(I('get.end_time'));
		
		if(in_array($so_key,array('id','nickname','mobile','username'))){
			if(!empty($so_val)&&!empty($so_val)){
				$map[$so_key]=array('like','%'.$so_val.'%');
			}
		}
		
		if($begin_time>0){
			$map['posttime']=array('egt',$begin_time);
		}
		
		if($end_time>0){
			$map['posttime']=array('elt',$end_time);
		}
		
		

		
		$count = $db->where($map)->count();
		$Page = new Page($count,10);

		$list=$db->where($map)->order('id DESC')->limit($Page->firstRow.','.$Page->listRows)->select();
		foreach($list as $key=>$val){
			$list[$key]['f_user']=M('wechat_user')->where(array('id'=>$val['f_uid']))->find();
			$list[$key]['t_user']=M('wechat_user')->where(array('id'=>$val['t_uid']))->find();
		}
		$show = $Page->show();
		$this->assign('show',$show);
		$this->assign('list',$list);
		$this->display();
	}

	/*
		删除
	*/
	public function del(){
		$db=M('plugin_chat');
		$id=I('get.id');
		$db=$db->where(array('id'=>$id))->delete();
		$this->redirect('index',array('p'=>I('get.p',1)));
	}
}