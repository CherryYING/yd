<?php

/**
 * 常用公共函数库
 *
 */
 
/*
	计算升级代理的伦理费用
*/ 
function count_up_agent_fee($uid,$aid){
	
	$user=$db=M('wechat_user')->where(array('id'=>$uid))->find();

	$agent=M('agent_config')->where(array('id'=>$aid))->find();
	
	$arr=array(1=>0,2=>1,3=>2,4=>3);
	//升级
	if($user['role_id']>=2){
		if($aid>=2){
			$pre=M('agent_config')->where(array('id'=>$arr[$user['role_id']]))->find();
			$total_fee=$agent['price']-$pre['price'];			
			$money_cloud=$agent['money_cloud']-$pre['money_cloud'];	//赠送金币
		}
	//直接购买
	}else{
		$total_fee=$agent['price'];
		$money_cloud=$agent['money_cloud'];
	}	
	return array('total_fee'=>$total_fee,'money_cloud'=>$money_cloud);
	
}
 

/*
	根据购物车计算订单最多可使用多少云金币抵现
*/
function count_order_max_money_cloud($cart_goods_list){
	$list=$cart_goods_list;
	$max_money_cloud=0;
	foreach($list as $val){
		$goods=M('goods')->where(array('id'=>$val['goods_id']))->find();
		$max_money_cloud+=$goods['money_cloud']*$val['goods_nums'];	
	}
	return $max_money_cloud;
}

 
/*
	获取会员等级名称
*/
function user_role($role_id){
	$arr=array(1=>'普通会员',2=>'市级代理',3=>'省级代理',4=>'全国代理');
	return $arr[$role_id];
}



/*
	修改商品销量及库存
*/
function goods_nums_reset($order_id){
	$list=M('order_goods')->where(array('order_id'=>$order_id))->select();
	foreach($list as $val){
		//增加销量
		M('goods')->where(array('id'=>$val['goods_id']))->setInc('sale_num',$val['goods_nums']);
		//减少库存
		$goods=M('goods')->where(array('id'=>$val['goods_id']))->find();
		if($goods['store_num']>=$val['goods_nums']){
			$data['store_num']=$goods['store_num']-$val['goods_nums'];
		}else{
			$data['store_num']=0;
		}
		M('goods')->where(array('id'=>$val['goods_id']))->save($data);
	}
}

/*
	订单商品退款
	$refund_id:退款申请id
*/
function goods_refund($refund_id){
	//申请信息
	$info=M('order_refund')->where(array('id'=>$refund_id))->find();	
	//订单商品
	$goods=M('order_goods')->where(array('order_id'=>$info['order_id'],'goods_id'=>$info['goods_id']))->find();
	//订单信息
	$order=M('order_info')->where(array('id'=>$info['order_id']))->find();	
	//申请未处理
	if($info['status']==0&&$info['type']==1){
		//佣金>0；需要撤回佣金
		if($goods['yongjin']>0){
			
			//商品总佣金
			$yongjin=$goods['yongjin']*$goods['goods_nums'];
			
			//下单人信息
			$user=M('wechat_user')->where(array('id'=>$order['uid']))->find();
			//分佣配置信息
			$conf=M('resale_config')->find(1);		
			
			//分销商信息
			$p_1=M('wechat_user')->where(array('id'=>$user['p_1']))->find();		//上一级
			$p_2=M('wechat_user')->where(array('id'=>$user['p_2']))->find();		//上二级
			$p_3=M('wechat_user')->where(array('id'=>$user['p_3']))->find();		//上三级
			$p_4=M('wechat_user')->where(array('id'=>$user['p_4']))->find();		//上4级
			
			$yj_1=$yongjin*$conf['parent_1']*0.01;			//一级分销佣金
			$yj_2=$yongjin*$conf['parent_2']*0.01;			//二级分销佣金
			$yj_3=$yongjin*$conf['parent_3']*0.01;			//三级分销佣金
			$yj_4=$yongjin*$conf['parent_4']*0.01;			//4级分销佣金
			
			//一级分佣
			if($user['p_1']>0&&$yj_1>0){
				
				money_change($user['p_1'],2,$yj_1,'goods_refund','商品退款,佣金撤回',$goods['order_id'],$money_type='b');
				//fenyong(array('uid'=>$user['p_1'],'money'=>$yj_1,'order_id'=>$id));
			}
			
			if($user['p_2']>0&&$yj_2>0){
	
				money_change($user['p_2'],2,$yj_2,'goods_refund','商品退款,佣金撤回',$goods['order_id'],$money_type='b');
			}
			
			if($user['p_3']>0&&$yj_3>0){
	
				money_change($user['p_3'],2,$yj_3,'goods_refund','商品退款,佣金撤回',$goods['order_id'],$money_type='b');
			}
			
			if($user['p_4']>0&&$yj_4>0){
	
				money_change($user['p_4'],2,$yj_4,'goods_refund','商品退款,佣金撤回',$goods['order_id'],$money_type='b');
			}
			
			
			
			//区域代理佣金撤回
			//市代信息
			$city_agent=M('area_agent')->where(array('id'=>$order['city_id']))->find();
			//区代信息
			$county_agent=M('area_agent')->where(array('id'=>$order['district_id']))->find();
			
			$city_yj=$yongjin*$conf['city_agent']*0.01;
			
			$county_yj=$yongjin*$conf['county_agent']*0.01;
			
			if(!empty($city_agent)&&$city_yj>0){
				money_change($order['uid'],2,$city_yj,'goods_refund','商品退款，佣金撤回',$order['id'],'b');
			}
			if(!empty($county_agent)&&$county_yj>0){
				money_change($order['uid'],2,$county_yj,'goods_refund','商品退款，佣金撤回',$order['id'],'b');
			}
			
			
		
		}
		
		//撤回货款
		if($goods['sid']>0){
			/*$goods_money=$goods['goods_price']*$goods['goods_nums']-$goods['pay_money_cloud'];
			money_change($goods['sid'],2,$goods_money,'goods_refund','商品退款：'.$goods['goods_name'],$goods['order_id'],$money_type='p');*/
			//货款历史
			$money_log=M('money_water')->where(array('uid'=>$goods['sid'],'type'=>1,'order_id'=>$goods['order_id'],'way'=>'goods_sale','money_type'=>'p'))->find();
			
			money_change($goods['sid'],2,$money_log['amount'],'goods_refund','商品退款：'.$goods['goods_name'],$goods['order_id'],$money_type='p');
		}
		//修改商品支付状为已退款【1,2,3,4】
		M('order_goods')->where(array('order_id'=>$info['order_id'],'goods_id'=>$info['goods_id']))->save(array('order_status'=>4));
		
		//改为已处理
		M('order_refund')->where(array('id'=>$refund_id))->save(array('status'=>1,'shop_handle_status'=>1));
	}
}


/*
	【订单状态修改】
	
	$order_goods_id:订单商品id
	
	$order_status:1,2,3
	
*/
function order_status_change($order_goods_id,$order_status){
	$db=M('order_goods');
	$goods=$db->where(array('id'=>$order_goods_id))->find();
	$goods_list=$db->where(array('id'=>$goods['order_id']))->select();
	$db->where(array('id'=>$order_goods_id))->save(array('order_status'=>$order_status));
	$confirm_goods_count=0;				//已签收数量
	$send_goods_count=0;				//已发货数量
	$unsend_goods_count=0;				//未发货数量
	foreach($goods_list as $val){
		if($val['order_status']==3){
			$confirm_goods_count+=1;
		}
		if($val['order_status']==2){
			$send_goods_count+=1;
		}
		if($val['order_status']==2){
			$unsend_goods_count+=1;
		}
	}
	//已签收商品数量==订单商品数量【修改订单状态为：已签收】
	/*if($confirm_goods_count==count($goods_list)){
		M('order_info')->where(array('id'=>$goods['order_id']))->save(array('order_status'=>3));		//已签收
	}
	if($send_goods_count>=1){
		M('order_info')->where(array('id'=>$goods['order_id']))->save(array('order_status'=>2));		//已发货
	}
	if($unsend_goods_count==count($goods_list)){
		M('order_info')->where(array('id'=>$goods['order_id']))->save(array('order_status'=>1));		//未发货
	}*/
	
	$status=array(1=>'未发货',2=>'已发货',3=>'已签收');
	
	if($order_status>1){
		//发送订单状态通知
		order_status_notice($goods['order_id'],$status[$order_status],$order_goods_id);
	}
	
}

/*
	统计销售业绩
	$uid:
	$amount:数量
	$way:收入支出途径
	$remark:中文备注
	$order_id:订单id
	$money_type:A网 || B网
*/
function sell_money_count($uid,$amount,$way,$remark,$order_id,$money_type='b'){

	$log['uid']=$uid;
	$log['money_type']=$money_type;				//资金类型
	$log['amount']=$amount;
	$log['way']=$way;
	$log['remark']=$remark;
	$log['order_id']=$order_id;
	$log['posttime']=time();
	
	$info=M('wechat_user')->where(array('id'=>$uid))->find();
	
	if($log['money_type']=='a'){
		$data['total_sell_money_a']=$info['total_sell_money_a']+$amount;				//变更后的金额
	}else{
		$data['total_sell_money_b']=$info['total_sell_money_b']+$amount;				//变更后的金额
	}
	M('wechat_user')->where(array('id'=>$uid))->save($data);
	//记录日志
	M('sell_money_water')->add($log);
	
}



/*
	支付商家商品销售资金
	params:(Array)$order:订单信息数组
*/
function pay_shop_sale_money($order){
	//订单商品列表
	$goods=M('order_goods')->where(array('order_id'=>$order['id']))->select();
	foreach($goods as $key=>$val){
		if($val['sid']>0){
			//买家实际支付商品金额【（商品零售价-商品佣金）*商品数量+物流费用-支付云金币】
			$pay_money=($val['goods_price']-$val['yongjin'])*$val['goods_nums']+$val['express_fee']-$val['pay_money_cloud'];
			//操作商家资金账户p（B网）
			money_change($val['sid'],1,$pay_money,'goods_sale','商品销售:'.$val['goods_name'],$order['id'],'p');
		}	
	}
}


