<?php 
/*
	资金相关操作
*/
class MoneyAction extends PublicAction{
		public function _initialize(){
			parent::_initialize();
		}
		
		public function index(){
			import("@.ORG.Page");
			$db=M('money_water');
			//$map=array('way'=>array('in','yongjin,yongjin_refund'));		//'type'=>1,
			
			if($uid=I('get.uid')){
				$map['uid']=$uid;
			}
			
			if($money_type=I('get.money_type')){
				switch($money_type){
					case 'a':
						$map['money_type']='a';
					break;
					case 'b':
						$map['money_type']='b';
					break;
					case 'p':
						$map['money_type']='p';
					break;
				}
				
			}
			
			$count = $db->where($map)->count();
			$Page = new Page($count,20);
			$list = $db->where($map)->order('id desc')->limit($Page->firstRow.','.$Page->listRows)->select();
			foreach($list as $key=>$val){
				$list[$key]['user']=M('wechat_user')->where(array('id'=>$val['uid']))->field('id,nickname,name,username')->find();
				if($val['money_type']=='a'){
					$list[$key]['order']=M('agent_order')->where(array('id'=>$val['order_id']))->field('id,out_trade_no')->find();
				}else{
					$list[$key]['order']=M('order_info')->where(array('id'=>$val['order_id']))->field('id,out_trade_no')->find();
				}
				
				/*switch($val['way']){
					case 'yongjin':
						$list[$key]['way_name']='订单返佣';
					break;
					
					case 'yongjin_refund':
						$list[$key]['way_name']='佣金撤回';
					break;
					
					case 'admin_change':
						$list[$key]['way_name']='管理员操作';
					break;
					
					case 'take_money':
						$list[$key]['way_name']='用户提现';
					break;
				}*/
			}
			$show = $Page->show();
			$this->assign('show',$show);
			$this->assign('list',$list);
			$this->display();  
		}
		/*
			 红包提现记录
		*/
		public function wechat_hb_list(){
			import("@.ORG.Page");
			$db=M('wechat_hb_list');
			$map=array();
			
			$so_key=I('get.key');
			$so_val=I('get.val');
			
			if(in_array($so_key,array('uid','mch_billno'))){
				if(!empty($so_key)&&!empty($so_val)){
					$map[$so_key]=$so_val;
					
				}
			}
			
			$count = $db->where($map)->count();
			$Page = new Page($count,20);
			$list = $db->where($map)->order('id desc')->limit($Page->firstRow.','.$Page->listRows)->select();
			foreach($list as $key=>$val){
				$list[$key]['user']=M('wechat_user')->where(array('id'=>$val['uid']))->field('id,nickname,name,username')->find();
			}
			$show = $Page->show();
			$this->assign('show',$show);
			$this->assign('list',$list);
			//累计发送红包金额
			$pay_total_money=$db->where(array('status'=>1))->sum('total_amount');
			$this->assign('pay_total_money',$pay_total_money);
			
			$this->display();   
		}
		
		/*
			云金币
		*/
		public function money_cloud_list(){
			import("@.ORG.Page");
			$db=M('money_cloud_water');
			$map=array();
			
			
			if($uid=I('get.uid')){
				$map['uid']=$uid;
			}
		
			
			$count = $db->where($map)->count();
			$Page = new Page($count,20);
			$list = $db->where($map)->order('id desc')->limit($Page->firstRow.','.$Page->listRows)->select();
			foreach($list as $key=>$val){
				$list[$key]['user']=M('wechat_user')->where(array('id'=>$val['uid']))->field('id,nickname,name,username')->find();
				$list[$key]['order']=M('agent_order')->where(array('id'=>$val['order_id']))->field('id,out_trade_no')->find();
			}
			$show = $Page->show();
			$this->assign('show',$show);
			$this->assign('list',$list);
			$this->display();   
		}
		
		/*
			销售业绩流水
		*/
		public function sell_money_list(){
			import("@.ORG.Page");
			$db=M('sell_money_water');
			
			if($money_type=I('get.money_type')){	//A、B网
				$map['money_type']=$money_type;
			}
			
			if($uid=I('get.uid')){	
				$map['uid']=$uid;
			}
						
			$count = $db->where($map)->count();
			$Page = new Page($count,20);
			$list = $db->where($map)->order('id desc')->limit($Page->firstRow.','.$Page->listRows)->select();
			foreach($list as $key=>$val){
				$list[$key]['user']=M('wechat_user')->where(array('id'=>$val['uid']))->field('id,nickname,name,username')->find();
				if($val['money_type']=='a'){
					$list[$key]['order']=M('agent_order')->where(array('id'=>$val['order_id']))->field('id,out_trade_no')->find();
				}else{
					$list[$key]['order']=M('order_info')->where(array('id'=>$val['order_id']))->field('id,out_trade_no')->find();
				}
				
			}
			$show = $Page->show();
			$this->assign('show',$show);
			$this->assign('list',$list);
			$this->display();   
		}
		
		
		/*
			在线充值
		*/
		public function recharge(){
			$uid=I('get.uid');
			if($uid>0){	
				$user=M('wechat_user')->where(array('id'=>$uid))->find();
				$this->assign('user',$user);
			}
			$this->display();   
		}
		
}