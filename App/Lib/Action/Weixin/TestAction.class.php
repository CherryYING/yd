<?php
/*
	微信红包控制器
	
	微信红包接口需要安全证书，安全证书需在微信商户后台下载：
	
	apiclient_cert.pem
	apiclient_key.pem
	rootca.pem
	
	
	
	
*/

class TestAction extends Action{
	
	function user_agent(){
		$agent = $_SERVER['HTTP_USER_AGENT']; 
		dump($agent);
	}
	
	/*
		 商品图片路径异常
	*/
	public function goods_spic(){
		$db=M('goods');
		$list=$db->where()->field('id,content')->select();
		/*$domian="http://wdkl168.com/";
		foreach($list as $key=>$val){
			if(strpos($val['content'],$domain)){
				unset($list[$key]);
			}	
		}*/
		
		/*foreach($list as $val){
			$val['content']=stripslashes($val['content']);
			$val['content']=str_replace("http://yd.wdkl168.com/",'',$val['content']);
			$val['content']=str_replace("http://xa.wdkl168.com/",'',$val['content']);
			$item[]=$data['content']=str_replace("http://wdkl168.com/",'',$val['content']);
			$db->where(array('id'=>$val['id']))->save($data);
		}*/
		echo 'Ok';
		//dump($item);
		dump($list);
	} 
	
	/*
		删除异常订单
	*/
	public function del_err_order(){
		$db=M('order_info');
		$list=$db->where(array('pay_status'=>0))->select();
		foreach($list as $val){
			M('order_goods')->where(array('order_id'=>$val['id']))->delete();
		}
		$db->where(array('pay_status'=>0))->delete();
		echo 'Ok';
	}
	
	/*
		处理异常数据
		
		update `tp_wechat_user` set money=0,money_a=0,money_p=0,money_cloud=0,total_sell_money_a=0,total_sell_money_b=0,role_id=1 WHERE status=2
	*/
	public function err_data(){
		//$db=M('agent_order');
		$db=M('order_info');
		$list=$db->where(array('total_fee'=>0.01))->select();
		foreach($list as $val){
			$data=array(
			'status'=>2,
			'money'=>0,
			'money_a'=>0,
			'money_p'=>0,
			'money_cloud'=>0,
			'total_sell_money_a'=>0,
			'total_sell_money_b'=>0,
			'role_id'=>1
			);
			M('wechat_user')->where(array('id'=>$val['uid']))->save($data);
		}	
		echo 'Ok';
	}
	
	public function goods_tj(){
		$conf=M('resale_config')->find(1);
		$order_id=6;
		$goods=M('order_goods')->where(array('order_id'=>6))->select();
		product_recom($goods,$conf);
	}
	
	public function chat(){
		$t_uid=$f_uid=1514;
		chat_notice($f_uid,$t_uid);
	}