/*
	A网分佣【购买代理】
	params:$order_id
*/
function fenyong_a($order_id){
	$db=M('agent_order');
	$order=$db->where(array('id'=>$order_id))->find();
	$user=M('wechat_user')->where(array('id'=>$order['uid']))->find();
	
	$agent=array(1=>'市级代理',2=>'省级代理',3=>'全国代理');
	
	$fy_1_log=M('agent_fy')->where(array('uid'=>$user['p_1'],'order_uid'=>$order['uid']))->find();
	$fy_2_log=M('agent_fy')->where(array('uid'=>$user['p_2'],'order_uid'=>$order['uid']))->find();
	$fy_3_log=M('agent_fy')->where(array('uid'=>$user['p_3'],'order_uid'=>$order['uid']))->find();
	$fy_4_log=M('agent_fy')->where(array('uid'=>$user['p_4'],'order_uid'=>$order['uid']))->find();
	
	
	//账号升级上级一律不拿佣金【普通会员直接购买上级拿佣金】	
	if(!in_array($order['total_fee'],array(100,230,399))){
		die();
		return false;
	}
		
	//第1代分佣【市级代理，省级代理，全国代理】
	if($user['p_1']>0&&empty($fy_1_log)){			
		$p_1=M('wechat_user')->where(array('id'=>$user['p_1']))->find();
		
		if($p_1['role_id']==2){				//市级代理【第一代100元，3个】
			
				//查询获得第一代分佣次数
				$count=M('agent_fy')->where(array('uid'=>$p_1['id'],'type'=>1))->count();
				
				if($count<3){
					money_change($p_1['id'],1,100,'sell_agent_1','发展第一代'.$agent[$order['aid']],$order_id,'a');
					//记录A网分佣数据
					agent_fy_log($p_1['id'],1,$order);	
					//统计销售业绩
					sell_money_count($p_1['id'],100,'sell_agent_1','发展第一代'.$agent[$order['aid']],$order_id,'a');
				}	
				
		}else{			//省级代理【第一代100元，无限】 全国代理【第1代100元,无限】
			money_change($p_1['id'],1,100,'sell_agent_1','发展第一代'.$agent[$order['aid']],$order_id,'a');
			//记录A网分佣数据
			agent_fy_log($p_1['id'],1,$order);			
			//统计销售业绩
			sell_money_count($p_1['id'],100,'sell_agent_1','发展第一代'.$agent[$order['aid']],$order_id,'a');
		}		
	
		
	}
	
	
	//第2代分佣【省级代理、全国代理】【省级以上代理订单：向上2级分佣】
	if($user['p_2']>0&&$order['aid']>=2&&empty($fy_2_log)){			
		$p_2=M('wechat_user')->where(array('id'=>$user['p_2']))->find();
		
		if($p_2['role_id']==3){				//省级代理【第二代100元，3个】
			
			//查询获得第二代分佣次数
			$count=M('agent_fy')->where(array('uid'=>$p_2['id'],'type'=>2))->count();
			if($count<3){
				money_change($p_2['id'],1,100,'sell_agent_2','发展第二代'.$agent[$order['aid']],$order_id,'a');
				//记录A网分佣数据
				agent_fy_log($p_2['id'],2,$order);		
				
				//统计销售业绩
				sell_money_count($p_1['id'],100,'sell_agent_2','发展第二代'.$agent[$order['aid']],$order_id,'a');	
			}
			
			
		}elseif($p_2['role_id']==4){		//全国代理【第2代100元,无限】
			money_change($p_2['id'],1,100,'sell_agent_2','发展第二代'.$agent[$order['aid']],$order_id,'a');
			//记录A网分佣数据
			agent_fy_log($p_2['id'],2,$order);		
			
			//统计销售业绩
			sell_money_count($p_2['id'],100,'sell_agent_2','发展第二代'.$agent[$order['aid']],$order_id,'a');		
		}
	}
	
	//第3代分佣【全国代理】【全国代理订单：向上3级分佣】
	if($user['p_3']>0&&$order['aid']==3&&empty($fy_3_log)){
		$p_3=M('wechat_user')->where(array('id'=>$user['p_3']))->find();
		
		if($p_3['role_id']==4){			//全国代理【第3代100元,无限】
		
			money_change($p_3['id'],1,100,'sell_agent_3','发展第三代'.$agent[$order['aid']],$order_id,'a');
			//记录A网分佣数据
			agent_fy_log($p_3['id'],3,$order);		
			
			//统计销售业绩
			sell_money_count($p_3['id'],100,'sell_agent_3','发展第三代'.$agent[$order['aid']],$order_id,'a');		
		}
	}
	
	//第4代分佣【全国代理】【全国代理订单：向上3级分佣】
	if($user['p_4']>0&&$order['aid']==3&&empty($fy_4_log)){
		$p_4=M('wechat_user')->where(array('id'=>$user['p_4']))->find();
		
		if($p_4['role_id']==4){			//全国代理【第4代30元,无限】
		
			money_change($p_4['id'],1,30,'sell_agent_4','分红'.$agent[$order['aid']],$order_id,'a');
			//记录A网分佣数据
			agent_fy_log($p_4['id'],4,$order);		
			
			//统计销售业绩
			sell_money_count($p_4['id'],30,'sell_agent_4','分红'.$agent[$order['aid']],$order_id,'a');		
		}
	}
	
	
	
}


/*
	A网分佣数据
	params:(Int)$uid:分佣者UID
		   (Int)$type:第（1、2、3）代的佣金
		   (Array)$order:代理订单数据
*/
function agent_fy_log($uid,$type,$order){
	$data['uid']=$uid;				//上级UID
	$data['order_uid']=$order['uid'];		//下级UID
	$data['order_id']=$order['id'];
	$data['aid']=$order['aid'];				//购买代理等级
	$data['money']=$order['total_fee'];		//佣金金额
	$data['type']=$type;						//第一代佣金
	$data['posttime']=time();
	M('agent_fy')->add($data);
}


/*
	购买赠送云金币
*/
function send_money_cloud($order_id){
	$order=M('order_info')->where(array('id'=>$order_id))->find();
	$goods=M('order_goods')->where(array('order_id'=>$order_id))->select();
	$money_cloud=0;
	foreach($goods as $val){
		$money_cloud+=$val['money_cloud']*$val['goods_nums'];
	}
	if($money_cloud>0){
		money_cloud_change($order['uid'],1,$money_cloud,'order','下单赠送',$order_id);
		
		//云金币变动通知
		money_cloud_change_notice($order['uid'],1,$money_cloud,$remark);
	}
}

/*
	根据商品sid获取店铺信息
	2016-06-21
*/
function get_shop_field($id,$field){
	$db=M('shop');
	if($id>0){
		$return=$db->where(array('id'=>$id))->field($field)->find();
	}else{
		$return='';	
	}
	return $return[$field];
}

/*
	云金币流水日志
	$uid:
	$type:1收入；2支出
	$amount:数量
	$way:收入支出途径
	$remark:中文备注
*/
function money_cloud_change($uid,$type,$amount,$way,$remark,$order_id=''){

	$log['uid']=$uid;
	$log['type']=$type;
	$log['amount']=$amount;
	$log['way']=$way;
	$log['remark']=$remark;
	$log['order_id']=$order_id;
	$log['posttime']=time();
	
	$info=M('wechat_user')->where(array('id'=>$uid))->find();
	
	if($type==1){			//收入		
		$money=$info['money_cloud']+$amount;				//变更后的金额
	}elseif($type==2){		//支出
		if($info['money_cloud']>=$amount){
			$money=$info['money_cloud']-$amount;			//变更后的金额
		}else{
			$money=0;
		}
	}
	M('wechat_user')->where(array('id'=>$uid))->save(array('money_cloud'=>$money));
	//记录日志
	M('money_cloud_water')->add($log);	
	//云金币变动通知
	money_cloud_change_notice($uid,1,$amount,$remark);	
}



/*
	发送代金券
	param:array('cid','uid','amount','name','over_time')
*/

function coupon_send($arr){
	$db=M('coupon');
	$arr['posttime']=time();			//领取时间
	$id=$db->add($arr);
	coupon_notice($id);	
	return $id;
}

/*
	核销代金券
	param:$id
*/
function coupon_use($id){
	$db=M('coupon');
	$data=array('status'=>1,'cost_time'=>time());
	$db->where(array('id'=>$id))->save($data);
}	

/*
	佣金撤回
*/
function yongjin_refund($order_id){
	//当前订单的分佣历史记录
	$where=array('order_id'=>$order_id,'type'=>1,'money_type'=>array('in','b,p'));
	$fy_log=M('money_water')->where($where)->select();
	if(!empty($fy_log)){
		foreach($fy_log as $key=>$val){
			$log['uid']=$val['uid'];
			$log['type']=2;
			$log['amount']=$val['amount'];
			
			if($val['money_type']=='p'){
				$log['way']='order_refund';				//订单退款
				$log['remark']='订单退款，货款撤回';
			}elseif($val['money_type']=='b'){
				$log['way']='yongjin_refund';			//订单退款
				$log['remark']='订单退款，佣金撤回';
			}
			
			$log['order_id']=$val['order_id'];
			$log['money_type']=$val['money_type'];		//资金类型
			
			//$uid,$type,$amount,$way,$remark,$order_id,$money_type
			money_change($log['uid'],$log['type'],$log['amount'],$log['way'],$log['remark'],$log['order_id'],$log['money_type']);
		}	
	}
}


/*
	获取规格名称
*/
function norm_name($id){
	$res=M('goods_norm')->where(array('id'=>$id))->getField('title');
	return $res;
}


/*
	查询用户是否关注公众号
*/
function is_sub($uid){
	import('@.ORG.Wxhelper');
	$wxconfig=M('wechat_config')->find(1);
	$wxhelper=new Wxhelper($wxconfig);
	//用户id
	$user=M('wechat_user')->where(array('id'=>$uid))->find();
	$ret=$wxhelper->get_user_info($user['wechatid']);
	if($ret['subscribe']==1){
		return true;
	}else{
		return false;
	}
	
}

/*
	订单分佣
*/

