<?php
/*
	Ajax请求处理控制器-【微信商城】
*/
class AjaxAction extends Action{
	public $user_id;
	public $user_info;
	public function _initialize(){
		$this->user_id=$_SESSION['user_id'];
		$this->user_info=M('wechat_user')->find($_SESSION['user_id']);
	}
	
	/*
		手机登录
	*/
	public function do_login(){
		$db=M('wechat_user');
		$output=array('errcode'=>0);
		if($this->_post()){
			$jump=I('get.jump');
			//$verify=I('post.verify');
			$username=I('post.username');
			$password=md5(I('post.password'));
			/*if(sessoin('verify')==md5($verify)){
				
			}else{
				$output['errcode']=1;			//验证码错误
			}*/
			$i=$db->where(array('username'=>$username))->find();

			if($i['password']!=$password){
				$output['errcode']=2;		//用户名或密码错误
				$output['msg']='用户名或密码错误';
			}else{				
				session('user_id',$i['id']);		//登录者id
				if(!empty($jump)){
					$output['jump']=base64_decode($jump);
				}
				$login_log=M('user_login_log')->where(array('user_id'=>$i['id']))->find();
				//记录登录时间
				if(empty($login_log)){
					M('user_login_log')->add(array('user_id'=>$i['id'],'ymd'=>date('Ymd',time()),'login_time'=>time()));
				}else{
					M('user_login_log')->where(array('user_id'=>$i['id']))->save(array('login_time'=>time()));
				}
			}
			
			echo json_encode($output);
			
			exit();
		}
	}
	
	/*
		订单签收
	*/
	public function order_confirm(){
		$db=M('order_info');
		if($arr=$this->_post()){
			$db->where(array('id'=>$arr['order_id']))->save(array('order_status'=>3));
			M('order_goods')->where(array('order_id'=>$arr['order_id']))->save(array('order_status'=>3));
			echo 1;
		}
	}
	
	/*
		 设置账户
	*/
	public function set_account(){
		$db=M('wechat_user');
		$json=array('errcode'=>0);
		if($arr=$this->_post()){
			$i=$db->where(array('username'=>$arr['username']))->find();
			if(empty($i)||$this->user_info['username']==$arr['username']){
				$data['username']=$arr['username'];
				$data['password']=md5($arr['password']);
				$db->where(array('id'=>$this->user_id))->save($data);
			}else{
				$json['errcode']=1;
				$json['msg']='账户名已经存在，请重新输入';
			}
			echo json_encode($json);
		}
	}
	
	/*
		购买代理付款成功	notify_url
	*/
	public function agent_notify_url(){
		$order_id=I('get.order_id');
		//订单信息
		$order=M('agent_order')->where(array('id'=>$order_id))->find();
		//异步通知只能通知一次，故要记录通知状态，多次处理异步通知将导致数据出错
		if($order['notify_status']==0){
			
			if($order['pay_money']<100){		//异常订单【订单金额!=支付金额】		
					M('agent_order')->where(array('id'=>$order_id))->save(array('errcode'=>1));
			}else{									// 支付金额<100【异常订单，不分佣】
					//升级代理
					$role=array(1=>2,2=>3,3=>4);				//代理等级id=>会员等级id
					
					$role_name=array(1=>'市级代理',2=>'省级代理',3=>'全国代理');		//代理等级id=>代理名称
					
					M('wechat_user')->where(array('id'=>$order['uid']))->save(array('role_id'=>$role[$order['aid']]));
					
					//升级代理通知
					upgrade_agent_notice($order['uid'],$role[$order['aid']]);
					
					//A网分佣【统计销售业绩】
					fenyong_a($order_id);
		
					//记录云金币明细			
					$remark='升级'.$role_name[$order['aid']];
					money_cloud_change($order['uid'],1,$order['money_cloud'],'upgrade_agent_'.$order['aid'],$remark,$order_id);
					
					
					//更新订单通知状态
					M('agent_order')->where(array('id'=>$order_id))->save(array('notify_status'=>1));
				}
		}

	}
	
