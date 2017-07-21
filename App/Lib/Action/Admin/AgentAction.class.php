<?php

/*
	代理商
*/

class AgentAction extends PublicAction{
				
	//列表
	public function index(){
		$db=M('agent_config');
		import("@.ORG.Page");
		$count =$db->count();
		$Page = new Page($count,10);	
		$show = $Page->show();
		$this->assign('show',$show);
		$list=$db->limit($Page->firstRow.','.$Page->listRows)->order('id desc')->select();   
		$this->assign('list',$list);
		$this->display();
	}		
			
    //添加
	public function add(){
		$db=M('agent_config');
		if($data=$this->_post()){
			$res=$db->data($data)->add();
			if($res){
			  $this->redirect('index');  
			}
		}
		$this->display();
	}
        //编辑
	public function edit(){
		$db=M('agent_config');
		$id=I('get.id');
		if($data=$this->_post()){
		   $db->where(array('id'=>$id))->save($data); 
		   $this->redirect('index');  
		}
		$info=$db->find($id);
		$this->assign('info',$info);
		$this->display();
	}
        //删除
	public function del(){
		$db=M('agent_config');
		$id=I('get.id');
		$res=$db->delete($id); 
		if($res){
			$this->redirect('index');  
		}
	}
	
	/*
		代理订单
	*/
	public function order_list(){
		$db=M('agent_order');
		import("@.ORG.Page");
		
		//支付状态
		$pay_status=I('get.pay_status');
		if($pay_status=='nopay'){
			$map['pay_status']=0;
		}elseif($pay_status=='pay'){
			$map['pay_status']=1;
		}
		
		$so_key=I('get.key');
		$so_val=I('get.val');
		
		$begin_time=strtotime(I('get.begin_time'));
		$end_time=strtotime(I('get.end_time'));
		
		if(in_array($so_key,array('id','uid','out_trade_no'))){
			if(!empty($so_key)&&!empty($so_val)){
				if(is_numeric($so_val)){
					$map[$so_key]=$so_val;
				}else{
					$map[$so_key]=array('like','%'.$so_val.'%');
				}
				
			}
		}
		
		if($id=I('get.id')){
			$map['id']=$id;
		}
		
		$count =$db->where($map)->count();
		$Page = new Page($count,20);	
		$show = $Page->show();
		$this->assign('show',$show);
		$list=$db->where($map)->limit($Page->firstRow.','.$Page->listRows)->order('id desc')->select();   
		foreach($list as $key=>$val){
			$list[$key]['user']=M('wechat_user')->where(array('id'=>$val['uid']))->find();
		}
		$this->assign('list',$list);
		$this->display();
	}
	
	/*
		订单删除
	*/
	public function order_del(){
		$db=M('agent_order');
		if($id=I('get.id')){
			$db->where(array('id'=>$id))->delete();
		}
		$this->redirect('order_list');
	}
	