function order_fenyong($order_id){
	 $db=M('order_info');
	 $id=$order_id;			//订单id
	 $errcode=0;
	 //订单信息
	$order=$db->where(array('id'=>$id))->find();
	//下单人信息
	$user=M('wechat_user')->where(array('id'=>$order['uid']))->find();
	//订单商品信息
	$goods=M('order_goods')->where(array('order_id'=>$order['id']))->select();
	//分佣配置信息
	$conf=M('resale_config')->find(1);
	
	//产品推荐奖
	product_recom($goods,$conf);
	
	//市代，区代分佣
	agent_fenyong($order,$conf);
	
	
	//分销商信息
	$p_1=M('wechat_user')->where(array('id'=>$user['p_1']))->find();		//上一级
	$p_2=M('wechat_user')->where(array('id'=>$user['p_2']))->find();		//上二级
	$p_3=M('wechat_user')->where(array('id'=>$user['p_3']))->find();		//上三级
	$p_4=M('wechat_user')->where(array('id'=>$user['p_4']))->find();		//上4级
	
	//计算总佣金
	$yongjin=0;
	foreach($goods as $val){
		$yongjin+=$val['yongjin']*$val['goods_nums'];
	}
	
	if($order['fy_status']==1){
		$errcode=1;								//订单已经分佣
		echo $errcode;
		die();
	}
	
	if($yongjin==0){
		$errcode=2;								//无佣金
		echo $errcode;
		die();
	}
	
	//佣金总额大于0 && 未分佣状态，进行分佣
	if($yongjin>0&&$order['fy_status']==0){
		$yj_1=$yongjin*$conf['parent_1']*0.01;			//一级分销佣金
		$yj_2=$yongjin*$conf['parent_2']*0.01;			//二级分销佣金
		$yj_3=$yongjin*$conf['parent_3']*0.01;			//三级分销佣金
		$yj_4=$yongjin*$conf['parent_4']*0.01;			//4级分销佣金
		
		//一级分佣
		if($user['p_1']>0&&$yj_1>0){

			fenyong(array('uid'=>$user['p_1'],'money'=>$yj_1,'order_id'=>$id));
		}
		
		if($user['p_2']>0&&$yj_2>0){

			fenyong(array('uid'=>$user['p_2'],'money'=>$yj_2,'order_id'=>$id));
		}
		
		if($user['p_3']>0&&$yj_3>0){

			fenyong(array('uid'=>$user['p_3'],'money'=>$yj_3,'order_id'=>$id));
		}
		
		if($user['p_4']>0&&$yj_4>0){

			fenyong(array('uid'=>$user['p_4'],'money'=>$yj_4,'order_id'=>$id));
		}
		
		//修改订单分佣状态【已分佣】
		$db->where(array('id'=>$id))->save(array('fy_status'=>1));
	}
	
	echo $errcode;
	
 }
 
 /*
	 分销佣金结算【统计销售业绩】
	 param:array('uid','money','order_id')
*/
function fenyong($arr){
	//统计销售业绩
	$order=M('order_info')->where(array('id'=>$arr['order_id']))->find();
	sell_money_count($arr['uid'],$arr['money'],'product_sell','商品销售',$arr['order_id'],'b');
	
	//资金变动
	money_change($arr['uid'],1,$arr['money'],'yongjin','订单分佣',$arr['order_id']);
}

/*
	市代、区代分佣
	params: (Array)$order:订单信息数组
			(Array)$conf：分佣配置数组
*/
function agent_fenyong($order,$conf){
	//市代信息
	$city_agent=M('area_agent')->where(array('id'=>$order['city_id']))->find();
	//区代信息
	$county_agent=M('area_agent')->where(array('id'=>$order['district_id']))->find();
	
	$city_yj=$order['yongjin']*$conf['city_agent']*0.01;
	
	$county_yj=$order['yongjin']*$conf['county_agent']*0.01;
	
	if(!empty($city_agent)&&$city_yj>0){
		money_change($order['uid'],1,$city_yj,'city_agent','市代佣金',$order['id'],'b');
	}
	if(!empty($county_agent)&&$county_yj>0){
		money_change($order['uid'],1,$county_yj,'county_agent','区代佣金',$order['id'],'b');
	}
}

/*
	产品推荐奖
	param:(Array)$goods:订单商品列表
		  (Array)$conf:佣金比例配置	
*/
function product_recom($goods,$conf){
	
	foreach($goods as $val){
		if($val['sid']>0){
			//供货商信息
			$user=M('wechat_user')->where(array('id'=>$val['sid']))->find();
			//奖金金额
			$yongjin=$val['yongjin']*$val['goods_nums']*$conf['product_recom']*0.01;
			
			
			//存在上级用户
			if($user['p_1']>0&&$yongjin>0){
				
				$money_log=M('money_water')->where(array('uid'=>$user['p_1'],'amount'=>$yongjin,'type'=>1,'money_type'=>'product_recom','remark'=>'产品推荐奖：'.$val['goods_name'],'order_id'=>$val['order_id']))->find();
				
				//防止重复发放产品推荐奖
				if(empty($money_log)){
					//发放产品推荐奖
					money_change($user['p_1'],1,$yongjin,'product_recom','产品推荐奖：'.$val['goods_name'],$val['order_id'],'b');
				}
				
				
				$diary='yongjin:'.$yongjin.'===goods_nums:'.$val['goods_nums'].'===goods_yongjin:'.$val['yongjin'].'===product_recom:'.$conf['product_recom'];
				file_put_contents('product_recom.txt',$diary);

			}
		}	
	}	
}

/*
	升级分销商
*/

function up_resaler($order_id){
	/*//升级条件
	$conf=M('resale_config')->find(1);
	//订单信息
	$order=M('order_info')->where(array('id'=>$order_id))->find();
	if($order['total_fee']>=$conf['resaler_single_order']){
		do_up_resaler($order['uid']);
	}else{
		//查询累计消费金额
		$total_order_fee=M('order_info')->where(array('uid'=>$order['uid']))->sum('total_fee');
		if($total_order_fee>=$conf['resaler_total_order']){
			do_up_resaler($order['uid']);
		}
	}*/
	
}
function do_up_resaler($uid){
	/*$db=M('wechat_user');
	$user=$db->where(array('id'=>$uid))->find();
	if($user['role_id']==1){
		$db->where(array('id'=>$uid))->save(array('role_id'=>2));	
		//模板消息通知用户，升级分销商
		up_resaler_notice($uid);
	}
	return 1;*/
}


/*
	提现状态
*/
function apply_status($state){
	$arr=array(0=>'<font color="red">等待处理</font>',
			   1=>'<font color="green">提现成功</font>',
			   2=>'<font color="red">提现失败</font>');
	return $arr[$state];
}
/*
/*
	根据id获取用户信息对应字段
*/
function get_user($uid,$field){
	$info=M('wechat_user')->where(array('id'=>$uid))->getField($field);
	return $info;
}
/*
	php无限分级
*/
function order($array,$pid=0,$level=0){
	$arr = array();
	foreach($array as $v){
		if($v['fup']==$pid){	//||$v['parent_id']==$pid
			$v['pre']=str_repeat(' — ',$level);
			$arr[] = $v;
			$arr = array_merge($arr,order($array,$v['id'],$level+1));
		}
	}
	return $arr;
}
/*
	订单状态
*/
function order_status($state){
	$arr=array(
	1=>'<font color="red">未发货</font>',
	2=>'<font color="green"><b>已发货</b></font>',
	3=>'<font color="green"><b>已签收</b></font>');
	return $arr[$state];
}
/*
	获取品牌名称
*/
function get_brandname($bid){
	$db=M('goods_brand');
	$info=$db->find($bid);
	return $info['name'];	
}
/*
	获取分类名称
*/
function get_catename($cid){
	$db=M('goods_category');
	$info=$db->find($cid);
	return $info['name'];	
}
/*
	获取性别
*/
function get_sex($sex){
	$arr=array(0=>'未知',1=>'男',2=>'女');
	return $arr[$sex];
}

function node_merge($node,$access=null,$pid=0){
	$arr=array();
	foreach($node as $v){
		if(is_array($access)){
			$v['access']=in_array($v['id'],$access)?1:0;
		}
		if($v['pid']==$pid){
			$v['child']=node_merge($node,$access,$v['id']);
			$arr[]=$v;
		
		}
	}
	return $arr;
}
function cmstype($t,$i){
	$sort[1] = array('分类','栏目','单篇');
	$sort[2] = array('文章','图片','房产');
	return $sort[$t][$i];
}



/*
	获取缩略图地址
*/
function get_thumb($picurl){
	//$picurl="./Data/upload/photo/20141121/1416550895914.png";
	$picurl=str_replace('thumb_','',$picurl);
	$pathinfo=pathinfo($picurl);
	return $pathinfo['dirname'].'/thumb_'.$pathinfo['basename'];
}

/*
	获取原图地址
*/
function get_pic($picurl){
	$picurl=str_replace('thumb_','',$picurl);
	return $picurl;
}

/*
	资金变更
*/

function money_change($uid,$type,$amount,$way,$remark,$order_id,$money_type='b'){
	//资金日志数据
	$log['uid']=$uid;
	$log['type']=$type;
	$log['amount']=$amount;
	$log['way']=$way;
	$log['remark']=$remark;
	$log['order_id']=$order_id;
	$log['posttime']=time();
	$log['money_type']=$money_type;				//资金类型【A网，B网】
	
	$info=M('wechat_user')->where(array('id'=>$uid))->find();
	
	switch($money_type){
		
		case 'a':
			//代理订单信息
			$order=M('agent_order')->where(array('id'=>$order_id))->find();
			$f_user=M('wechat_user')->where(array('id'=>$order['uid']))->find();
			if($type==1){			//收入		
				$money=$info['money_a']+$amount;				//变更后的金额
			}elseif($type==2){		//支出
				$money=$info['money_a']-$amount>0?$info['money_a']-$amount:0;
			}
			
			$data=array('money_a'=>$money);
		break;
		
		case 'b':
			//商品订单
			$order=M('order_info')->where(array('id'=>$order_id))->find();
			$f_user=M('wechat_user')->where(array('id'=>$order['uid']))->find();
			
			if($type==1){			//收入		
				$money=$info['money']+$amount;				//变更后的金额
			}elseif($type==2){		//支出
				$money=$info['money']-$amount>0?$info['money']-$amount:0;
			}
			
			$data=array('money'=>$money);
		break;
		
		case 'p':
			//货款订单信息
			$order=M('order_info')->where(array('id'=>$order_id))->find();
			$f_user=M('wechat_user')->where(array('id'=>$order['uid']))->find();
	
			if($type==1){			//收入		
				$money=$info['money_p']+$amount;				//变更后的金额
			}elseif($type==2){		//支出
				
				$money=$info['money_p']-$amount>0?$info['money_p']-$amount:0;
				
			}
		
			$data=array('money_p'=>$money);
		break;
	
	}
	
	//更新账户资金
	M('wechat_user')->where(array('id'=>$uid))->save($data);
	
	//记录日志
	M('money_water')->add($log);
	//模板消息通知
	money_change_notice($uid,$type,$amount,$remark,$f_user['nickname'],$money_type);				
}