	/*
		购买代理
	*/
	public function agent_order(){
		$db=M('agent_order');					//购买代理订单模型
		$json=array('errcode'=>0);
		if($arr=$this->_post()){
			
			//保存个人信息
			$uinfo['name']=$arr['name'];
			$uinfo['mobile']=$arr['mobile'];
			$uinfo['weixin']=$arr['weixin'];
			$uinfo['address']=$arr['address'];
			M('wechat_user')->where(array('id'=>$this->user_id))->save($uinfo);
			
			
			
			$order['aid']=$arr['aid'];
			//$order['total_fee']=$arr['price'];						//订单价格
			//$order['money_cloud']=$arr['money_cloud'];				//赠送云金币
			
			$ret=count_up_agent_fee($this->user_id,$arr['aid']);
			
			$order['total_fee']=$ret['total_fee'];
			$order['money_cloud']=$ret['money_cloud'];
			
			$order['out_trade_no']='Daili'.date('ymdHis').rand(100,999);
			$order['uid']=$this->user_id;
			$order['order_time']=time();
			$order_id=$db->add($order);
			$json['order_id']=$order_id;
			echo json_encode($json);
		}
	}
	
	
	/*
		支付成功，异步通知地址
	*/
	public function notify_url(){
	
		
		//订单id
		$order_id=I('get.order_id');	
		
		//订单信息
		$order=M('order_info')->where(array('id'=>$order_id))->find();
		
		if($order['pay_money']>10){					//订单金额>10;再进行以下操作
			
			
			if($order['notice_status']==0){
				
				//模板消息通知用户
				order_pay_ok_notice($order_id);		
				
				//通知上级
				order_pay_ok_parent_notice($order_id);
				
				
				//商品销售金额存入商户（B网）资金账户
				pay_shop_sale_money($order);
				
				
				//通知商家发货
				product_sale_notice($order_id);
				
				
				//修改库存销量
				goods_nums_reset($order_id);
				
				//修改订单通知状态为1
				M('order_info')->where(array('id'=>$order_id))->save(array('notice_status'=>1));
			
			}
			
			if($order['fy_status']==0){
				//订单分佣
				order_fenyong($order_id);
				$db->where(array('id'=>$order_id))->save(array('fy_status'=>1));
			}
			
		//订单金额<=10，异常订单	
		}else{
			$db->where(array('id'=>$order['id']))->save(array('errcode'=>1));
		}	

		
	}
	
	/*
		商品评价
	*/
	public function goods_reply(){
		$db=M('goods_reply');
		if($arr=$this->_post()){
			$record=$db->where(array('gid'=>$arr['gid'],'uid'=>$this->user_id))->find();
			if(empty($record)){
				$arr['uid']=$this->user_id;
				$arr['uname']=$this->user_info['nickname'];
				$arr['headimg']=$this->user_info['headimgurl'];
				$arr['posttime']=time();
				$db->add($arr);
				echo 1;
			}else{
				echo 2;
			}
			
		}
	}
	
	/*
		商品收藏
	*/
	public function goods_collect(){
		$db=M('goods_collect');
		$json=array('code'=>0);
		if($arr=$this->_post()){
			if(empty($this->user_id)){
				$json['code']=500;
				$json['msg']='您还没有登录';
				echo json_encode($json);
				die();
			}
			$map=array('gid'=>$arr['gid'],'uid'=>$this->user_id);
			$record=$db->where($map)->find();
			if(empty($record)){
				$goods=M('goods')->where(array('id'=>$arr['gid']))->find();
				$arr['name']=$goods['name'];
				$arr['spic']=$goods['spic'];
				$arr['uid']=$this->user_id;
				$arr['posttime']=time();
				$db->add($arr);
				$json['code']=1;		//已收藏
				$json['msg']='收藏成功';
			}else{
				$db->where(array('uid'=>$this->user_id,'gid'=>$arr['gid']))->delete();
				$json['code']=2;		//已取消收藏
				$json['msg']=' 取消收藏';
				
			}
			echo json_encode($json);
		}
	}
	