	public function goods_load(){
		$list=M('goods_old')->where()->select();
		foreach($list as $val){
			$data=$val;
			$data['sid']=503;
			unset($data['id']);
			$id=M('goods')->add($data);
			echo $id."<br/>";
		}
	}
	
	
	public function goods(){
		/*$list=M('goods')->where()->select();
		foreach($list as $val){
			$data['content']=str_replace('http://www.wdkl168.com','http://wdkl168.com',$val['content']);
			//dump($data);
			M('goods')->where(array('id'=>$val['id']))->save($data);
		}*/
		
		
		
		$list=M('goods')->where()->select();
		foreach($list as $val){
			//$data['spic']=str_replace('http://xa.wdkl168.com/uploadimages/productImage','./productImage/weidaoShop',$val['spic']);	
			$data['spic']=str_replace('/uploadimages','',$val['spic']);
			M('goods')->where(array('id'=>$val['id']))->save($data);
		}
		
		/*$list=M('old_product')->where()->select();
		foreach($list as $val){
			$data['name']=$val['product_name'];				//名称
			
			$img=explode('，',$val['product_imgurl']);
			$data['spic']=str_replace('http://xa.wdkl168.com','.',$img[0]);	
			$data['spic']=str_replace('{BaseImgUrl}','http://xa.wdkl168.com/uploadimages/productImage/',$data['spic']);	
			//$data['spic']=str_replace('uploadimages/','',$data['spic']);	
			
			$data['market_price']=$val['product_market'];	//市场价
			$data['price']=$val['product_price'];			//售价
			$data['sale_num']=$val['product_buynum'];		//销量
			$data['is_sale']=$val['product_isshow'];		//上架
			$data['store_num']=$val['product_stock'];		//库存
			$data['content']=$val['product_remark'];		//介绍
			$data['money_cloud']=$val['default3'];			//云金币
			$data['sid']=$val['product_shopid'];			//供货商
			$data['cid']=','.$val['product_producttypeid'].',';
			$data['express_count_way']=1;
			$data['express_price']=$val['product_youfei'];
			$data['posttime']=time();
			$data['yongjin']=$val['product_integral'];
			//dump($data);die();
			M('goods')->add($data);
		}*/
	}
	/*
		删除微信access_token
	*/
	public function clear(){
		unlink("./Data/wxcache/access_token.json");
		unlink("./Data/wxcache/jsapi_ticket.json");
	}
	/*
		测试
	*/
	public function up_fx(){
		$order_id=48;
		//升级条件
		$conf=M('resale_config')->find(1);
		//订单信息
		$order=M('order_info')->where(array('id'=>$order_id))->find();
		if($order['total_fee']>=$conf['resaler_single_order']){
			//do_up_resaler($order['uid']);
			echo 'yes1';
		}else{
			//查询累计消费金额
			$total_order_fee=M('order_info')->where(array('uid'=>$order['uid']))->sum('total_fee');
			if($total_order_fee>=$conf['resaler_total_order']){
				//do_up_resaler($order['uid']);
				echo 'yes2';
			}else{
				echo 'no';
			}
		}
	}
	
	
	/*
	*	$return_arr返回参数说明：
	*	Array
		(
			[return_code] => FAIL
			[return_msg] => 帐号余额不足，请用户充值或更换支付卡后再支付.
			[result_code] => FAIL
			[err_code] => NOTENOUGH
			[err_code_des] => 帐号余额不足，请用户充值或更换支付卡后再支付.
			[mch_billno] => 02131555451625
			[mch_id] => 10011481
			[wxappid] => wxe7e5a985ba3e3b17
			[re_openid] => oGRGsuPd1v5e4OBPuJhksjRCqr4c
			[total_amount] => 100
		)
		Array
		(
			[return_code] => SUCCESS
			[return_msg] => 发放成功.
			[result_code] => SUCCESS
			[err_code] => 0
			[err_code_des] => 发放成功.
			[mch_billno] => 02131606163557
			[mch_id] => 10011481
			[wxappid] => wxe7e5a985ba3e3b17
			[re_openid] => oGRGsuPd1v5e4OBPuJhksjRCqr4c
			[total_amount] => 100
		)
	*
	*/
	//发红包
	
	public function test(){
		//引入微信红包类
		import("@.ORG.WxRedPack");
		
		$db=M('pubchatuser');
		//获取公众账号信息
		$option=$db->field('appid,appsecret,mchid,partnerkey')->find(1);
		
		//实例化红包类
		$obj=new WxRedPack($option);
		//接口数据
		$money=rand($conf['min_value'],$conf['max_value']);	//随机红包金额【1-2元】
		$post_arr=array();
		$post_arr['mch_billno']=date('mdHis',time()).rand(1111,9999);		//订单号
		$post_arr['mch_id']=$option['mchid'];	//商户号
		$post_arr['wxappid']=$option['appid'];
		$post_arr['nick_name']=$conf['nick_name'];		//红包提供方名称
		$post_arr['send_name']=$conf['send_name'];		//红包发送方名称
		$post_arr['re_openid']=$this->wechatid;			//红包接收者openid='oGRGsuPd1v5e4OBPuJhksjRCqr4c'
		$post_arr['total_amount']=$money;			//红包金额(分)(发放金额、最小金额、最大金额必须相等)
		$post_arr['min_value']=$money;				//最小红包金额(发放金额、最小金额、最大金额必须相等)
		$post_arr['max_value']=$money;				//最大红包金额(发放金额、最小金额、最大金额必须相等)
		$post_arr['total_num']=1;				//红包发放总人数(total_num必须为1)
		$post_arr['wishing']=$conf['wishing'];			//红包祝福语
		$post_arr['client_ip']=I('server.SERVER_ADDR');//调用接口的机器IP(应该是服务器IP)
		$post_arr['act_name']=$conf['act_name'];			//活动名称
		$post_arr['remark']=$conf['remark']='测试红包';			//备注信息
		//========================非必填项(预留参数)==========================//
		if(!empty($conf['logo_imgurl'])){
			$post_arr['logo_imgurl']=$conf['logo_imgurl'];		//商户logo
		}
		//$post_arr['share_content']=$conf['share_content'];	//分享文案
		//$post_arr['share_url']=$conf['share_url'];			//分享链接
		//$post_arr['share_imgurl']=$conf['share_imgurl'];	//分享的图片url			
		//========================非必填项==========================//
		$post_arr['nonce_str']=$obj->createNoncestr();				//随机字符串，不长于32位
		//签名
		$post_arr['sign']=$obj->getSign($post_arr);
		//调用发送红包接口
		$return_arr=$obj->sendRedPack($post_arr);
		/*echo "<pre>";
		print_r($return_arr);*/
		if($return_arr['result_code']=='SUCCESS'&&$return_arr['result_code']=='SUCCESS'){
			echo '发送红包成功';
		}
	}
	
	
	
