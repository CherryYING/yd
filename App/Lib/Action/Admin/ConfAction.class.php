<?php
//系统设置
class ConfAction extends PublicAction{
	public function index(){
		$con=M('cms_config')->find(1);
		$this->assign('conf',$con);
		if($arr=$this->_post()){
			M('cms_config')->where('id=1')->save($arr);
			$this->success('设置成功！',U('index'));
		}else{
			$this->display();
		}
		
	}
	
	public function editSlide(){
		$conf=M('cms_config')->field('web_url')->find(1);
                if(!empty($conf['web_url'])){
                   $silde=json_decode($conf['web_url'],true);
                    foreach($silde as $key=>$val){
                        $silde[$key]['pic']=$this->get_thumb($val['pic']);
                    }  
                }
		$this->assign('silde',$silde);
		$this->display();
	}
	
	public function slideHandle(){
		$db=M('cms_config');
		if($_POST){
			if($this->_post('picurl')){
				$num=count($this->_post('picurl'));
				for($i=0;$i<$num;$i++){
					$arr[$i]['pic']=$_POST['picurl'][$i];
					$arr[$i]['title']=$_POST['title'][$i];
					$arr[$i]['url']=$_POST['url'][$i];
				}
				$data['web_url']=json_encode($arr);
			}
			$res=$db->where(array('id'=>1))->save($data);
		}else{
			$data['web_url']='';
			$res=$db->where(array('id'=>1))->save($data);
		}
		$this->success('操作成功！');
	}
	
	/*
		邮件服务器设置
	*/
	public function email(){
		$db=M('email_config');
		$info=$db->find(1);
		$this->assign('info',$info);
		if($arr=$this->_post()){
			$db->where(array('id'=>1))->save($arr);
			$this->success('设置成功！',U('email'));
		}else{
			$this->display();
		}
		
	}
	/*
		短信网关地址
	*/
	public function msg(){
		$db=M('message_config');
		$info=$db->find(1);
		$this->assign('info',$info);
		if($arr=$this->_post()){
			$db->where(array('id'=>1))->save($arr);
			$this->success('设置成功！',U('msg'));
		}else{
			$this->display();
		}
		
	}
	/*
		 分销佣金分配设置
	*/
	public function resale(){
		$db=M('resale_config');	
		$info=$db->find(1);
		$this->assign('info',$info);
		if($arr=$this->_post()){
			$db->where(array('id'=>1))->save($arr);
			$this->success('设置成功！',U('resale'));
		}else{
			$this->display();
		}
	}
	/*
		分销商升级规则
	*/
	public function resaler(){
		$db=M('resale_config');	
		$info=$db->find(1);
		$this->assign('info',$info);
		if($arr=$this->_post()){
			$db->where(array('id'=>1))->save($arr);
			$this->redirect('resaler');
		}else{
			$this->display();
		}
	}
	/*
		积分规则配置
	*/
	public function jifen(){
		$db=M('jifen_config');	
		$info=$db->find(1);
		$this->assign('info',$info);
		if($arr=$this->_post()){
			$db->where(array('id'=>1))->save($arr);
			$this->success('设置成功！',U('jifen'));
		}else{
			$this->display();
		}
	}
	
	/*
		提现手续费
	*/
	public function take_money(){
		$db=M('resale_config');
		$info=$db->find(1);
		$this->assign('info',$info);
		if($arr=$this->_post()){
			$db->where('id=1')->save($arr);
			$this->success('设置成功！',U('take_money'));
		}else{
			$this->display();
		}
		
	}
	
}