	/*
		保存购物车地址
	*/
	public function save_address(){
		$_SESSION['address_cache']=$data=$this->_post();
		//入库
		$data['user_id']=$_SESSION['user_id'];
		M('user_address')->add($data);
		echo 1;
	}
	/*
		添加购物车
	*/
	public function addcart(){
		$json=array('errcode'=>0);
		if($arr=$this->_post()){
			$goods_id=$arr['goods_id'];				//商品id
			$goods_nums=$arr['goods_nums'];			//商品数量
			$goods_price=$arr['goods_price'];		//商品价格
			$goods_norm=$arr['goods_norm'];			//商品规格
			addcart($goods_id,$goods_nums,$goods_price,$goods_norm);
			foreach($_SESSION['shop_cart_info'] as $val){
				$cart_goods_nums+=$val['goods_nums'];
			}
			session('cart_goods_nums',$cart_goods_nums);
			$json['goods_count']=$cart_goods_nums;
			$json['msg']='添加购物车成功';
			echo json_encode($json);
		}
		
	}
	/*
		更新购物车
	*/
	public function updatecart(){
		$json=array('errcode'=>0);
		if($arr=$this->_post()){
			$cart_key=I('post.cart_key');
			$act=I('post.act');		//add?增加:减少
			updatecart($cart_key,$act);
			$total_price=0;
			foreach($_SESSION['shop_cart_info'] as $val){
				$cart_goods_nums+=$val['goods_nums'];
				$total_price+=$val['goods_nums']*$val['goods_price'];
			}
			session('cart_goods_nums',$cart_goods_nums);
			$json['total_price']=number_format($total_price,2);
			echo json_encode($json);
		}
		
	}
	public function _updatecart(){
		$goods_id=I('post.goods_id');				//商品id
		$goods_norm=I('post.goods_norm');			//规格
		$act=I('post.act');		//add?增加:减少
		updatecart($goods_id,$act,$goods_norm);
		foreach($_SESSION['shop_cart_info'] as $val){
			$cart_goods_nums+=$val['goods_nums'];
		}
		$_SESSION['cart_goods_nums']=$cart_goods_nums;
		echo 1;
	}
	/*
		删除购物车
	*/
	public function delcart(){
		if($arr=$this->_post()){
			delcart($arr['cart_key']);
			foreach($_SESSION['shop_cart_info'] as $val){
				$cart_goods_nums+=$val['goods_nums'];
			}
			$_SESSION['cart_goods_nums']=$cart_goods_nums;
			echo 1;
		}
		
		
	}
	/*
		提交订单
	*/
	public function order(){
		$time=time();
		if($arr=$this->_post()){
			
			
			$goods_data=cart_product_list();
			
			//本次购物最多可使用云金币数量
			$max_money_cloud=count_order_max_money_cloud($goods_data);
			
			if(empty($goods_data)){
				$output=array('errcode'=>1);
				echo json_encode($output);
				die();
			}
			
			if($arr['money_cloud']<0){
				$output=array('errcode'=>500,'msg'=>'异常订单');
				echo json_encode($output);
				die();
			}
			
			if($addr_id=I('post.addr_id')){
				$addr_info=M('user_address')->where(array('id'=>$addr_id))->find();
				$order_data['consignee']=$addr_info['consignee'];
				$order_data['mobile']=$addr_info['mobile'];
				$order_data['zipcode']=$addr_info['zipcode'];
				
				$order_data['province_id']=$addr_info['province_id'];
				$order_data['city_id']=$addr_info['city_id'];
				$order_data['district_id']=$addr_info['district_id'];
				
				$order_data['province']=$addr_info['province'];
				$order_data['city']=$addr_info['city'];
				$order_data['district']=$addr_info['district'];
				$order_data['address']=$addr_info['address'];
			}else{
				/*if(!empty($arr['province_id'])){
					$order_data['province_id']=$arr['province_id'];
					$order_data['province']=M('region')->where(array('id'=>$arr['province_id']))->getField('region_name');
				}
				
				if(!empty($arr['city_id'])){
					$order_data['city_id']=$arr['city_id'];
					$order_data['city']=M('region')->where(array('id'=>$arr['city_id']))->getField('region_name');
				}
				
				if(!empty($arr['district_id'])){
					$order_data['district_id']=$arr['district_id'];
					$order_data['district']=M('region')->where(array('id'=>$arr['district_id']))->getField('region_name');
				}*/
				
				$order_data['consignee']=$arr['consignee'];
				$order_data['mobile']=$arr['mobile'];
				
				$order_data['province']=$arr['province'];
				$order_data['city']=$arr['city'];
				$order_data['district']=$arr['district'];
				
				$order_data['address']=$arr['address'];
				
				
				
				//插入用户收货地址表
				$order_data['uid']=$this->user_id;
				//M('user_address')->add($order_data);
				
				//快递信息
				/*$express=M('express')->where(array('id'=>$arr['express_id']))->find();
				
				$order_data['express_id']=$arr['express_id'];
				$order_data['express_fee']=$express['price'];
				$order_data['express_name']=$express['name'];*/
			
				
			}
			
			$order_data['uid']=$this->user_id;		//下单用户
			$order_data['out_trade_no']='YDYS'.date('mdHis',time()).rand(1111,9999);
			$order_data['order_time']=$time;
			$order_data['ymd']=date('Ymd',$time);
			$order_data['ym']=date('Ym',$time);
			$order_data['year']=date('Y',$time);
			$order_data['month']=date('m',$time);
			$order_data['pay_way']=$arr['pay_way'];		//1微信支付,2支付宝
			$order_data['yongjin']=0;					//订单总佣金
			
			
			
			//用户本次使用云金币总数量
			$pay_money_cloud=$arr['money_cloud'];
			
			foreach($goods_data as $key=>$val){
				
				//商品信息
				$info=M('goods')->where(array('id'=>$val['goods_id']))->find();
				
				$goods_data[$key]['sid']=$info['sid'];								//商家id
				$goods_data[$key]['goods_name']=$info['name'];
				$goods_data[$key]['goods_spic']=$info['spic'];
				
				if($pay_money_cloud>=($info['money_cloud']*$val['goods_nums'])){
					//用户使用的云金币分配到每个商品上
					$goods_data[$key]['pay_money_cloud']=$info['money_cloud']*$val['goods_nums'];	
					$pay_money_cloud-=$info['money_cloud']*$val['goods_nums'];			
				}else{
					$goods_data[$key]['pay_money_cloud']=$pay_money_cloud;
					$pay_money_cloud=0;	
				}
				
				//$order_data['express_fee']+=$info['express_price'];				//快递总费用
				
				//商品规格信息
				if($val['goods_norm']>0){
					$norm=M('goods_norm')->where(array('id'=>$val['goods_norm']))->find();
					if(empty($norm)){
						$goods_data[$key]['goods_price']=$info['price'];			
					}else{
						$goods_data[$key]['goods_norm_id']=$norm['id'];				//商品规格ID
						$goods_data[$key]['goods_norm']=$norm['title'];				//商品规格名称
						$goods_data[$key]['goods_price']=$norm['price'];			//商品价格【规格不同，价格不同】	
					}
					//商品总金额【商品原始价格总和】
					$order_data['total_price']+=$norm['price']*$val['goods_nums'];			
					unset($norm);
				}else{
					//商品总金额【商品原始价格总和】
					$order_data['total_price']+=$info['price']*$val['goods_nums'];		
				}
				//单个商品佣金【不同规格商品，佣金相同】
				$goods_data[$key]['yongjin']=$info['yongjin'];				
				//计算订单商品总佣金
				$order_data['yongjin']+=$info['yongjin']*$val['goods_nums'];		
			}
			
			//商品总价+快递费用+$express['price']		
			$order_data['express_fee']=$arr['express_fee'];
			
			if($order_data['express_fee']<0){
				$output['errcode']=500;
				$output['msg']='异常订单';
				echo json_encode($output);
				die();
			}
			$order_data['total_fee']=$order_data['total_price']+$order_data['express_fee'];		
			
			//优惠券信息	
			if($arr['coupon_id']>0){
				$coupon=M('coupon')->where(array('id'=>$arr['coupon_id']))->find();
				$order_data['coupon_status']=1;	
				$order_data['coupon_id']=$arr['coupon_id'];						//使用了优惠券
				$order_data['coupon_amount']=$coupon['amount'];
				$order_data['total_fee']=$order_data['total_fee']-$order_data['coupon_amount'];
				M('coupon')->where(array('id'=>$arr['coupon_id']))->save(array('status'=>1,'cost_time'=>time()));
			}
			//云金币消费（1云金币=1元）
			//用户使用云金币数量<=理论值, 再进行云金币抵现
			if($arr['money_cloud']<=$max_money_cloud){
				if($arr['money_cloud']>0&&$this->user_info['money_cloud']>=$arr['money_cloud']){
					$order_data['money_cloud']=$arr['money_cloud'];
					//money_cloud_change($uid,$type,$amount,$way,$remark,$order_id)
					money_cloud_change($this->user_id,2,$arr['money_cloud'],'order','订单消费',0);
					$order_data['total_fee']=$order_data['total_fee']-$order_data['money_cloud'];
				}
			}
			
			
			$shop_id=session('shop_id');
			if(!empty($shop_id)){
				$order_data['shop_id']=session('shop_id');				//店铺id
			}
			
			
			if($order_data['total_fee']<10){
				$output['errcode']=	500;
				$output['msg']=	'异常订单';
				echo json_encode($output);
				die();
			}
			
			//插入订单表
			$order_id=M('order_info')->add($order_data);
			foreach($goods_data as $key=>$val){
				$val['order_id']=$order_id;
				$val['ymd']=date('Ymd',$time);
				$val['posttime']=$time;
				M('order_goods')->add($val);					//插入订单商品表
			}
			
			session('shop_cart_info',null);						//清空购物车
			
			$output=array('pay_way'=>$arr['pay_way'],'order_id'=>$order_id,'errcode'=>0);
			
			//发送订单提交成功通知【模板消息】
			order_add_ok_notice($order_id);
			//通知上级用户
			//order_add_ok_parent_notice($order_id);
			
			echo json_encode($output);
			
		}
		
	}
	