/*
	积分变更
*/

function jifen_change($user_id,$type,$amount,$way,$remark){
	//积分日志数据
	$log['user_id']=$user_id;
	$log['type']=$type;
	$log['amount']=$amount;
	$log['way']=$way;
	$log['way_name']=$remark;
	$log['posttime']=time();
	
	$info=M('wechat_user')->where(array('id'=>$user_id))->find();
	
	if($type==1){			//收入		
		$jifen=$info['jifen']+$amount;
	}elseif($type==2){		//支出
		if($info['jifen']>=$amount){
			$jifen=$info['jifen']-$amount;
		}else{
			$jifen=0;
		}
	}
	M('wechat_user')->where(array('id'=>$user_id))->save(array('jifen'=>$jifen));
	//记录日志
	M('jifen_water')->add($log);
	//模板消息通知
	jifen_change_notice($user_id,$type,$log['amount'],$remark);				
}

/*
	 积分策略
	 @param $type  1收,2支出
	 @param $act 积分动作
	 @param $user_id	用户id
*/
function return_jifen($type,$act,$user_id){
	//查询积分策略
	$jifen_conf=M('jifen_config')->find(1);
	//积分日志数据
	$log['type']=$type;
	$log['user_id']=$user_id;
	$log['posttime']=time();			
	switch($act){
		//注册
		case 'reg':
			$log['way']='reg';				
			$log['way_name']='注册';
			$log['amount']=$jifen_conf['reg'];		//积分数量		
		break;
		//推荐注册
		case 'reg_tui':
			$log['way']='reg_tui';				
			$log['way_name']='推荐用户注册';
			$log['amount']=$jifen_conf['reg_tui'];		//积分数量		
		break;
		//登录
		case 'login':
			$log['way']='login';				
			$log['way_name']='每日登录';
			$log['amount']=$jifen_conf['login'];		//积分数量		
		break;
		//分享
		case 'share':
			$log['way']='share';				
			$log['way_name']='分享';
			$log['amount']=$jifen_conf['share'];		//积分数量		
		break;
		//签到
		case 'sign':
			$log['way']='sign';				
			$log['way_name']='签到';
			$log['amount']=$jifen_conf['sign'];		//积分数量		
		break;
	}
	if($type==1){			//收入		
		M('wechat_user')->where(array('id'=>$user_id))->setInc('jifen',$log['amount']);
	}elseif($type==2){		//支出
		M('wechat_user')->where(array('id'=>$user_id))->setDec('jifen',$log['amount']);
	}
	//记录日志
	if($log['amount']>0){
		M('jifen_water')->add($log);
		//模板消息通知
		jifen_change_notice($user_id,$type,$log['amount'],$log['way_name']);
	}
}


/*
	 图片水印
*/
 function imageWater($dst_img,$src_img,$dst_x=330,$dst_y=40){
	$dst_path = $dst_img;
	$src_path = $src_img;
	//创建图片的实例
	$dst = imagecreatefromstring(file_get_contents($dst_path));
	$src = imagecreatefromstring(file_get_contents($src_path));
	//获取水印图片的宽高
	list($src_w, $src_h) = getimagesize($src_path);
	//将水印图片复制到目标图片上，最后个参数100是设置透明度，这里实现半透明效果
	imagecopymerge($dst, $src, $dst_x, $dst_y, 0, 0, $src_w, $src_h, 100);
	//如果水印图片本身带透明色，则使用imagecopy方法
	//imagecopy($dst, $src, 10, 10, 0, 0, $src_w, $src_h);
	//输出图片
	list($dst_w, $dst_h, $dst_type) = getimagesize($dst_path);
	switch ($dst_type) {
		case 1://GIF
			//header('Content-Type: image/gif');
			imagegif($dst,$dst_img);
			break;
		case 2://JPG
			//header('Content-Type: image/jpeg');
			imagejpeg($dst,$dst_img);
			break;
		case 3://PNG
			//header('Content-Type: image/png');
			imagepng($dst,$dst_img);
			break;
		default:
			break;
	}
	imagedestroy($dst);
	imagedestroy($src);
}

/*
	文字水印
	param:背景图，字体大小，字体倾斜，
*/
 function textWater($dst_img,$size,$text,$x=0,$y=0,$rgb=array(255,255,255),$angle=0){
		//创建图片的实例
		$dst = imagecreatefromstring(file_get_contents($dst_img));
		//打上文字
		$font = './Data/font/black.ttc';//字体
		
		$color = imagecolorallocate($dst, $rgb[0],$rgb[1],$rgb[2]);//字体颜色
		
		$rs=imagefttext($dst,$size,$angle, $x, $y, $color,$font,$text);
		//输出图片
		list($dst_w, $dst_h, $dst_type) = getimagesize($dst_img);
		//dump($dst_type);die();
		switch ($dst_type) {
			case 1://GIF
				//header('Content-Type: image/gif');
				imagegif($dst,$dst_img);
				break;
			case 2://JPG
				//header('Content-Type: image/jpeg');
				imagejpeg($dst,$dst_img);
				break;
			case 3://PNG
				//header('Content-Type: image/png');
				imagepng($dst,$dst_img);
				break;
			default:
				break;
		}
		imagedestroy($dst);
}

/*
	获取文件后缀名
*/
function extend($file_name){
	$extend = pathinfo($file_name);
	$extend = strtolower($extend["extension"]);
	return $extend;
}

/*
	数组=>对象
*/
function array2object($array) {  
   
    if (is_array($array)) {  
        $obj = new StdClass();  
   
        foreach ($array as $key => $val){  
            $obj->$key = $val;  
        }  
    }  
    else { $obj = $array; }  
   
    return $obj;  
}  

/*
	对象=>数组
*/   
function object2array($object) {  
    if (is_object($object)) {  
        foreach ($object as $key => $value) {  
            $array[$key] = $value;  
        }  
    }  
    else {  
        $array = $object;  
    }  
    return $array;  
}  
/**
 * 转换XML文档为数组
 *
 * @author Luis Pater
 * @date 2011-09-06
 * @param string xml内容
 * @return mixed 返回的数组，如果失败，返回false
 */
function xml2array($xml) {
	$xml = simplexml_load_string($xml, "SimpleXMLElement", LIBXML_NOCDATA);
	return simplexml2array($xml);
}

/*
	判断是否为移动设备
*/
function is_mobile()
{ 
    // 如果有HTTP_X_WAP_PROFILE则一定是移动设备
    if (isset ($_SERVER['HTTP_X_WAP_PROFILE']))
    {
        return true;
    } 
    // 如果via信息含有wap则一定是移动设备,部分服务商会屏蔽该信息
    if (isset ($_SERVER['HTTP_VIA']))
    { 
        // 找不到为flase,否则为true
        return stristr($_SERVER['HTTP_VIA'], "wap") ? true : false;
    } 
    // 脑残法，判断手机发送的客户端标志,兼容性有待提高
    if (isset ($_SERVER['HTTP_USER_AGENT']))
    {
        $clientkeywords = array ('nokia',
            'sony',
            'ericsson',
            'mot',
            'samsung',
            'htc',
            'sgh',
            'lg',
            'sharp',
            'sie-',
            'philips',
            'panasonic',
            'alcatel',
            'lenovo',
            'iphone',
            'ipod',
            'blackberry',
            'meizu',
            'android',
            'netfront',
            'symbian',
            'ucweb',
            'windowsce',
            'palm',
            'operamini',
            'operamobi',
            'openwave',
            'nexusone',
            'cldc',
            'midp',
            'wap',
            'mobile'
            ); 
        // 从HTTP_USER_AGENT中查找手机浏览器的关键字
        if (preg_match("/(" . implode('|', $clientkeywords) . ")/i", strtolower($_SERVER['HTTP_USER_AGENT'])))
        {
            return true;
        } 
    } 
    // 协议法，因为有可能不准确，放到最后判断
    if (isset ($_SERVER['HTTP_ACCEPT']))
    { 
        // 如果只支持wml并且不支持html那一定是移动设备
        // 如果支持wml和html但是wml在html之前则是移动设备
        if ((strpos($_SERVER['HTTP_ACCEPT'], 'vnd.wap.wml') !== false) && (strpos($_SERVER['HTTP_ACCEPT'], 'text/html') === false || (strpos($_SERVER['HTTP_ACCEPT'], 'vnd.wap.wml') < strpos($_SERVER['HTTP_ACCEPT'], 'text/html'))))
        {
            return true;
        } 
    } 
    return false;
}

/*
	判断是否为"微信浏览器"
*/
function is_weixin(){
	
	$agent = $_SERVER['HTTP_USER_AGENT']; 
	if(strpos($agent,"icroMessenger")===false) {
		$return=false;  						//不是微信
	}else{
		$return=true;							//是微信
	}
	return $return;
}

/**

 * 生成随机字符串，由小写英文和数字组成。去掉了容易混淆的0o1l之类

 * @param int $int 生成的随机字串长度

 * @param boolean $caps 大小写，默认返回小写组合。true为大写，false为小写

 * @return string 返回生成好的随机字串

 */

function randStr($int = 6, $caps = false) {

	$strings = 'abcdefghjkmnpqrstuvwxyz23456789';

	$return = '';

	for ($i = 0; $i < $int; $i++) {

		srand();

		$rnd = mt_rand(0, 30);

		$return = $return . $strings[$rnd];

	}

	return $caps ? srttoupper($return) : $return;

}

