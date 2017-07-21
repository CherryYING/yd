<?php
/*
	插件控制器
*/
class PluginAction extends BaseAction{
	public function _initialize(){
		parent::_initialize();
		import('@.ORG.Page');
		import("@.ORG.Http");
        import('@.ORG.Image.ThinkImage');
		
		if(!is_weixin()) {
		    //exit('此功能只能在微信浏览器中使用');
		}else{
			//to do
		}
		
		$jump=get_curr_url();
		$jump=base64_encode($jump);
		if(!$this->user_id){
			//无登录信息，跳转到登录页
			$this->redirect('Member/login',array('jump'=>$jump));
		}
		
	}
	
	
	/*
		 消息中心
	*/
	public  function chat_index(){
		$this->display();
	}
	
	/*
		小伙伴【下级用户】
	*/
	public function chat_user_index(){
		$db=M('wechat_user');
		$info=$db->where(array('id'=>$this->user_id))->find();
		
		$parent=$db->where(array('id'=>array('in',array($info['p_1'],$info['p_2'],$info['p_3']))))->select();
		foreach($parent as $key=>$val){
			if($val['id']==$info['p_1']){
				$parent[$key]['relation']='上一级';
			}
			if($val['id']==$info['p_2']){
				$parent[$key]['relation']='上二级';
			}
			if($val['id']==$info['p_3']){
				$parent[$key]['relation']='上三级';
			}
		}
		
		$this->assign('parent',$parent);
		
		
		//son_1
		$son_1_count = $db->where(array('p_1'=>$this->user_id))->count();
		//son_1
		$son_2_count = $db->where(array('p_2'=>$this->user_id))->count();
		//son_1
		$son_3_count = $db->where(array('p_3'=>$this->user_id))->count();
		$this->assign('son_1_count',$son_1_count);
		$this->assign('son_2_count',$son_2_count);
		$this->assign('son_3_count',$son_3_count);
		$this->display();
	}
	
	/*
		小伙伴【下级用户】
	*/
	public function chat_user_list(){
		$type=I('get.type',1);					//下一级、下二级、下三级
		$db=M('wechat_user');
		$info=$db->where(array('id'=>$this->user_id))->find();
		
		$parent=$db->where(array('id'=>array('in',array($info['p_1'],$info['p_2'],$info['p_3']))))->select();
		foreach($parent as $key=>$val){
			if($val['id']==$info['p_1']){
				$parent[$key]['relation']='上一级';
			}
			if($val['id']==$info['p_2']){
				$parent[$key]['relation']='上二级';
			}
			if($val['id']==$info['p_3']){
				$parent[$key]['relation']='上三级';
			}
		}
		
		$this->assign('parent',$parent);
		
		//$map="p_1=$this->user_id or p_2=$this->user_id or p_3=$this->user_id";
		
		switch($type){
			case '1':
				$map['p_1']=$this->user_id;
			break;
			case '2':
				$map['p_2']=$this->user_id;
			break;
			case '3':
				$map['p_3']=$this->user_id;
			break;
			
		}
		
		$count = $db->where($map)->count();
		$Page = new Page($count,20);
		$Page->setConfig('prev', '上一页');
		$Page->setConfig('next', '下一页');
		$Page->setConfig('theme',"%upPage% %downPage%");
		$page = $Page->show();
		$this->assign('page',$page);
		
		$son_list=$db->where($map)->order('id asc')->limit($Page->firstRow.','.$Page->listRows)->select();
		foreach($son_list as $key=>$val){
			if($val['p_1']==$this->user_id){
				$son_list[$key]['relation']='下一级';
			}elseif($val['p_2']==$this->user_id){
				$son_list[$key]['relation']='下二级';
			}elseif($val['p_3']==$this->user_id){
				$son_list[$key]['relation']='下三级';
			}
		}
		$this->assign('son_list',$son_list);
		$this->display();
	}
	
	
	
	/*
		发送消息form
	*/
	public function chat_room(){
		$db=M('plugin_chat');
		$t_uid=I('get.id');			//消息接收者
		$t_user=M('wechat_user')->where(array('id'=>$t_uid))->find();
		if(empty($t_user)){
			$this->error('参数错误！');
		}
		$this->assign('t_user',$t_user);
		//最近1次对方发给我的消息
		$last_msg=$db->where(array('f_uid'=>$t_uid,'t_uid'=>$this->user_id))->order('id desc')->limit(1)->select();
		foreach($last_msg as $key=>$val){
			$last_msg[$key]['f_user']=M('wechat_user')->find($t_uid);
		}
		$this->assign('last_msg',$last_msg);
		$this->display();
		
	}
	
	/*
		聊天记录
	*/
	public function chat_list(){
		$db=M('plugin_chat');			
		$type=I('get.type',1);							//1我的接收;2我的发送【默认为我接收到的消息】
		if($type==1){
			$map=array('t_uid'=>$this->user_id);			//接收者为当前用户
			$tpl="chat_list";
		}elseif($type==2){
			$map=array('f_uid'=>$this->user_id);			//发送者为当前用户
			$tpl="chat_list2";
		}
		
		
		$count = $db->where($map)->count();
		$Page = new Page($count,20);
		$Page->setConfig('prev', '上一页');
		$Page->setConfig('next', '下一页');
		$Page->setConfig('theme',"%upPage% %downPage%");
		$page = $Page->show();
		$this->assign('page',$page);
		
		$list=$db->where($map)->order('id desc')->limit($Page->firstRow.','.$Page->listRows)->select();
		foreach($list as $key=>$val){
			$list[$key]['f_user']=M('wechat_user')->field('id,nickname,headimgurl')->where(array('id'=>$val['f_uid']))->find();
			$list[$key]['t_user']=M('wechat_user')->field('id,nickname,headimgurl')->where(array('id'=>$val['t_uid']))->find();
		}
		$this->assign('list',$list);
		$this->display($tpl);
	}
	
} 

?>