	/*
		订单取消
	*/
	public function order_cancel(){
		$db=M('order_info');
		if($this->_post()){
			$id=I('post.order_id');
			
			//返还订单使用的云金币
			$order=$db->where(array('id'=>$id))->find();
			if($order['money_cloud']>0){
				money_cloud_change($order['uid'],1,$order['money_cloud'],'order_refund','订单取消，返还云金币',$order['id']);
			}
			
			$db->delete($id);												//删除订单信息
			M('order_goods')->where(array('order_id'=>$id))->delete();		//删除订单商品信息
			echo 1;
			exit();
		}
	}
	
	/*
		新增收货地址
	*/
	public function address_add(){
		$data=$this->_post();
		$data['user_id']=$_SESSION['user_id'];
		$data['province']=M('region')->where(array('id'=>$data['province_id']))->getField('region_name');
		$data['city']=M('region')->where(array('id'=>$data['city_id']))->getField('region_name');
		$data['district']=M('region')->where(array('id'=>$data['district_id']))->getField('region_name');
		M('user_address')->add($data);
		echo 1;
	}
	/*
		新增/编辑收货地址
	*/
	public function address_edit(){
		$db=M('user_address');
		if($data=$this->_post()){
			//$id=I('get.id');
			if($data['province_id']>0&&!empty($data['province_id'])){
				$data['province']=M('region')->where(array('id'=>$data['province_id']))->getField('region_name');
			}
			if($data['city_id']>0&&!empty($data['city_id'])){
				$data['city']=M('region')->where(array('id'=>$data['city_id']))->getField('region_name');
			}
			if($data['district_id']>0&&!empty($data['district_id'])){
				$data['district']=M('region')->where(array('id'=>$data['district_id']))->getField('region_name');
			}
			$id=$data['id'];
			if($data['id']>0){
				unset($data['id']);
				$db->where(array('id'=>$id))->save($data);				//编辑
				$addr_id=$id;
			}else{
				$data['uid']=$this->user_id;
				$addr_id=$db->add($data);										//新增
			}
			echo $addr_id;
		}
		
	}
	