/*
	对二维数组按键值排序
*/
function array_sort($arr,$keys,$type='asc'){
	$keysvalue = $new_array = array();
		foreach ($arr as $k=>$v){
			$keysvalue[$k] = $v[$keys];
		}
		if($type == 'asc'){
			asort($keysvalue);
		}else{
			arsort($keysvalue);
		}
		reset($keysvalue);
		foreach ($keysvalue as $k=>$v){
			$new_array[$k] = $arr[$k];
		}
	return $new_array;
}

/*
	按键值对查找数组
*/
function seekarr($arr=array(),$key,$val){
	$res = array();
	$str = json_encode($arr);
	preg_match_all("/\{[^\{]*\"".$key."\"\:\"".$val."\"[^\}]*\}/",$str,$m);
	if($m && $m[0]){
		foreach($m[0] as $val) $res[] = json_decode($val,true);
	}
	return $res;
}

/*
	递归-按照分类子级关系重排栏目
*/
function sarr($arr,$id){
	global $ic;
	$thisa=array();
	$aarr=seekarr($arr,'fup',$id);	//fup 上级
	if(count($aarr)>0){
		for($i=0;$i<count($aarr);$i++){
			$thisa[$ic]=$aarr[$i];
			$ic+=1;
			$o=$aarr[$i]['id'];	//fid 栏目id
			$toarr=sarr($arr,$o);
			if(count($toarr)>0){
				$thisa=array_merge($thisa,$toarr);
			}
		}
	return $thisa;
	}
}

/*
	获取当前url
*/
function get_curr_url() {
	$sys_protocal = isset($_SERVER['SERVER_PORT']) && $_SERVER['SERVER_PORT'] == '443' ? 'https://' : 'http://';
	$php_self = $_SERVER['PHP_SELF'] ? $_SERVER['PHP_SELF'] : $_SERVER['SCRIPT_NAME'];
	$path_info = isset($_SERVER['PATH_INFO']) ? $_SERVER['PATH_INFO'] : '';
	$relate_url = isset($_SERVER['REQUEST_URI']) ? $_SERVER['REQUEST_URI'] : $php_self.(isset($_SERVER['QUERY_STRING']) ? '?'.$_SERVER['QUERY_STRING'] : $path_info);
	return $sys_protocal.(isset($_SERVER['HTTP_HOST']) ? $_SERVER['HTTP_HOST'] : '').$relate_url;
}


function replace_pic($content){
	preg_match_all('/\[.*?\]/is',$content,$arr);
	if($arr[0]){
		$pic=F('pic','','./data/');
		foreach($arr[0] as $v){
			foreach($pic as $key=>$val){
				if($v=='['.$val.']'){
					$content=str_replace($v,'<img src="'.__ROOT__.'/Public/Images/phiz/'.$key.'.gif"/>',$content);
				}
				continue;
			}
		}
	}
	return $content;
}



/*
	微信红包接口
	params:uid,amount
*/
function wxhb($uid,$amount){
	header('content-type:text/html;charset=utf-8');
	//引入微信红包类
	import("@.ORG.WxRedPack");
	//用户信息
	$user=M('wechat_user')->field('id,wechatid')->where(array('id'=>$uid))->find();
	$db=M('wechat_config');
	//获取公众账号信息
	$option=$db->find(1);		//->field('appid,appsecret,mchid,partnerkey')
	$conf=array(
		'nick_name'=>$option['pubchatname'],
		'send_name'=>$option['pubchatname'],
		'act_name'=>'佣金提现红包',
		'wishing'=>'祝您财源滚滚，感谢您的支持',
		'remark'=>'祝您财源滚滚，感谢您的支持',
	);
	//实例化红包类
	$obj=new WxRedPack($option);
	//接口数据
	//$money=rand($conf['min_value'],$conf['max_value']);	//随机红包金额【1-2元】
	$money=$amount*100;		//【单位：分】
	$post_arr=array();
	$post_arr['mch_billno']=$option['mchid'].date('Ymd',time()).rand(1111111111,9999999999);//订单号
	$post_arr['mch_id']=$option['mchid'];	//商户号
	$post_arr['wxappid']=$option['appid'];
	//$post_arr['nick_name']=$conf['nick_name'];		//红包提供方名称
	$post_arr['send_name']=$conf['send_name'];		//红包发送方名称
	$post_arr['re_openid']=$user['wechatid'];//'oDKY5xGsf9QeBNnOI3-a2DcZl_X0';//
	
	$post_arr['total_amount']=$money;			//红包金额(分)(发放金额、最小金额、最大金额必须相等)
	//$post_arr['min_value']=$money;				//最小红包金额(发放金额、最小金额、最大金额必须相等)
	//$post_arr['max_value']=$money;				//最大红包金额(发放金额、最小金额、最大金额必须相等)
	$post_arr['total_num']=1;					//红包发放总人数(total_num必须为1)
	$post_arr['wishing']=$conf['wishing'];			//红包祝福语
	$post_arr['client_ip']=I('server.SERVER_ADDR');//调用接口的机器IP(应该是服务器IP)
	$post_arr['act_name']=$conf['act_name'];		//活动名称
	$post_arr['remark']=$conf['remark'];			//备注信息
	//========================非必填项(预留参数)==========================//
	
	//========================非必填项==========================//
	$post_arr['nonce_str']=$obj->createNoncestr();				//随机字符串，不长于32位
	//签名
	$post_arr['sign']=$obj->getSign($post_arr);
	//调用发送红包接口
	$return_arr=$obj->sendRedPack($post_arr);
	if($return_arr['return_code']=='SUCCESS'&&$return_arr['result_code']=='SUCCESS'){
		$return=1;				//成功
		$log['status']=1;
	}else{
		$return=2;				//失败
		$log['status']=2;
	}
	$log['uid']=$uid;
	$log['openid']=$return_arr['re_openid'];
	$log['total_amount']=$return_arr['total_amount']*0.01;			//单位：元
	$log['mch_billno']=$return_arr['mch_billno'];
	$log['return_code']=$return_arr['return_code'];
	$log['result_code']=$return_arr['result_code'];	
	$log['return_msg']=$return_arr['return_msg'];
	$log['err_code']=$return_arr['err_code'];
	$log['err_code_des']=$return_arr['err_code_des'];
	$log['send_listid']=$return_arr['send_listid'];
	$log['posttime']=time();
	//记录红包日志
	M('wechat_hb_list')->add($log);		
	return $return;
	//echo "<pre>";
	//print_r($return_arr);die();
}





/*
	微信转账
*/
function wxtransfer($uid,$amount){
	header('content-type:text/html;charset=utf-8');
	//引入微信红包类
	import("@.ORG.WxRedPack");
	//用户信息
	$user=M('wechat_user')->field('id,wechatid')->where(array('id'=>$uid))->find();
	$db=M('wechat_config');
	//获取公众账号信息
	$option=$db->find(1);		//->field('appid,appsecret,mchid,partnerkey')
	//实例化红包类
	$obj=new WxRedPack($option);
	//接口数据
	$money=$amount*100;		//【单位：分】
	$post_arr=array();
	
	$post_arr['mch_appid']=$option['appid'];
	
	$post_arr['mchid']=$option['mchid'];	//商户号
	
	
	$post_arr['nonce_str']=$obj->createNoncestr();				//随机字符串，不长于32位
	
	$post_arr['partner_trade_no']=$option['mchid'].date('Ymd',time()).rand(1000,9999);//订单号
	
	$post_arr['openid']=$user['wechatid'];//'oDKY5xGsf9QeBNnOI3-a2DcZl_X0';//

	$post_arr['check_name']='NO_CHECK';			//校验真实姓名
	
	$post_arr['amount']=intval($money);			//红包金额(分)(发放金额、最小金额、最大金额必须相等)
	$post_arr['desc']='用户提现';				//用户提现
	$post_arr['spbill_create_ip']=$_SERVER['SERVER_ADDR'];			//调用接口的机器Ip地址	
	
	//签名
	$post_arr['sign']=$obj->getSign($post_arr);
	//调用发送红包接口
	$return_arr=$obj->transfer($post_arr);
	
	dump($post_arr);
	dump($return_arr);
	
	if($return_arr['return_code']=='SUCCESS'&&$return_arr['result_code']=='SUCCESS'){
		$return=1;				//成功
		$log['status']=1;
	}else{
		$return=2;				//失败
		$log['status']=2;
	}
	
	/*$log['uid']=$uid;
	$log['openid']=$return_arr['re_openid'];
	$log['total_amount']=$return_arr['total_amount']*0.01;			//单位：元
	$log['mch_billno']=$return_arr['mch_billno'];
	$log['return_code']=$return_arr['return_code'];
	$log['result_code']=$return_arr['result_code'];	
	$log['return_msg']=$return_arr['return_msg'];
	$log['err_code']=$return_arr['err_code'];
	$log['err_code_des']=$return_arr['err_code_des'];
	$log['send_listid']=$return_arr['send_listid'];
	$log['posttime']=time();
	//记录红包日志
	M('wechat_hb_list')->add($log);		*/
	return $return;
}




/*
	 微信模板消息【通用函数】
	 
	$tpl_arr=array();			
	$tpl_arr['touser']='oQusRs-uFBANUQFuEqbJ7VphdO2s';			//bruce
	$tpl_arr['template_id']='5caLnApJcxhfRRM2TDBM_jauzs8PFjzD0Vy0wStDRIQ';	
	$tpl_arr['url']='http://'.I('server.HTTP_HOST').U('Weixin/Plugin/chat_list');	
	$tpl_arr['topcolor']='#FF0000';	
	$tpl_arr['data']['content']['value']='您好，您有新消息，请注意查收！';
	wx_tpl_msg($tpl_arr);
*/
function wx_tpl_msg($arr){
	import('@.ORG.Wxhelper');
	$wxconfig=M('wechat_config')->find(1);
	$wxhelper=new Wxhelper($wxconfig);
	$rs=$wxhelper->send_tpl_msg($arr);
	return $rs;
}

