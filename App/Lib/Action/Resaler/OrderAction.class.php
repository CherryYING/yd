<?php
//订单管理
class OrderAction extends PublicAction{
	public function index(){
		import("@.ORG.Page");
		$db=M('order_info');
		if(isset($_GET['order_status'])){
			$order_status=I('get.order_status');
			$map=array('order_status'=>$order_status);
		}else{
			$map='';
		}
		
		$map['shop_id']=I('session.resaler_id');		
		
		$so_key=I('get.key');
		$so_val=I('get.val');
		if(in_array($so_key,array('order_sn','mobile','consignee'))){
			if(!empty($so_val)&&!empty($so_val)){
				$map[$so_key]=array('like','%'.$so_val.'%');
			}
		}
		if($user_id=I('get.user_id')){
			$map['user_id']=$user_id;
		}
		
		$count = $db->where($map)->count();
		$Page = new Page($count,10);
		
		$list=$db->where($map)->order('id desc')->limit($Page->firstRow.','.$Page->listRows)->select();
		$show = $Page->show();

		$this->assign('show',$show);
		$this->assign('list',$list);
		$this->display();
	}
	//增加
	public function add(){
		$db=M('order_info');
		if(IS_POST){
			$data=$this->_post();
			$db->add($data);
			$this->redirect('index');
		}
		$this->display();
	}
	//编辑
	public function edit(){
            $order_id=$map['id']=I('get.id');
            $db=M('order_info');
            //订单信息
            $data=$db->where($map)->find();
            if(empty($data)){
                $this->error('该订单已不存在！');
            }else{
				$data['order_user']=M('wechatuser')->field('id,nickname,name')->find($data['user_id']);
			}
            $this->assign('data',$data);
            //商品信息
            $order_goods=M('order_goods')->where(array('order_id'=>I('get.id')))->order('id desc')->select();
            $this->assign('order_goods',$order_goods);
			$goods_list=M('order_goods')->where(array('order_id'=>$order_id))->select();
            if($this->_post()){
                $arr=$this->_post();
                //已发货，更新库存
                if($arr['order_status']==2){
                     foreach($goods_list as $val){
                        M('goods')->where(array('id'=>$val['goods_id']))->setDec('store_num',$val['goods_nums']);
                    }
                }
                //订单交易完成
                if($arr['order_status']==3){
					$dis_config=F('distributor_config');
					
					//商品总金额>=3360升级为分销商
					if($data['total_price']>=$dis_config['distributor_price']){
						$rs=M('wechatuser')->where(array('id'=>$data['user_id']))->save(array('user_level'=>2));
					}
					//增加消费总金额
					M('wechatuser')->where(array('id'=>$data['user_id']))->setInc('cost_money',$data['total_fee']);
                    //计算分销商提成
					$parent_info=M('user_relation')->where(array('user_id'=>$data['user_id']))->find();
					$percent=F('distributor_config');
					
					//根据物价指数，计算分销提成基数
					foreach($goods_list as $val){
						$ticheng_base+=$val['goods_price']*$val['goods_nums']*($val['wujiazhishu']/100);
					}
					//一级父级【二级分销商】【提成】
					if(!empty($parent_info['parent_1'])){
						//只有分销商才有提成
						$info=M('wechatuser')->find($parent_info['parent_1']);
						
						$score=$ticheng_base*($percent['second_percent']/100);

						if($info['user_level']==2){
							$leader_base=$score;		//分销商身份，记录提成金额【计算上级领导奖要用】
							//增加分销商收益
							$res=M('wechatuser')->where(array('id'=>$parent_info['parent_1']))->setInc('score',$score);
							if($res){
								//记录日志
								$score_data=array(
									  'user_id'=>$parent_info['parent_1'],
									  'order_id'=>$data['id'],
									  'buyer_id'=>$data['user_id'],
									  'score'=>$score,
									  'percent'=>$percent['second_percent'],
									  'descrip'=>'分销提成',
									  'posttime'=>time()
								  );
								M('score_log')->add($score_data);
							}
							unset($score);
							unset($res);
							unset($score_data);
						}else{		//上级为普通用户
							if($dis_config['send_coupon']==1){		//赠送代金券开关开启，赠送代金券
								/*$coupon_data=array(
									 'user_id'=>$parent_info['parent_1'],
									 'order_id'=>$data['id'],
									 'amount'=>$score,
									 'name'=>'分销奖励',
									 'type'=>'2',				//分销奖励
									 'posttime'=>time()		   
								);
								M('coupon')->add($coupon_data);
								unset($coupon_data);*/
								$jifen_water=array(
									 'user_id'=>$parent_info['parent_1'],
									 'type'=>1,						//增加
									 'order_id'=>$data['id'],
									 'score'=>$score,				//积分数量
									 'posttime'=>time()		   			   
								);
								//记录积分流水
								M('score_water')->add($jifen_water);
								unset($jifen_water);
								//增加用户积分
								M('wechatuser')->where(array('user_id'=>$parent_info['parent_1']))->setInc('jifen',$score);
							}
						}
						unset($info);
					}

					//二级父级【一级分销商】【提成+领导奖】
					if(!empty($parent_info['parent_2'])){
						$info=M('wechatuser')->find($parent_info['parent_2']);
						$score=$ticheng_base*($percent['first_percent']/100);
						if($info['user_level']==2){
							$leader_base2=$score;			//分销商身份，记录提成金额【计算上级领导奖要用】
							if($leader_base>0&&$dis_config['leader_switch']==1){		//领导奖开启
								//领导奖
								$leader_score=$leader_base*($percent['leader_percent']/100);
								$score=$score+$leader_score;
							}
							//增加分销商收益
							$res=M('wechatuser')->where(array('id'=>$parent_info['parent_2']))->setInc('score',$score);
							if($res){
								//记录日志
								$score_data=array(
									  'user_id'=>$parent_info['parent_2'],
									  'order_id'=>$data['id'],
									  'buyer_id'=>$data['user_id'],
									  'score'=>$score,
									  'percent'=>$percent['first_percent'],
									  'posttime'=>time()
								  );
								if(!empty($leader_base)){
									$score_data['descrip']='分销提成+领导奖';
								}else{
									$score_data['descrip']='分销提成';
								}
								M('score_log')->add($score_data);
							}
							unset($score);
							unset($res);
							unset($score_data);
						}else{		//上级为普通用户
							if($dis_config['send_coupon']==1){		//赠送代金券开关开启，赠送代金券
								/*$coupon_data=array(
									 'user_id'=>$parent_info['parent_2'],
									 'order_id'=>$data['id'],
									 'buyer_id'=>$data['user_id'],
									 'amount'=>$score,
									 'name'=>'分销奖励',
									 'type'=>'2',
									 'posttime'=>time()		   
								);
								M('coupon')->add($coupon_data);
								unset($coupon_data);*/
								$jifen_water=array(
									 'user_id'=>$parent_info['parent_2'],
									 'type'=>1,						//增加
									 'order_id'=>$data['id'],
									 'score'=>$score,				//积分数量
									 'posttime'=>time()		   			   
								);
								//记录积分流水
								M('score_water')->add($jifen_water);
								unset($jifen_water);
								//增加用户积分
								M('wechatuser')->where(array('user_id'=>$parent_info['parent_2']))->setInc('jifen',$score);
							}
						}
						unset($info);
					}
					
				//领导奖-begin
				if($dis_config['leader_switch']==1){	
				
					//三级父级【1级和2级的领导奖】【领导奖】【领导奖计算基数为下一级的提成】
					if(!empty($parent_info['parent_3'])){
						$info=M('wechatuser')->find($parent_info['parent_3']);
						if($info['user_level']==2){
							$score=0;		//领导奖
							if($leader_base>0){
								$score=$leader_base*($percent['leader_percent']/100);
							}
							if($leader_base2>0){
								$score=$score+($leader_base2*($percent['leader_percent']/100));
							}
							if($score>0){
								//增加分销商收益
								$res=M('wechatuser')->where(array('id'=>$parent_info['parent_3']))->setInc('score',$score);
								if($res){
									//记录日志
									$score_data=array(
										  'user_id'=>$parent_info['parent_3'],
										  'order_id'=>$data['id'],
										  'buyer_id'=>$data['user_id'],
										  'score'=>$score,
										  'percent'=>$percent['leader_percent'],
										  'descrip'=>'领导奖',
										  'posttime'=>time()
									  );
									M('score_log')->add($score_data);
								}
								unset($score);
								unset($res);
								unset($score_data);
							}
						}
						unset($info);
					}
					//四级父级【2级的领导奖】【只赚取下两级的领导奖，由于3级没有提成，故不计算】【领导奖计算基数为下一级的提成】
					if(!empty($parent_info['parent_4'])){
						$info=M('wechatuser')->find($parent_info['parent_4']);
						if($info['user_level']==2){
							$score=0;		//领导奖
							if($leader_base2>0){
								$score=$leader_base2*($percent['leader_percent']/100);
								//增加分销商收益
								$res=M('wechatuser')->where(array('id'=>$parent_info['parent_4']))->setInc('score',$score);
								if($res){
									//记录日志
									$score_data=array(
										  'user_id'=>$parent_info['parent_4'],
										  'order_id'=>$data['id'],
										  'buyer_id'=>$data['user_id'],
										  'score'=>$score,
										  'percent'=>$percent['leader_percent'],
										  'descrip'=>'领导奖',
										  'posttime'=>time()
									  );
									M('score_log')->add($score_data);
								}
								unset($score);
								unset($res);
								unset($score_data);
							}
						}
						unset($info);
					}
					
				}		
				//领导奖-end
					
                    //增加商品销量
                    foreach($goods_list as $val){
                        M('goods')->where(array('id'=>$val['goods_id']))->setInc('sale_num',$val['goods_nums']);
                    }
                }
                $db->where($map)->save($arr);
                $this->redirect('edit',array('id'=>$order_id));
            }
            $this->display();
	}
	/*
		更新支付状态
	*/
	public function update_pay_status(){
		$order_id=I('get.order_id');
		$db=M('order_info');
		$info=$db->find($order_id);
		if($info['pay_status']==1){
			$data=array('pay_status'=>0,'pay_time'=>0);
		}else{
			$data=array('pay_status'=>1,'pay_time'=>time());
		}
		$db->where(array('id'=>$order_id))->save($data);
		$this->redirect('index',array('p'=>I('get.p','1')));
	}
	//删除
	public function del(){
            if($id=I('get.id')){
                M('order_info')->where(array('id'=>$id))->delete();
                M('order_goods')->where(array('order_id'=>$id))->delete();
                $this->redirect('index');
            }
	}  
	