	/*
		 删除地址
	*/
	public function address_del(){
		$db=M('user_address');
		if($this->_post()){
			$id=I('post.id');
			$db->delete($id);
			echo 1;
		}
		
	}
	
	/*
		申请提现
	*/
	public function apply_money(){
		$db=M('apply_money');
		$data['money']=I('post.money');
		if(empty($_SESSION['user_id'])){
			echo 2;		//登录超时
		}else{
			$data['user_id']=$_SESSION['user_id'];
			$data['apply_time']=time();
			$data['bank_card']=$_SESSION['user_info']['bank_card'];
			$data['bank_name']=$_SESSION['user_info']['bank_name'];
			$rs=$db->add($data);
			if($rs){
				echo 1;		//操作成功
			}else{
				echo 3;		//操作失败
			}
		}
		
	}
	
	/*
		加载更多商品	      
	*/
	public function product_load(){
		$db=M('goods');
		if($this->_post()){
			$firstRow=I('post.offset');		//从第几条开始
			$listRows=8;		//每次加载条数
			$cid=I('post.cid');
			$rank=I('post.rank');		//排序
			if($rank=='price'){
				$order='price asc';
			}elseif($rank=='hits'){
				$order='hits desc';
			}elseif($rank=='sale_nums'){
				$order='sale_nums desc';
			}else{
				$order='id desc';
			}
			if($cid>0){
				$map['cid']=array('like','%'.$cid.',%');
			}else{
				$map='';
			}
			$map['is_sale']=1;
			$list=$db->where($map)->order($order)->limit($firstRow.','.$listRows)->select();
			foreach($list as $key=>$val){
				$list[$key]['name']=mb_substr($val['name'],0,38,'utf-8');
			}
			echo json_encode($list);
		}
	}
	
	/*
		编辑信息
	*/
	public function info_update(){
		$db=M('wechat_user');
		if($arr=$this->_post()){
			if(!empty($arr['password'])){			//如果密码不为空
				$arr['password']=md5($arr['password']);
			}else{
				unset($arr['password']);
			}
			$db->where(array('id'=>$this->user_id))->save($arr);
			echo '保存成功';
			exit();
		}
	}
	
	/*
		修改密码
	*/
	public function pwd_update(){
		$db=M('wechat_user');
		$errcode=0;
		if($arr=$this->_post()){
			$i=$db->where(array('id'=>$this->user_id))->find();
			if($i['password']!=md5($arr['old_pwd'])){
				$errcode=1;
			}else{
				$data['password']=md5($arr['password']);
				$db->where(array('id'=>$this->user_id))->save($data);
			}
			echo $errcode;exit();
		}
	}
	/*
		积分兑换订单
	*/
	public function jifen_order(){
		$db=M('jifen_order');
		$errcode=0;
		if($arr=$this->_post()){
			$goods_nums=$arr['goods_nums'];
			unset($arr['goods_nums']);
			/*if(isset($arr['addr_id'])){
				$addr=M('user_address')->find($addr_id);
				$arr['consignee']=$addr['consignee'];
				$arr['mobile']=$addr['mobile'];
				$arr['province']=$addr['province'];
				$arr['city']=$addr['city'];
				$arr['district']=$addr['district'];
				$arr['address']=$addr['address'];
				unset($arr['addr_id']);	
			}else{
				
			}*/
			$arr['province']=M('region')->where(array('id'=>$arr['province']))->getField('region_name');
			$arr['city']=M('region')->where(array('id'=>$arr['city']))->getField('region_name');
			$arr['district']=M('region')->where(array('id'=>$arr['district']))->getField('region_name');
			
			$goods=M('jifen_goods')->where(array('id'=>$arr['goods_id']))->find();
			unset($arr['goods_id']);
			$arr['total_fee']=$goods['price']*$goods_nums;
			if($this->user_info['jifen']>=$arr['total_fee']){
				$arr['user_id']=$this->user_id;
				$arr['out_trade_no']='JF'.date('mdHis').rand(1111,9999);
				$arr['order_time']=time();
				$order_id=$db->add($arr);			//插入订单数据
				$order_goods['order_id']=$order_id;
				$order_goods['goods_id']=$goods['id'];
				$order_goods['goods_name']=$goods['name'];
				$order_goods['goods_price']=$goods['price'];
				$order_goods['goods_spic']=$goods['spic'];
				$order_goods['goods_nums']=$goods_nums;
				M('jifen_order_goods')->add($order_goods);
				jifen_change($this->user_id,2,$arr['total_fee'],'exchange','兑换商品');
			}else{
				$errcode=1;		//积分余额不足
			}
			
			echo $errcode;exit();
			
		}
	}
	
