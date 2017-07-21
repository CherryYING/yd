<?php
/*
	提现申请
*/
class TakeMoneyAction extends PublicAction{
	public $fee_c;
	public function _initialize(){
		parent::_initialize();
		$this->fee_c=M('resale_config')->find(1);
	}
	public function index(){
		import("@.ORG.Page");
		$db=M('take_money');
		
		$so_key=I('get.key');
		$so_val=I('get.val');
		
		if(in_array($so_key,array('bank_owner','nickname','user_id'))){
			
			if(!empty($so_key)&&!empty($so_val)){
				
				switch($so_key){
					case 'nickname':
						$users=M('wechat_user')->where(array('nickname'=>array('like','%'.$so_val.'%')))->select();
						foreach($users as $key=>$val){
							$user[]=$val['id'];
						}
						$map['user_id']=array('in',$user);
					break;
					
					case 'user_id':
						$map[$so_key]=$so_val;
					break;
					
					default:
						$map[$so_key]=array('like','%'.$so_val.'%');
					break;
					
				}
				
			}
		}
		
		
		$count = $db->where($map)->count();
		$Page = new Page($count,20);
		$show = $Page->show();
		$this->assign('show',$show);
		
		$list=$db->where($map)->order('id desc')->limit($Page->firstRow.','.$Page->listRows)->select();
		foreach($list as $key=>$val){
			$user=M('wechat_user')->where(array('id'=>$val['user_id']))->find();
/*			if($user['role_id']==1){			//会员提现
				$handle_fee=$this->fee_c['tx_fee_1']*0.01*$val['money'];
				$pay_money=$val['money']-$handle_fee;
			}else{
				$handle_fee=$this->fee_c['tx_fee_2']*0.01*$val['money'];
				$pay_money=$val['money']-$handle_fee;
			}
			$list[$key]['handle_fee']=$handle_fee;
			$list[$key]['pay_money']=$pay_money;
			$db->where('id='.$val['id'])->save(array('handle_fee'=>$handle_fee,'pay_money'=>$pay_money));
*/			$list[$key]['user']=$user;
		}
		$this->assign('list',$list);
		$this->display();
	}
	public function edit(){
		$id=I('get.id');
		$db=M('take_money');
		$info=$db->where(array('id'=>$id))->find();
		$user=M('wechat_user')->find($info['user_id']);
		$this->assign('info',$info);
		$this->assign('user',$user);
		if($arr=$this->_post()){
			//表单令牌
			if($db->autoCheckToken($_POST)){
				
				switch($info['money_type']){
					case 'a':
						
						if($user['money_a']<$arr['money']){
							$this->error('提现失败，A网资金余额不足！');
							die();
						}	
					
					break;
					
					case 'b':
						
						if($user['money']<$arr['money']){
							$this->error('提现失败，B网资金余额不足！');
							die();
						}	
					
					break;
					
					case 'p':
					
						if($user['money_p']<$arr['money']){
							$this->error('提现失败，货款资金余额不足！');
							die();
						}	
						
					break;
					
					
				}
				
				
				$map=array('id'=>$info['user_id']);
				
				
				if($arr['status']==1){		//成功
					
					
					if($info['pay_way']==3&&$info['money']<=200){			//微信红包提现
					
						$res=wxhb($info['user_id'],$info['pay_money']);		//红包金额为实际到账金额
					
						if($res==1){
							
							//扣除相应提现金额
							switch($info['money_type']){
								case 'a':
									
									M('wechat_user')->where($map)->setDec('money_a',$info['money']);
									$water['money_type']='a';				//资金类型
								
								break;
								
								case 'b':
									
									M('wechat_user')->where($map)->setDec('money',$info['money']);
									$water['money_type']='b';				//资金类型
								
								break;
								
								case 'p':
								
									M('wechat_user')->where($map)->setDec('money_p',$info['money']);
									$water['money_type']='p';				//资金类型
									
								break;
								
								
							}
							
							
							//记录资金流水
							$water['money_type']=$info['money_type'];	//资金类型	
							$water['uid']=$info['user_id'];
							$water['type']=2;							//支出【提现】
							$water['amount']=$info['money'];
							$water['way']='take_money';
							$water['remark']='微信红包提现';
							$water['posttime']=time();
							
							//添加流水记录
							M('money_water')->add($water);
							//模板消息通知
							take_money2($info['user_id'],$info['money']);	
							
						}else{
							$this->error('微信红包发送失败，可能原因：微信商户平台余额不足');
							die();
						}
					}else{				//其他提现方式
						
						//扣除相应提现金额
						switch($info['money_type']){
							case 'a':
								
								M('wechat_user')->where($map)->setDec('money_a',$info['money']);
								$water['money_type']='a';				//资金类型
							
							break;
							
							case 'b':
								
								M('wechat_user')->where($map)->setDec('money',$info['money']);
								$water['money_type']='b';				//资金类型
							
							break;
							
							case 'p':
							
								M('wechat_user')->where($map)->setDec('money_p',$info['money']);
								$water['money_type']='p';				//资金类型
								
							break;
							
							
						}
						
						
						
						//记录资金流水
						
						$water['uid']=$info['user_id'];
						$water['type']=2;						//支出【提现】
						$water['amount']=$info['money'];
						$water['way']='take_money';
						if($info['pay_way']==1){
							$water['remark']='银行卡提现';
						}elseif($info['pay_way']==2){
							$water['remark']='支付宝提现';
						}
						$water['posttime']=time();
						//添加流水记录
						M('money_water')->add($water);
						//模板消息通知
						take_money2($info['user_id'],$info['money']);
						
						//money_change($info['user_id'],2,$info['money'],'take_money','在线提现',$info['id'],$info['money_type']);
						
					}
					
				}else{		//提现失败
					//解除相应金额冻结资金
					/*if(M('wechat_user')->where($map)->setDec('money_dongjie',$info['money'])){
						//增加可用资金
						M('wechat_user')->where($map)->setInc('money',$info['money']);
					}*/
				}
				$arr['handle_time']=time();
				$db->where(array('id'=>$id))->save($arr);
				$this->success('操作成功',U('edit',array('id'=>$id)));
			}
		}else{
			$this->display();
		}
		
	}
	/*
		处理提现
	*/
	public function update(){
		$db=M('take_money');
		$id=I('get.id');
		$data=$this->_post();
		$data['handle_time']=time();
		//申请信息
		$apply_info=M('apply_money')->find($id);
		//用户信息
		$user=M('wechatuser')->find($apply_info['user_id']);
		//账户余额>=提现金额
		if($user['score']>=$apply_info['money']){
			//更新申请表
			$rs=$db->where(array('id'=>$id))->save($data);
			if($rs){
				//从积分余额中扣除提现金额
				M('wechatuser')->where(array('id'=>$apply_info['user_id']))->setDec('score',$apply_info['money']);
				$this->success('处理成功！');
			}else{
				$this->success('处理失败！');
			}
		}else{
			$this->error('账户余额不足！');
		}
	}
	
	
	/*
		删除提现
	*/
	public function del(){
		$db=M('take_money');
		$id=I('get.id');
		$db->where(array('id'=>$id))->delete();
		$this->redirect('index');
	}
}