	/*
		退款成功
	*/
	public function refund_success(){
		import('@.ORG.Wxhelper');
		$order_id=I('get.order_id');
		$db=M('order');
		$order=$db->find($order_id);
		if($order['refund_status']==1){
			$user=M('wechat_users')->find();
			$log=array(
		   'order_id'=>$order['id'],
		   'user_id'=>$order['user_id'],
		   'refund_fee'=>$order['refund_fee'],
		   'posttime'=>$order['refund_time'],
		   'nickname'=>$user['nickname'],
		   );
		   //添加退款记录
		   M('refund_log')->add($log);
		   //发送退款消息【客户消息】
		   	$config['appid']=C('WECHAT_APPID');
			$config['appsecret']=C('WECHAT_APPSECRET');
			$helper=new Wxhelper($config);
			$msg_data['touser']=$user['openid'];			
			$msg_data['template_id']="1i9b4WDKkoxIVGLHqCWKiitTDLnbO6JvaE5Xz9EfDYs";				
			$msg_data['url']='http://'.I('server.HTTP_HOST').U('Wx/Member/order_detail',array('order_id'=>$order_id));
			$msg_data['topcolor']='#FF0000';
			$msg_data['data']['first']=array('value'=>"您好，您的订单已成功退款，请您注意查收");
			$msg_data['data']['keynote1']=array('value'=>$order['refund_fee']);			//退款金额
			$msg_data['data']['keynote2']=array('value'=>'退回微信余额或支付银行卡');					//退款方式
			$msg_data['data']['keynote3']=array('value'=>'参考微信支付系统消息');						//到账时间
			$msg_data['data']['keynote4']=array('value'=>$order['luxian_name']);		//商品描述
			$msg_data['data']['keynote5']=array('value'=>$order['order_sn']);			//交易单号
			$msg_data['data']['keynote6']=array('value'=>'客户申请退款');							//退款原因
			$msg_data['data']['remark']=array('value'=>'单击可查看订单详情。');
			$return=$helper->send_tpl_msg($msg_data);
			//dump($return);die();
			
			unset($msg_data);
			//发送退款消息【通知管理员】
			$msg_data['touser']='#';//'oqNjGs1b-0OmVFIaKAqT80OaXSIA';//			//李洋			
			$msg_data['template_id']="1i9b4WDKkoxIVGLHqCWKiitTDLnbO6JvaE5Xz9EfDYs";				
			//$msg_data['url']='http://'.I('server.HTTP_HOST').U('Wx/Member/order_detail',array('order_id'=>$order_id));
			$msg_data['topcolor']='#FF0000';
			$msg_data['data']['first']=array('value'=>"您好，有一笔客户退款成功，退款详情如下");
			$msg_data['data']['keynote1']=array('value'=>$order['refund_fee']);			//退款金额
			$msg_data['data']['keynote2']=array('value'=>'退回微信余额或支付银行卡');					//退款方式
			$msg_data['data']['keynote3']=array('value'=>'参考微信支付系统消息');						//到账时间
			$msg_data['data']['keynote4']=array('value'=>$order['luxian_name']);		//商品描述
			$msg_data['data']['keynote5']=array('value'=>$order['order_sn']);			//交易单号
			$msg_data['data']['keynote6']=array('value'=>'客户申请退款');							//退款原因
			$msg_data['data']['remark']=array('value'=>'客户昵称：'.$user['nickname'].'；联系人：'.$order['linkman'].'【退款详情可登录微信支付商户后台进行查看。】');
			$return=$helper->send_tpl_msg($msg_data);
			
		   $this->success('退款成功!',U('Order/edit',array('id'=>$order_id)));
		}else{
			$this->redirect('refund_fail',array('order_id'=>$order_id,'err_msg'=>'退款失败'));
		}
		
	}
	/*
		退款失败
	*/
	public function refund_fail(){
		$this->error(I('get.err_msg'),U('Order/edit',array('id'=>I('get.order_id'))));
	}

}