	/*
		选择店铺主题
	*/
	public function shop_theme(){
		$db=M('wechat_user');
		if($this->_post()){
			$data['shop_theme']=I('post.shop_theme');
			$db->where(array(array('id'=>$this->user_id)))->save($data);
			echo 1;exit();
		}
	}
	
	/*
		头像上传
	*/
	public function upload_headimg(){
		import('@.ORG.Image.ThinkImage');
		$return=array('flag'=>0,'msg'=>'','img'=>'');
		if(empty($this->user_id)){
			$return['msg']='登录超时，请重新登录！';
			echo json_encode($return);
			exit();
		}
		$dir="./Data/upload/headimg";

		$extArr = array("jpg", "png", "gif");
		if(isset($_POST) and $_SERVER['REQUEST_METHOD'] == "POST"){
			$name = $_FILES['photoimg']['name'];
			$size = $_FILES['photoimg']['size'];
			
			if(empty($name)){
				$return['msg']='请选择要上传的图片!';
				echo json_encode($return);
				exit;
			}
			$ext=extend($name);
			if(!in_array($ext,$extArr)){
				$return['msg']='图片格式错误!';
				echo json_encode($return);
				exit;
			}
			if($size>(100*1024*1024)){
				$return['msg']='图片大小不能超过100KB!';
				echo json_encode($return);
				exit;
			}
			$image_name =$this->user_id.".".$ext;
			$tmp = $_FILES['photoimg']['tmp_name'];
			if(move_uploaded_file($tmp, $dir.'/'.$image_name)){
				$return['flag']=1;
				$return['msg']='上传成功!';
				$return['img']=$dir.'/'.$image_name;
				$img_source=$dir.'/'.$image_name;
				//生成缩略图
				$thumb=$dir.'/thumb_'.$image_name;
				$img = new ThinkImage(THINKIMAGE_GD,$img_source); 
        		$img->thumb(200,200,THINKIMAGE_THUMB_FIXED)->save($thumb);
				//保存数据库
				M('wechat_user')->where(array('id'=>$this->user_id))->save(array('headimgurl'=>$return['img']));
				echo json_encode($return);
				exit;
			}else{
				$return['msg']='上传失败';
				echo json_encode($return);
				exit;
			}
		}
	}
	
	/*
		上传base64
	*/
	public function upload_base64(){
		import('@.ORG.Image.ThinkImage');
		$return=array('flag'=>0,'msg'=>'','img'=>'');
		if(empty($this->user_id)){
			$return['msg']='登录超时，请重新登录！';
			echo json_encode($return);
			exit();
		}
		$dir="./Data/upload/headimg";
		
		$rand=substr(time(),-4);
		$image_name =$this->user_id.'_'.$rand.".jpg";
		
		
		$img_str=$_POST['img_str'];
		$img_str=str_replace('data:image/jpeg;base64,','',$img_str);
		$img_str=str_replace('data:image/png;base64,','',$img_str);
		$img_str=str_replace('data:image/gif;base64,','',$img_str);
		file_put_contents($dir.'/'.$image_name,base64_decode($img_str));
		
		
		$return['flag']=1;
		$return['msg']='上传成功!';
		$return['img']=$dir.'/'.$image_name;
		$img_source=$dir.'/'.$image_name;
		//生成缩略图
		$thumb=$dir.'/thumb_'.$image_name;
		$img = new ThinkImage(THINKIMAGE_GD,$img_source); 
		$img->thumb(200,200,THINKIMAGE_THUMB_FIXED)->save($thumb);
		//保存数据库
		M('wechat_user')->where(array('id'=>$this->user_id))->save(array('headimgurl'=>$thumb));
		echo json_encode($return);
		exit;
	}
	
	/*
		商品评价
	*/
	public function goods_comment(){
		$db=M('goods_comment');
		$id=I('post.id');			//订单商品表【order_goods】id
		$output['errcode']=0;
		if($arr=$this->_post()){
			$goods=M('order_goods')->find($id);
			$data['goods_id']=$goods['goods_id'];		
			$data['goods_name']=$goods['goods_name'];
			$data['goods_spic']=$goods['goods_spic'];
			
			$data['star']=$arr['star'];				//星评
			$data['content']=$arr['content'];		//评价内容
			
			$data['user_id']=$this->user_id;		//评论者id
			$data['nickname']=$this->user_info['username'];
			$data['headimg']=$this->user_info['headimgurl'];
			
			$data['posttime']=time();
			
			if($db->add($data)){
				//改为"已评论"状态
				M('order_goods')->where(array('id'=>$id))->save(array('status'=>1));	
			}
			
			
			$output['msg']='感谢您的评价！';
			$output['order_id']=$goods['order_id'];		//订单id
			
			echo json_encode($output);exit();
		}
	}
	