/*
	资金变动提醒
	
	
	{{first.DATA}}
	变动时间：{{keyword1.DATA}}
	变动金额：{{keyword2.DATA}}
	帐户余额：{{keyword3.DATA}}
	{{remark.DATA}}
	
*/
function  money_change_notice($uid,$type,$amount,$remark,$f_user,$money_type='b'){
	$user=M('wechat_user')->where(array('id'=>$uid))->find();

	$tpl_list=M('wechat_tpl_msg')->find(1);								//模板消息
	
	$tpl_arr=array();
	$tpl_arr['touser']=$user['wechatid'];
	$tpl_arr['template_id']=$tpl_list['money_change'];//'-tXdJLs7_jblv6kTEsyu3ytLH9hnHNUaDfU81rz-d-M';
	$tpl_arr['url']='http://'.I('server.HTTP_HOST').U('Weixin/Ucenter/index');		
	$tpl_arr['topcolor']='#D76729';	
	
	if($type==1){
		$type_name='收入';
	}elseif($type==2){
		$type_name='支出';
	}
	
	$tpl_arr['data']['first']['value']='您的账户资金发生以下变动：'.$type_name.$amount.'元';
	//$tpl_arr['data']['first']['color']='red';
	
	
	/*$tpl_arr['data']['keyword1']['value']=$type_name;							//类型
	$tpl_arr['data']['keyword2']['value']=$amount.'元';							//金额						
	$tpl_arr['data']['keyword3']['value']=date('Y-m-d H:i:s',time());			//时间*/
	
	$tpl_arr['data']['keyword1']['value']=date('Y-m-d H:i:s',time());			//时间
	$tpl_arr['data']['keyword2']['value']=$amount.'元';							//金额						
	$tpl_arr['data']['keyword3']['value']='查看个人中心';							//帐户余额
	
	switch($money_type){
		case 'a';
			$money_type_name='A网';
		break;
		
		case 'b';
			$money_type_name='B网';
		break;
		
		case 'p';
			$money_type_name='货款（B网）';
		break;
	}
	
	$tpl_arr['data']['remark']['value']='资金类型：'.$money_type_name;
	$tpl_arr['data']['remark']['value'].='<br/>下单用户：'.$f_user;
	$tpl_arr['data']['remark']['value'].='<br/>'.$remark;
	$tpl_arr['data']['remark']['value'].=';请您注意查看资金账户,感谢您的关注！';
	
	//$tpl_arr['data']['remark']['color']='red';
	//取消佣金变动提醒
	//wx_tpl_msg($tpl_arr);
}

/*
	提现申请通知【提现申请结果通知】
*/
function take_money1($uid,$money,$remark=''){
	$user=M('wechat_user')->where(array('id'=>$uid))->find();

	$tpl_list=M('wechat_tpl_msg')->find(1);								//模板消息
	
	$tpl_arr=array();
	$tpl_arr['touser']=$user['wechatid'];
	$tpl_arr['template_id']=$tpl_list['take_money'];//'-tXdJLs7_jblv6kTEsyu3ytLH9hnHNUaDfU81rz-d-M';
	$tpl_arr['url']='http://'.I('server.HTTP_HOST').U('Weixin/Ucenter/take_money_list');		
	$tpl_arr['topcolor']='#D76729';	
	
	$tpl_arr['data']['first']['value']='您于'.date('Y-m-d H:i:s',time()).'成功申请提现';
	//$tpl_arr['data']['first']['color']='red';
	$tpl_arr['data']['keyword1']['value']=date('Y-m-d H:i:s',time());					//申请时间
	$tpl_arr['data']['keyword2']['value']=$money.'元';									//金额
	$tpl_arr['data']['keyword3']['value']='申请成功，等待处理';								//申请结果
	//$tpl_arr['data']['keyword4']['value']='微商城提现';
	$tpl_arr['data']['remark']['value']=$remark.'[我们会尽快处理您的提现申请，请您耐心等待]';
	//$tpl_arr['data']['remark']['color']='red';
	wx_tpl_msg($tpl_arr);
}

/*
	提现到账通知
	
	
	{{first.DATA}}
	提现金额：{{keyword1.DATA}}
	提现账户：{{keyword2.DATA}}
	提现时间：{{keyword3.DATA}}
	到账时间：{{keyword4.DATA}}
	{{remark.DATA}}
*/
function take_money2($uid,$money,$remark=''){
	$user=M('wechat_user')->where(array('id'=>$uid))->find();

	$tpl_list=M('wechat_tpl_msg')->find(1);								//模板消息
	
	$tpl_arr=array();
	$tpl_arr['touser']=$user['wechatid'];
	$tpl_arr['template_id']=$tpl_list['take_money2'];	//'w4tcl-xcWuQY1BzSS2-iMWpkrcuAKq3llJ5_7nbZWUo';
		
	$tpl_arr['topcolor']='#D76729';	
	
	$tpl_arr['data']['first']['value']='您的提现申请已成功受理，请您注意查收';
	//$tpl_arr['data']['first']['color']='red';
	/*$tpl_arr['data']['money']['value']=$money.'元';								//到账金额
	$tpl_arr['data']['timet']['value']=date('Y-m-d H:i:s',time());			//到账时间*/
	
	$tpl_arr['data']['keyword1']['value']=$money.'元';							//金额
	$tpl_arr['data']['keyword2']['value']='微信商城';								//账户
	$tpl_arr['data']['keyword3']['value']='暂无';								//提现时间
	$tpl_arr['data']['keyword4']['value']=date('Y-m-d H:i:s',time());			//到账时间
	
	$tpl_arr['data']['remark']['value']=$remark.'[如有疑问，请联系客服]';
	wx_tpl_msg($tpl_arr);
}


/*
	 邀请关注成功【邀请注册成功通知】
	 
	 {{first.DATA}}
	昵称：{{keyword1.DATA}}
	账户：{{keyword2.DATA}}
	注册时间：{{keyword3.DATA}}
	{{remark.DATA}}
*/
function subscribe_notice($uid){
	$user=M('wechat_user')->where(array('id'=>$uid))->find();
	$p_user=M('wechat_user')->where(array('id'=>$user['p_1']))->find();	//上级用户
	//file_put_contents('user',var_export($user,true));
	//file_put_contents('p_user',var_export($p_user,true));
	
	$tpl_list=M('wechat_tpl_msg')->find(1);								//模板消息
	
	$tpl_arr=array();
	$tpl_arr['touser']=$p_user['wechatid'];
	$tpl_arr['template_id']=$tpl_list['subscribe'];//'L_gTGA_5BLAAeoWcSrbOoVRNXkB2LopNz4H9wgABQN4';
		
	$tpl_arr['topcolor']='#D76729';	
	
	$tpl_arr['data']['first']['value']='您好，以下会员通过您的邀请关注了我们';
	//$tpl_arr['data']['first']['color']='#D76729';
	
	/*$tpl_arr['data']['keyword1']['value']=$user['nickname'];								//昵称
	$tpl_arr['data']['keyword2']['value']=date('Y-m-d H:i:s',time());
	$tpl_arr['data']['keyword3']['value']=get_user($user['p_1'],'nickname');				//邀请人*/
	
	$tpl_arr['data']['keyword1']['value']=$user['nickname'];								//昵称
	$tpl_arr['data']['keyword2']['value']='暂无';											//账户
	$tpl_arr['data']['keyword3']['value']=date('Y-m-d H:i:s',time());						//时间
	
	$tpl_arr['data']['remark']['value']='如有疑问，请联系客服';
	wx_tpl_msg($tpl_arr);
}

/*
	订单提交成功【提醒上级用户】
*/
function order_add_ok_parent_notice($order_id){
	$order=M('order_info')->where(array('id'=>$order_id))->find();	
	$goods=M('order_goods')->where(array('order_id'=>$order_id))->find();
	$user=M('wechat_user')->where(array('id'=>$order['uid']))->find();
	//上级用户列表
	$map=array('id'=>array('in',array($user['p_1'],$user['p_2'],$user['p_3'])));
	$parents=M('wechat_user')->where($map)->select();
	$tpl_list=M('wechat_tpl_msg')->find(1);			
	//循环发送模板消息
	foreach($parents as $val){
		//模板消息
		$tpl_arr=array();
		$tpl_arr['touser']=$val['wechatid'];
		$tpl_arr['template_id']=$tpl_list['order_add'];//'dK2f2B2Cg2MuHtXAhP1YhS2Hh9aajKy4DbbPsQXYfu8';//
		//$tpl_arr['url']='http://'.I('server.HTTP_HOST').U('Weixin/Ucenter/order_detail',array('id'=>$order_id));	
		$tpl_arr['topcolor']='#F5390D';	
		
		$tpl_arr['data']['first']['value']='您的下线用户【'.$user['nickname'].'】的订单已提交成功';
		//$tpl_arr['data']['first']['color']='red';
		
		$tpl_arr['data']['orderID']['value']=$order['out_trade_no'];
		$tpl_arr['data']['orderMoneySum']['value']=$order['total_fee'];
		$tpl_arr['data']['backupFieldName']['value']='商品信息:';
		$tpl_arr['data']['backupFieldData']['value']=$goods['goods_name'].'...';
		//$tpl_arr['data']['remark']['value']='请您及时付款，以便我们尽快为您发货';
		$rs=wx_tpl_msg($tpl_arr);
		
	}
	
}

/*
	订单支付成功通知【上级通知】
	
	{{first.DATA}}
	商品名称：{{keyword1.DATA}}
	订单编号：{{keyword2.DATA}}
	支付金额：{{keyword3.DATA}}
	{{remark.DATA}}
	
*/
function order_pay_ok_parent_notice($order_id){
	$order=M('order_info')->where(array('id'=>$order_id))->find();	
	$goods=M('order_goods')->where(array('order_id'=>$order_id))->find();
	$user=M('wechat_user')->where(array('id'=>$order['uid']))->find();
	//上级用户列表
	$map=array('id'=>array('in',array($user['p_1'],$user['p_2'],$user['p_3'])));
	$parents=M('wechat_user')->where($map)->select();
	
	$tpl_list=M('wechat_tpl_msg')->find(1);								//模板消息
	
	foreach($parents as $val){
		$tpl_arr=array();
		$tpl_arr['touser']=$val['wechatid'];
		$tpl_arr['template_id']=$tpl_list['order_pay'];//'dRsdACm5kgQOj_yXGsw9J7chGenATbG9D0lhiwOej8U';	//
		//$tpl_arr['url']='http://'.I('server.HTTP_HOST').U('Weixin/Ucenter/order_detail',array('id'=>$order_id));	
		$tpl_arr['topcolor']='#F5390D';	
		$tpl_arr['data']['first']['value']='您的下线用户【'.$user['nickname'].'】已成功支付订单';
		//$tpl_arr['data']['first']['color']='red';
/*		$tpl_arr['data']['orderMoneySum']['value']=$order['total_fee'];
		$tpl_arr['data']['orderProductName']['value']=$goods['goods_name'].'...';*/
		
		$tpl_arr['data']['keyword1']['value']=$goods['goods_name'].'...';
		$tpl_arr['data']['keyword2']['value']=$order['out_trade_no'];
		$tpl_arr['data']['keyword3']['value']=$order['total_fee'].'元';
		
		$tpl_arr['data']['remark']['value']='如有问题请致电或直接在微信留言，我们将第一时间为您服务！';
		$rs=wx_tpl_msg($tpl_arr);
		
	}
	
	
}

