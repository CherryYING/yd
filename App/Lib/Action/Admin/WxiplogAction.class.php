<?php 
class WxiplogAction extends PublicAction{
		public function index(){
			import("@.ORG.Page");
			$db = M('iplog');
			if($uid=I('get.uid')){
				$map=array('uid'=>$uid);
			}else{
				$map=array();
			}
			$count = $db->count($map);
			$Page = new Page($count,20);
			$plist =$db->where($map)->order('id DESC')->limit($Page->firstRow.','.$Page->listRows)->select();
			$show = $Page->show();
			$this->assign('show',$show);
			$this->assign('iplist',$plist);
			$this->display();  
		}
	
		public function ipdel(){
			M('iplog')->delete(I('get.id'));
			redirect(U('index'));
		}
		
}