	/*
		导入老数据
	*/
	public function old_user(){
		$db=M('old_users');
		$list=$db->where()->select();				//'users_id=503'
		
		foreach($list as $val){
			$data['id']=$val['users_id'];		
			$data['wechatid']=$val['users_openid'];						//openid
			$data['nickname']=$val['users_name'];							//昵称
			$data['headimgurl']=$val['users_headimgurl'];
			
			$data['p_1']=$val['users_tuijianren'];						//推荐人
			
			if($val['users_tuijianren']>0){
				$p_1=$db->where(array('users_id'=>$val['users_tuijianren']))->find();
				
				if($p_1['users_tuijianren']>0){
					$data['p_2']=$p_1['users_tuijianren'];
					
					$p_2=$db->where(array('users_id'=>$p_1['users_tuijianren']))->find();
					$data['p_3']=$p_2['users_tuijianren'];
					
					$p_3=$db->where(array('users_id'=>$p_2['users_tuijianren']))->find();
					$data['p_4']=$p_3['users_tuijianren'];
					
				}
			}
			
			$data['sex']=$val['users_sex'];								//性别
			$data['name']=$val['users_zhenshiname'];					//真实姓名
			
			
			$data['bank_name']=$val['users_bankname'];					//开户行
			$data['bank_card']=$val['users_bankcard'];					//银行卡号
			$data['qq']=$val['users_qq'];
			$data['email']=$val['users_email'];
			$data['mobile']=$val['users_phone'];
			
			$data['money_cloud']=$val['users_jifen'];					//云金币	
			
			$data['money_a']=$val['users_integral'];					//发展代理佣金
			
			$data['money']=$val['default8'];							//商品销售佣金	
			
			$data['role_id']=$val['users_role'];						//会员角色
			
			$data['posttime']=strtotime($val['users_createdate']);
			
			$id=M('wechat_user_copy')->add($data);
			
			//dump($val['users_role']);
			if($val['users_role']==4){
				
				//$sdata['id']=$id;
				
				/*$shop=M('old_shop')->where(array('userId'=>$val['users_id']))->find();
				
				$data['bank_owner']=$shop['txname'];						//持卡人
				$sdata['bank_card']=$shop['txcardno'];					//银行卡号
				$sdata['shop_address']=$shop['address'];
				$sdata['shop_tel']=$shop['tel'];
				$sdata['shop_name']=$shop['shopName'];*/
				
				
				$account=M('old_employees')->where(array('userno'=>$val['users_no']))->find();
				
				$pwd['username']=$account['userno'];												//登录账号
				$pwd['password']=$account['password']?md5($account['password']):NULL;					//登录密码
				
				M('wechat_user_copy')->where(array('id'=>$id))->save($pwd);
				
				$shop1=M('old_shop_data')->where(array('userid'=>$val['users_id']))->find();
				
				$sdata['id']=$shop1['userid'];					//店铺id
				
				if(empty($data['shop_name'])){
					$sdata['shop_name']=$shop1['orgname'];
				}
				
				if(empty($data['shop_address'])){
					$sdata['shop_address']=$shop1['orgaddress'];	
				}
				$sdata['shop_introduce']=$shop1['orgjieshao'];
				$sdata['shop_descript']=$shop1['orgproinfo'];
				$sdata['shop_remark']=$shop1['orgremark'];	
				
				M('shop')->add($sdata);
				
				//dump(M('shop')->getlastsql());die();
			}
		
			//echo M('wechat_user_copy')->getlastsql();
			echo $id.'<br/>';//die();
		}
		
	}
	
}