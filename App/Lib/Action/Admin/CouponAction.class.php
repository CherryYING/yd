<?php
/*
	代金券管理
*/
class CouponAction extends PublicAction{
	
	/*
		用户代金券管理
	*/
	public function index(){
		$db=M('coupon');
		import("@.ORG.Page");
		$map='';
		$count = $db->where($map)->count();
		$Page = new Page($count,20);
		$show = $Page->show();
		$this->assign('show',$show);
		$list = $db->where($map)->order('id desc')->limit($Page->firstRow.','.$Page->listRows)->select();
		foreach($list as $key=>$val){
			$list[$key]['user']=M('wechat_user')->where(array('id'=>$val['uid']))->find();	
		}
		$this->assign('list',$list);
		$this->display();
	}
	
	
	/*
		 发送代金券
	*/
	public function add(){
		$db=M('coupon');
		if($arr=$this->_post()){
			$user=M('wechat_user')->where(array('id'=>$arr['uid']))->find();
			if(empty($user)){
				$this->error('用户不存在，请核实用户信息！');
			}else{
				$arr['cid']=2;			//后台赠送
				$arr['posttime']=time();
				if(!empty($arr['over_time'])){
					$arr['over_time']=strtotime($arr['over_time']);
				}
				$coupon_id=$db->add($arr);
				//发送模板消息
				coupon_notice($coupon_id);
				$this->redirect('index');
			}
			
		}else{
			$this->display();
		}
	}
	
	
	/*
		删除代金券
	*/
	public function del(){
		$db=M('coupon');
		$id=I('get.id');
		$db->delete($id);
		$this->redirect('index',array('p'=>I('get.p',1)));
	}

	/*
		修改状态
	*/
	public function state(){
		$db=M('coupon');
		$id=I('get.id');
		$info=$db->where("id=$id")->find();
		if($info['status']==1){
			$data['status']=0;					
		}else{
			$data['status']=1;
			$data['cost_time']=time();			//使用时间
		}
		$db->where(array('id'=>$id))->save($data);
		$this->redirect('index',array('p'=>I('get.p',1)));
	}

}


?>