/*
	订单提交成功
	
*/
function order_add_ok_notice($order_id){
	$order=M('order_info')->where(array('id'=>$order_id))->find();	
	$goods=M('order_goods')->where(array('order_id'=>$order_id))->find();
	$user=M('wechat_user')->where(array('id'=>$order['uid']))->find();
	$tpl_list=M('wechat_tpl_msg')->find(1);								//模板消息
	
	$tpl_arr=array();
	$tpl_arr['touser']=$user['wechatid'];
	$tpl_arr['template_id']=$tpl_list['order_add'];//'dK2f2B2Cg2MuHtXAhP1YhS2Hh9aajKy4DbbPsQXYfu8';//
	$tpl_arr['url']='http://'.I('server.HTTP_HOST').U('Weixin/Ucenter/order_detail',array('id'=>$order_id));	
	$tpl_arr['topcolor']='#F5390D';	
	$tpl_arr['data']['first']['value']='您的订单已提交成功';
	//$tpl_arr['data']['first']['color']='red';
	
	$tpl_arr['data']['orderID']['value']=$order['out_trade_no'];
	$tpl_arr['data']['orderMoneySum']['value']=$order['total_fee'];
	$tpl_arr['data']['backupFieldName']['value']='商品信息:';
	$tpl_arr['data']['backupFieldData']['value']=$goods['goods_name'].'...';
	$tpl_arr['data']['remark']['value']='请您及时付款，以便我们尽快为您发货';
	$rs=wx_tpl_msg($tpl_arr);
}

/*
	订单支付成功通知
	{{first.DATA}}
	商品名称：{{keyword1.DATA}}
	订单编号：{{keyword2.DATA}}
	支付金额：{{keyword3.DATA}}
	{{remark.DATA}}
*/
function order_pay_ok_notice($order_id){
	$order=M('order_info')->where(array('id'=>$order_id))->find();	
	$goods=M('order_goods')->where(array('order_id'=>$order_id))->find();
	$user=M('wechat_user')->where(array('id'=>$order['uid']))->find();
	$tpl_list=M('wechat_tpl_msg')->find(1);								//模板消息
	
	$tpl_arr=array();

	$tpl_arr['touser']=$user['wechatid'];
	$tpl_arr['template_id']=$tpl_list['order_pay'];//'dRsdACm5kgQOj_yXGsw9J7chGenATbG9D0lhiwOej8U';	//
	$tpl_arr['url']='http://'.I('server.HTTP_HOST').U('Weixin/Ucenter/order_detail',array('id'=>$order_id));	
	$tpl_arr['topcolor']='#F5390D';	
	$tpl_arr['data']['first']['value']='我们已收到您的货款，开始为您打包商品，请耐心等待';
	//$tpl_arr['data']['first']['color']='red';
	
	/*$tpl_arr['data']['orderMoneySum']['value']=$order['total_fee'];
	$tpl_arr['data']['orderProductName']['value']=$goods['goods_name'].'...';*/
	
	$tpl_arr['data']['keyword1']['value']=$goods['goods_name'].'...';
	$tpl_arr['data']['keyword2']['value']=$order['out_trade_no'];
	$tpl_arr['data']['keyword3']['value']=$order['total_fee'].'元';
	
	
	
	$tpl_arr['data']['remark']['value']='如有问题请致电或直接在微信留言，我们将第一时间为您服务！';
	wx_tpl_msg($tpl_arr);
}

/*
	订单状态改变提醒
	
	{{first.DATA}}
	订单号：{{keyword1.DATA}}
	订单状态：{{keyword2.DATA}}
	商品名称：{{keyword3.DATA}}
	{{remark.DATA}}
	
*/
function order_status_notice($order_id,$order_status,$order_goods_id=''){
	$order=M('order_info')->where(array('id'=>$order_id))->find();	
	$goods=M('order_goods')->where(array('order_id'=>$order_id))->find();
	$user=M('wechat_user')->where(array('id'=>$order['uid']))->find();
	$tpl_list=M('wechat_tpl_msg')->find(1);								//模板消息
	
	$tpl_arr=array();
	$tpl_arr['touser']=$user['wechatid'];
	$tpl_arr['template_id']=$tpl_list['order_status'];//'7GW8pelgEaUqam2QsJTGDSW0IIxnJIrrdeL3tyYW4yQ';	//
	$tpl_arr['url']='http://'.I('server.HTTP_HOST').U('Weixin/Ucenter/order_detail',array('id'=>$order_id));	
	$tpl_arr['topcolor']='#F5390D';	
	$tpl_arr['data']['first']['value']='尊敬的'.$user['nickname'].'，您的订单状态已更新！';
	//$tpl_arr['data']['first']['color']='red';
	
	/*$tpl_arr['data']['OrderSn']['value']=$order['out_trade_no'];
	$tpl_arr['data']['OrderStatus']['value']=$order_status;*/
	
	
	
	$tpl_arr['data']['keyword1']['value']=$order['out_trade_no'];				//订单编号
	$tpl_arr['data']['keyword2']['value']=$order_status;						//订单状态
	
	if($order_goods_id>0){
		$goods=M('order_goods')->where(array('id'=>$order_goods_id))->find();
	}else{
		$goods=M('order_goods')->where(array('order_id'=>$order_id))->find();
	}
	
	$tpl_arr['data']['keyword3']['value']=$goods['goods_name'];	
	
	$tpl_arr['data']['remark']['value']='物流信息：'.$goods['express_name']."\n快递单号：".$goods['express_no']."\n快递电话：".$goods['express_tel'];
	
	wx_tpl_msg($tpl_arr);
}

/*	
	积分变动通知
*/
function jifen_change_notice($uid,$type,$jifen,$remark){
	$user=M('wechat_user')->where(array('id'=>$uid))->find();
	$tpl_list=M('wechat_tpl_msg')->find(1);								//模板消息
	
	
	$tpl_arr=array();
	$tpl_arr['touser']=$user['wechatid'];
	$tpl_arr['template_id']=$tpl_list['jifen_change'];//'Gx-yvl3fpVIONZTn-1D3xV7ihi4-irAfyLb3DErIKS0';	//
	$tpl_arr['url']='http://'.I('server.HTTP_HOST').U('Weixin/Ucenter/index');	
	$tpl_arr['topcolor']='#FF0000';	
	$tpl_arr['data']['first']['value']='您的积分账户变更如下';
	//$tpl_arr['data']['first']['color']='red';
	if($type==1){
		$type_name='增加';
		$tpl_arr['data']['FieldName']['value']='收入途径';
	}elseif($type==2){
		$type_name='减少';
		$tpl_arr['data']['FieldName']['value']='支出途径';
	}
	
	$tpl_arr['data']['Account']['value']=$remark;
	
	$tpl_arr['data']['change']['value']=$type_name;
	$tpl_arr['data']['CreditChange']['value']=$jifen.'(积分)';
	$tpl_arr['data']['CreditTotal']['value']=$user['jifen'].'(积分)';
	$tpl_arr['data']['remark']['value']='您可以用积分在商城兑换礼品！';
	//wx_tpl_msg($tpl_arr);
}

/*	
	访客消息
*/
function visit_notice($f_uid,$t_uid){
	$f_user=M('wechat_user')->where(array('id'=>$f_uid))->find();
	$t_user=M('wechat_user')->where(array('id'=>$t_uid))->find();
	$tpl_list=M('wechat_tpl_msg')->find(1);								//模板消息
	
	
	$tpl_arr=array();
	$tpl_arr['touser']=$t_user['wechatid'];
	$tpl_arr['template_id']=$tpl_list['visit'];//'LEsVFKwICA38Undq7kqeBrdpJEtOs9i5FxycaAFC4Zs';	//
	$tpl_arr['url']='http://'.I('server.HTTP_HOST').U('Weixin/Plugin/chat_room',array('id'=>$f_uid));
	
	$tpl_arr['topcolor']='#FF0000';	
	$tpl_arr['data']['first']['value']='您好，有好友访问了您的推广链接';
	//$tpl_arr['data']['first']['color']='blue';
	$tpl_arr['data']['keynote1']['value']=$f_user['nickname'];
	$tpl_arr['data']['keynote2']['value']=date('Y-m-d H:i:s',time());
	//$tpl_arr['data']['remark']['value']='请点击详情打开会话页面，立即查看并回复消息！';
	wx_tpl_msg($tpl_arr);
}

/*
	好友消息提醒
	
{{first.DATA}}
消息类型：{{keyword1.DATA}}
发送状态：{{keyword2.DATA}}
发送时间：{{keyword3.DATA}}
发送对象：{{keyword4.DATA}}
{{remark.DATA}}
	
*/
function chat_notice($f_uid,$t_uid,$content){
	$f_user=M('wechat_user')->where(array('id'=>$f_uid))->find();
	$t_user=M('wechat_user')->where(array('id'=>$t_uid))->find();
	$tpl_list=M('wechat_tpl_msg')->find(1);								//模板消息
	
	
	$tpl_arr=array();
	$tpl_arr['touser']=$t_user['wechatid'];
	$tpl_arr['template_id']=$tpl_list['chat'];//'LEsVFKwICA38Undq7kqeBrdpJEtOs9i5FxycaAFC4Zs';	//
	$tpl_arr['url']='http://'.I('server.HTTP_HOST').U('Weixin/Plugin/chat_room',array('id'=>$f_uid));	
	$tpl_arr['topcolor']='#FF0000';	
	$tpl_arr['data']['first']['value']='您好，您有新消息，请注意查收';
	//$tpl_arr['data']['first']['color']='blue';
/*	$tpl_arr['data']['keynote1']['value']=$f_user['nickname'];
	$tpl_arr['data']['keynote2']['value']=date('Y-m-d H:i:s',time());*/
	$tpl_arr['data']['keyword1']['value']='站内消息';
	$tpl_arr['data']['keyword2']['value']='成功';
	$tpl_arr['data']['keyword3']['value']=date('Y/m/d H:i',time());
	$tpl_arr['data']['keyword4']['value']='发送人【'.$f_user['nickname'].'】';
	
	$tpl_arr['data']['remark']['value']='【消息内容：'.$content.'】点击详情打开会话页面，立即查看并回复消息！';
	$res=wx_tpl_msg($tpl_arr);
	dump($res);
}