	/*
		查询商品地区库存
	*/
	public function query_store_nums(){
		$output=array('errcode'=>0);
		if($arr=$this->_post()){
			//记录配送地区
			cookie('province',$arr['province']);
			cookie('city',$arr['city']);
			cookie('district',$arr['district']);
			//查询仓库信息
			$storage=M('storage')->where(array('area_list'=>array('like','%'.$arr['district'].',%')))->find();
			//file_put_contents('sql.txt',M('storage')->getlastsql());
			if(empty($storage)){				//无对应仓库信息
				$output['errcode']=1;			//库存不足
				$output['msg']='该地区无货！';
			}else{
				$map=array('goods_id'=>$arr['goods_id'],'storage_id'=>$storage['id']);	//商品id，仓库id
				$store_info=M('goods_store')->where($map)->find();
				if(empty($store_info)){
					$output['errcode']=1;			//库存不足
					$output['msg']='该地区库存不足！';
				}else{
					$output['store_nums']=$store_info['store_nums'];	//库存数量
					$output['msg']='该地区剩余库存'.$store_info['store_nums'];
				}
				
			}
			echo json_encode($output);
			exit();
		}
	}
	
	/*
		提现
	*/
	public function take_money(){
		$fee_c=M('resale_config')->find(1);
		$db=M('take_money');
		$output=array('errcode'=>0,'msg'=>'提交成功，我们会尽快处理您的申请');
		if($arr=$this->_post()){
			
			if($arr['money']<=0){
				$output['errcode']=3;					
				$output['msg']="非法请求";
				echo json_encode($output);
				die();
			}
			
			$udata=array();
			if($arr['pay_way']==1){
				$udata['bank_name']=$arr['bank_name'];
				$udata['bank_card']=$arr['bank_card'];
				$udata['bank_owner']=$arr['bank_owner'];
			}elseif($arr['pay_way']==2){
				$udata['alipay']=$arr['alipay'];
			}elseif($arr['pay_way']==3){
				$udata['weixin']=$arr['weixin'];
			}
			//保存用户数据
			M('wechat_user')->where(array('id'=>$this->user_id))->save($udata);
			
			
			$map=array('user_id'=>$this->user_id,'money_type'=>$arr['money_type'],'status'=>0);
			$undeal=M('take_money')->where($map)->find();
			if(!empty($undeal)){
				$output['errcode']=1;					//有一笔提现申请正在处理，暂时不能申请提现
				$output['msg']="您有一笔提现申请正在处理，暂时不能申请提";
				echo json_encode($output);
				die();
			}else{
				
				if($arr['money_type']=='a'){
					
					if($this->user_info['money_a']<$arr['money']){
						$output['errcode']=2;					//账户余额不足
						$output['msg']='账户余额不足';
						echo json_encode($output);
						die();
					}
				
				}elseif($arr['money_type']=='b'){
					
					if($this->user_info['money']<$arr['money']){
						$output['errcode']=2;					//账户余额不足
						$output['msg']='账户余额不足';
						echo json_encode($output);
						die();
					}
					
				}elseif($arr['money_type']=='p'){
					if($this->user_info['money_p']<$arr['money']){
						$output['errcode']=2;					//货款余额不足
						$output['msg']='账户余额不足';
						echo json_encode($output);
						die();
					}
				}
				
				//A网免手续费
				if($arr['money_type']=='a'){
					$arr['handle_fee']=0;	//手续费
					$arr['pay_money']=$arr['money'];			//实际到账金额
				}else{
					if($arr['money_type']=='p'){
						$arr['handle_fee']=($fee_c['tx_fee_2']*$arr['money']*0.01)+1;	//手续费【货款每笔加收1元】	
						$arr['pay_money']=$arr['money']-$arr['handle_fee'];			//实际到账金额	
					}else{
						$arr['handle_fee']=$fee_c['tx_fee_1']*$arr['money']*0.01;	//手续费
						$arr['pay_money']=$arr['money']-$arr['handle_fee'];			//实际到账金额
					}
				}
				
				
				//记录提现信息
				$arr['user_id']=$this->user_id;
				/*$data['pay_way']=$arr['pay_way'];						//提现方式
				$data['bank_name']=$arr['bank_name'];
				$data['bank_card']=$arr['bank_card'];
				$data['alipay']=$arr['alipay'];
				$data['weixin']=$arr['weixin'];*/
				$arr['apply_time']=time();
				$take_money_id=$db->add($arr);		
				
				
				//A网提现，无需后台审核
				/*if($arr['money_type']=='a'){
					
					if($arr['money']<=200){			//微信红包提现
		
						$res=wxhb($arr['user_id'],$arr['pay_money']);		//红包金额为实际到账金额
					
						if($res==1){
							
							//扣除相应提现金额
							if($arr['money_type']=='a'){				//A网提现
								M('wechat_user')->where(array('id'=>$this->user_id))->setDec('money_a',$arr['money']);
								$water['money_type']='a';				//资金类型
							}else{
								M('wechat_user')->where(array('id'=>$this->user_id))->setDec('money',$arr['money']);
								$water['money_type']='b';				//资金类型
							}
							//记录资金流水
							$water['money_type']=$arr['money_type'];	//资金类型	
							$water['uid']=$arr['user_id'];
							$water['type']=2;							//支出【提现】
							$water['amount']=$arr['money'];
							$water['way']='take_money';
							//$water['order_id']=$take_money_id;			//提现订单id	
							$water['remark']='微信红包提现';
							$water['posttime']=time();
							
							//添加流水记录
							M('money_water')->add($water);
							//模板消息通知
							take_money2($arr['user_id'],$arr['money'],'实际到账：'.$arr['pay_money'].'元;手续费：'.$arr['handle_fee'].'元');	
							//修改为提现成功
							$db->where(array('id'=>$take_money_id))->save(array('status'=>1));
						}else{
							//$this->error('微信红包发送失败，可能原因：微信商户平台余额不足');
							//die();
						}
					}
					
					
				}*/
				
				
				
			}
			//模板消息通知用户
			take_money1($this->user_id,$arr['money'],'预计实际到账：'.$arr['pay_money'].'元;手续费：'.$arr['handle_fee'].'元');
			echo json_encode($output);
		}
	}
	