	/*
		代理列表
	*/
	public function user_list(){
		import("@.ORG.Page");
		$db=M('wechat_user');
		
		$map['role_id']=array('egt',2);
		
		if($role_id=I('get.role_id')){
			$map['role_id']=$role_id;
		}
		$role=I('get.role');
		if($role=='agent'){
			$map['role_id']=array('egt',2);
		}
		
		$so_key=I('get.key');
		$so_val=I('get.val');
		
		$begin_time=strtotime(I('get.begin_time'));
		$end_time=strtotime(I('get.end_time'));
		
		if(in_array($so_key,array('id','nickname','mobile','username'))){
			if(!empty($so_val)&&!empty($so_val)){
				if($so_key=='id'&&is_numeric($so_val)){
					$map[$so_key]=$so_val;
				}else{
					$map[$so_key]=array('like','%'.$so_val.'%');
				}
				
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
			$where="p_1={$val['id']} or p_2={$val['id']} or p_3={$val['id']}";
			$list[$key]['son_count']=$db->where($where)->count();
			$list[$key]['order_total_fee']=M('order_info')->where(array('uid'=>$val['id'],'pay_status'=>1))->sum('total_fee');
		}
		$show = $Page->show();
		$this->assign('show',$show);
		$this->assign('list',$list);
		$this->display();
	}
	
	/*
		店铺管理
	*/
	public function shop_list(){
		import("@.ORG.Page");
		$db=M('shop');
		
		/*$map['role_id']=array('egt',2);
		
		if($role_id=I('get.role_id')){
			$map['role_id']=$role_id;
		}
		$role=I('get.role');
		if($role=='agent'){
			$map['role_id']=array('egt',2);
		}
	
		
		if($begin_time>0){
			$map['posttime']=array('egt',$begin_time);
		}
		
		if($end_time>0){
			$map['posttime']=array('elt',$end_time);
		}*/
		
		$map=array();
		
		$so_key=I('get.key');
		$so_val=I('get.val');
		
		$begin_time=strtotime(I('get.begin_time'));
		$end_time=strtotime(I('get.end_time'));
		
		if(in_array($so_key,array('id','shop_name','shop_tel'))){
			if(!empty($so_val)&&!empty($so_val)){
				if($so_key=='id'&&is_numeric($so_val)){
					$map[$so_key]=$so_val;
				}else{
					$map[$so_key]=array('like','%'.$so_val.'%');
				}
				
			}
		}	
		
		
		
		$count = $db->where($map)->count();
		$Page = new Page($count,10);

		$list=$db->where($map)->order('shop_status asc,id DESC')->limit($Page->firstRow.','.$Page->listRows)->select();
		foreach($list as $key=>$val){
			$list[$key]['goods_count1']=M('goods')->where(array('sid'=>$val['id'],'is_sale'=>1))->count();
			$list[$key]['goods_count2']=M('goods')->where(array('sid'=>$val['id'],'is_sale'=>2))->count();
			$list[$key]['user']=M('wechat_user')->where(array('id'=>$val['id']))->find();
		}

		$show = $Page->show();
		$this->assign('show',$show);
		$this->assign('list',$list);
		$this->display();
	}
	
	/*
		编辑店铺信息
	*/
	public function shop_edit(){
		$db=M('shop');
		$id=I('get.id');
		$shop2=M('goods');
		$shop=$db->where(array('id'=>$id))->find();
		$user=M('wechat_user')->where(array('id'=>$id))->find();
		if(empty($shop)){
			$this->error('店铺不存在');
		}
		$this->assign('shop',$shop);
		$this->assign('user',$user);
		if($arr=$this->_post()){
			if($_POST['shop_status']==1){
				$status['is_sale']=1;
				$arr2=$shop2->where(array('sid'=>$id))->save($status);
			}
			else if($_POST['shop_status']==0){
				$status['is_sale']=2;
				$arr2=$shop2->where(array('sid'=>$id))->save($status);
			}
			$db->where(array('id'=>$id))->save($arr);
			$this->success('保存成功');
		}else{
			$this->display();
		}
		
	}
	
	public function shop_status(){
		$db=M('shop');
		$id=I('get.id');
		$shop2=M('goods');
		$shop=$db->where(array('id'=>$id))->find();
		if($shop['shop_status']==1){
			$db->where(array('id'=>$id))->save(array('shop_status'=>0));
			$status['is_sale']=2;
			$arr2=$shop2->where(array('sid'=>$id))->save($status);
		}else{
			$db->where(array('id'=>$id))->save(array('shop_status'=>1));
			$status['is_sale']=1;
			$arr2=$shop2->where(array('sid'=>$id))->save($status);
		}
		$this->redirect('shop_list',array('p'=>I('get.p',1)));
	}
	
	
	
	/*
		代理设置
	*/
	public function agent_b_add(){
		
		
		$this->display();
	}
	
	/*
		代理列表
	*/
	public function agent_b_list(){
		$db=M('area_agent');
		import("@.ORG.Page");
		$map=array();
		$count =$db->where($map)->count();
		$Page = new Page($count,10);	
		$show = $Page->show();
		$this->assign('show',$show);
		$list=$db->where($map)->limit($Page->firstRow.','.$Page->listRows)->order('id desc')->select();   
		$this->assign('list',$list);
		$this->display();
	}
	
	/*
		删除代理
	*/
	public function agent_b_del(){
		$db=M('area_agent');
		$id=I('get.id');
		$db->delete($id);
		$this->redirect('agent_b_list');
	}
	
	 /*
	 	进入商家后台
	 */
	 public  function login_shop(){
		
		$db=M('wechat_user');
		$id=I('get.id');
		
		if(empty($id)){
			$this->error('参数错误');
		}else{
			$info=$db->find($id);
			
			if(empty($info)){
				$this->error('商家不存在');
			}else{
				session('service_id',$info['id']);
				session('service_name',$info['username']);
				$this->redirect('Service/Order/index');
			}
		}
	 }
	
}