/*
	升级分销商提醒
	
	@{{first.DATA}}
	代理姓名：{{keyword1.DATA}}
	代理微信：{{keyword2.DATA}}
	代理手机：{{keyword3.DATA}}
	{{remark.DATA}}
	
	
	{{first.DATA}}
	会员编号：{{keyword1.DATA}}
	有效期至：{{keyword2.DATA}}
	{{remark.DATA}}
	
*/
function upgrade_agent_notice($uid,$level=''){
	$user=M('wechat_user')->where(array('id'=>$uid))->find();
	$tpl_list=M('wechat_tpl_msg')->find(1);								//模板消息
	
	$agent=array(2=>'市级代理',3=>'省级代理',4=>'全国代理');
	
	$tpl_arr=array();
	$tpl_arr['touser']=$user['wechatid'];
	$tpl_arr['template_id']=$tpl_list['up_resaler'];//'2_vlxJO8KffWzVdiIgu6L-koogbzDu0fySByKv0QgdU';	//
	$tpl_arr['topcolor']='#FF0000';	
	$tpl_arr['data']['first']['value']='您好，恭喜您成功升级为'.$agent[$level];
	//$tpl_arr['data']['first']['color']='red';
	
	$tpl_arr['data']['keyword1']['value']=$user['nickname'].'【'.$agent[$level].'】';
	$tpl_arr['data']['keyword2']['value']=$user['weixin']?$user['weixin']:'暂无';
	$tpl_arr['data']['keyword3']['value']=$user['mobile']?$user['mobile']:'暂无';
	
/*	$tpl_arr['data']['keyword1']['value']=$user['id'].'(代理等级：'.$agent[$level].')';
	$tpl_arr['data']['keyword2']['value']='长期有限';*/
	
	if($level==4){
		$tpl_arr['url']='http://'.I('server.HTTP_HOST').U('Weixin/Agent/set_account');	
		$tpl_arr['data']['remark']['value']='全国代理可以免费开通个人店铺，点击设置账户信息！';
		//$tpl_arr['data']['remark']['color']='red';
	}else{
		$tpl_arr['data']['remark']['value']='如有疑问，请咨询我们的客服人员！';
	}
	
	wx_tpl_msg($tpl_arr);
}
/*
	批量代金券通知【现金券过期通知】
	$data['uid']
	$data['amount']
	$data['over_time']
	$data['num']
	
	
	{{first.DATA}}
	金额：{{keyword1.DATA}}
	门店：{{keyword2.DATA}}
	时间：{{keyword3.DATA}}
	{{remark.DATA}}
	
*/
function coupon_multi_notice($data){
	
	$user=M('wechat_user')->where(array('id'=>$data['uid']))->find();

	$tpl_list=M('wechat_tpl_msg')->find(1);								//模板消息
	
	$tpl_arr=array();
	$tpl_arr['touser']=$user['wechatid'];
	$tpl_arr['template_id']=$tpl_list['coupon_notice'];//'-tXdJLs7_jblv6kTEsyu3ytLH9hnHNUaDfU81rz-d-M';
	$tpl_arr['url']='http://'.I('server.HTTP_HOST').U('Weixin/Ucenter/coupon_list');		
	$tpl_arr['topcolor']='#D76729';	
	
	$tpl_arr['data']['first']['value']='您好，有'.$data['num'].'张优惠券已到账，请注意查收';
	//$tpl_arr['data']['first']['color']='red';
	
	
	$tpl_arr['data']['keyword1']['value']=$data['amount'].'元';				//金额
	$tpl_arr['data']['keyword2']['value']='微信商城';							//适用门店
	if($data['over_time']>0){
		$tpl_arr['data']['keyword3']['value']=date('Y-m-d H:i:s',$data['over_time']).'前';			//使用期限
	}else{
		$tpl_arr['data']['keyword3']['value']='长期有效';			//使用期限
	}			
				
	
	$tpl_arr['data']['remark']['value']='只有订单金额大于优惠券面值时方可使用，每次仅能使用一张！';
	//$tpl_arr['data']['remark']['color']='red';
	wx_tpl_msg($tpl_arr);
}

/*
	代金券通知【现金券过期通知】
*/
function coupon_notice($coupon_id){
	$coupon=M('coupon')->where(array('id'=>$coupon_id))->find();
	
	$user=M('wechat_user')->where(array('id'=>$coupon['uid']))->find();

	$tpl_list=M('wechat_tpl_msg')->find(1);								//模板消息
	
	$tpl_arr=array();
	$tpl_arr['touser']=$user['wechatid'];
	$tpl_arr['template_id']=$tpl_list['coupon_notice'];//'-tXdJLs7_jblv6kTEsyu3ytLH9hnHNUaDfU81rz-d-M';
	$tpl_arr['url']='http://'.I('server.HTTP_HOST').U('Weixin/Ucenter/coupon_list');		
	$tpl_arr['topcolor']='#D76729';	
	
	$tpl_arr['data']['first']['value']='您好，有新的优惠券已到账，请注意查收';
	//$tpl_arr['data']['first']['color']='red';
	$tpl_arr['data']['keyword1']['value']=$coupon['amount'].'元';							//金额
	$tpl_arr['data']['keyword2']['value']=' 微信商城';									//适用门店	
	if($coupon['over_time']>0){
		$tpl_arr['data']['keyword3']['value']=date('Y-m-d H:i:s',$coupon['over_time']).'前';			//使用期限
	}else{
		$tpl_arr['data']['keyword3']['value']='长期有效';			//使用期限
	}					
	
	$tpl_arr['data']['remark']['value']='只有订单金额大于优惠券面值时方可使用，每次仅能使用一张！';
	//$tpl_arr['data']['remark']['color']='red';
	wx_tpl_msg($tpl_arr);
}



/*
	商品售出通知供货商【新销售订单提醒】
	
	{{first.DATA}}
	商品信息：{{keyword1.DATA}}
	商品类型：{{keyword2.DATA}}
	商品数量：{{keyword3.DATA}}
	商品金额：{{keyword4.DATA}}
	购买时间：{{keyword5.DATA}}
	{{remark.DATA}}
*/
function product_sale_notice($order_id){
	$order_goods=M('order_goods')->where(array('order_id'=>$order_id))->select();
	
	foreach($order_goods as $val){
		
		$goods=M('goods')->where(array('id'=>$val['goods_id']))->find();
		
		if($goods['sid']>0){
			$user=M('wechat_user')->where(array('id'=>$goods['sid']))->find();
	
			$tpl_list=M('wechat_tpl_msg')->find(1);								//模板消息
			
			$tpl_arr=array();
			$tpl_arr['touser']=$user['wechatid'];
			$tpl_arr['template_id']=$tpl_list['product_sale_notice'];
			//$tpl_arr['url']='http://'.I('server.HTTP_HOST').U('Weixin/Ucenter/coupon_list');		
			$tpl_arr['topcolor']='#D76729';	
			
			$tpl_arr['data']['first']['value']='您好，您有新的商品订单，请及时通知相关部门发货';
			//$tpl_arr['data']['first']['color']='red';
			$tpl_arr['data']['keyword1']['value']=$val['goods_name'];					//商品信息
			$tpl_arr['data']['keyword2']['value']='实物';							//商品类型	
			$tpl_arr['data']['keyword3']['value']=$val['goods_nums'];				//商品数量
			$tpl_arr['data']['keyword4']['value']=$val['goods_price']*$val['goods_nums'].'元';				//商品金额
			$tpl_arr['data']['keyword5']['value']=date('Y-m-d H:i:s',time());							//购买时间	
				
			
			$tpl_arr['data']['remark']['value']='买家已付款，请及时发货，如有疑问，请联系官方客服！';
			//$tpl_arr['data']['remark']['color']='red';
			wx_tpl_msg($tpl_arr);
			
		}
		
	}
	
	
}


/*
	云金币变动提醒【帐户资金变动提醒】
	
	{{first.DATA}}
	变动时间：{{keyword1.DATA}}
	变动金额：{{keyword2.DATA}}
	帐户余额：{{keyword3.DATA}}
	{{remark.DATA}}
	
*/
function  money_cloud_change_notice($uid,$type,$amount,$remark){
	$user=M('wechat_user')->where(array('id'=>$uid))->find();

	$tpl_list=M('wechat_tpl_msg')->find(1);								//模板消息
	
	$tpl_arr=array();
	$tpl_arr['touser']=$user['wechatid'];
	$tpl_arr['template_id']=$tpl_list['money_cloud_change'];
	$tpl_arr['url']='http://'.I('server.HTTP_HOST').U('Weixin/Ucenter/cloud_coins');		
	$tpl_arr['topcolor']='#D76729';	
	
	$tpl_arr['data']['first']['value']='您的云金币账户发生以下变动【'.$remark.'】：';
	//$tpl_arr['data']['first']['color']='red';
	if($type==1){
		$type_name='收入';
	}elseif($type==2){
		$type_name='支出';
	}
	$tpl_arr['data']['keyword1']['value']=date('Y-m-d H:i:s');					//时间
	$tpl_arr['data']['keyword2']['value']=$type_name.$amount.'云金币';			//变动金额						
	$tpl_arr['data']['keyword3']['value']=$user['money_cloud'].'云金币';					//余额
	
	$tpl_arr['data']['remark']['value']='请您注意查看云金币账户,感谢您的关注！';
	//$tpl_arr['data']['remark']['color']='red';
	wx_tpl_msg($tpl_arr);
}


//===================微信接口函数=================//