	/*
		商品推荐
	*/
	public function goods_tui(){
		$db=M('goods_recommend');
		if($arr=$this->_post()){
			$arr['uid']=$this->user_id;
			$arr['posttime']=time();
			$db->add($arr);	
			echo 1;
		}
	}
	
	/*
		推荐取消
	*/
	public function goods_cancel(){
		$db=M('goods_recommend');
		if($arr=$this->_post()){
			$arr['uid']=$this->user_id;
			$db->where($arr)->delete();	
			echo 1;
		}
	}
	
	/*
		积分商城商品加载
	*/
	public function jifen_product_load(){
		$db=M('jifen_goods');
		if($this->_post()){
			$firstRow=I('post.offset');		//从第几条开始
			$listRows=8;		//每次加载条数
			$order='id desc';
			$map=array();
			$list=$db->where($map)->order('id desc')->limit($firstRow.','.$listRows)->select();
			foreach($list as $key=>$val){
				$list[$key]['name']=mb_substr($val['name'],0,38,'utf-8');
			}
			echo json_encode($list);
		}
	}
	
	/*
		加载资金流水
	*/
	public function fund_load(){
		$db=M('money_water');
		if($arr=$this->_post()){
			$firstRow=I('post.offset');		//从第几条开始
			$listRows=8;		//每次加载条数
			$map=array('user_id'=>$this->user_id);
			$list=$db->where($map)->order('id desc')->limit($firstRow.','.$listRows)->select();
			foreach($list as $key=>$val){
				$list[$key]['posttime']=date('Y-m-d H:i:s',$val['posttime']);
			}
			echo json_encode($list);
		}
	}
	
	/*
		加载积分流水
	*/
	public function jifen_load(){
		$db=M('jifen_water');
		if($arr=$this->_post()){
			$firstRow=I('post.offset');		//从第几条开始
			$listRows=8;		//每次加载条数
			$map=array('user_id'=>$this->user_id);
			$list=$db->where($map)->order('id desc')->limit($firstRow.','.$listRows)->select();
			foreach($list as $key=>$val){
				$list[$key]['posttime']=date('Y-m-d H:i:s',$val['posttime']);
			}
			echo json_encode($list);
		}
	}
	
	/*
		订单退款申请
	*/
	public function	order_refund(){
		$db=M('order_refund');
		if($arr=$this->_post()){
			$map=array('order_id'=>$arr['order_id'],'goods_id'=>$arr['goods_id']);
			//退款申请信息
			$info=$db->where($map)->find();
			$goods=M('order_goods')->where($map)->find();
			$arr['uid']=$this->user_id;
			$arr['sid']=$goods['sid'];		//商家id		
			//商品退款金额
			$arr['refund_money']=$goods['goods_price']*$goods['goods_nums']-$goods['pay_money_cloud'];
			if(empty($info)){
				$arr['posttime']=time();
				$db->add($arr);
			}else{
				$db->where(array('id'=>$info['id']))->save($arr);
			}
			echo 1;		
		}
	}
}