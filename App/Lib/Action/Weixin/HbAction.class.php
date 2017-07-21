<?php
/*
	微信红包控制器
	
	微信红包接口需要安全证书，安全证书需在微信商户后台下载：
	
	apiclient_cert.pem
	apiclient_key.pem
	rootca.pem
	
	
*/

class HbAction extends Action{

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
	
	public function sql(){
		$db=M('wechat_user');
		$map="p_1=9673 or p_2=9673 or p_3=9673";
		$list=$db->where($map)->field('id,p_1,p_2,p_3')->select();
		/*foreach($list as $val){
			if($val['p_1']==9673){
				$db->where(array('id'=>$val['id']))->save(array('p_2'=>688));
			}
			if($val['p_2']==9673){
				$db->where(array('id'=>$val['id']))->save(array('p_3'=>688));
			}
		}*/
		dump($list);
	}
	
	
	public function test(){
		//uid,money
		$ret=wxtransfer(1514,2);
		dump($ret);
	}
	
	//发红包
	
	public function index(){
		header('content-type:text/html;charset=utf-8');
		//引入微信红包类
		import("@.ORG.WxRedPack");
		
		$db=M('wechat_config');
		//获取公众账号信息
		$option=$db->field('appid,appsecret,mchid,partnerkey')->find(1);
		$conf=array(
		'min_value'=>1,
		'max_value'=>2,
		'nick_name'=>'唯道康络',
		'send_name'=>'唯道康络',
		'act_name'=>'提现红包',
		'wishing'=>'祝您财源滚滚，感谢您一路上的支持',
		'remark'=>'祝您财源滚滚，感谢您一路上的支持',
		'logo_imgurl'=>'http://wdkl.yfcms.cn/Public/Weixin/images/14357466811435746681.png'
		);
		//实例化红包类
		$obj=new WxRedPack($option);
		//接口数据
		//$money=rand($conf['min_value'],$conf['max_value']);	//随机红包金额【1-2元】
		$money=200;					//单位分
		$post_arr=array();
		$post_arr['mch_billno']=date('mdHis',time()).rand(1111,9999);//订单号
		$post_arr['mch_id']=$option['mchid'];	//商户号
		$post_arr['wxappid']=$option['appid'];
		$post_arr['nick_name']=$conf['nick_name'];		//红包提供方名称
		$post_arr['send_name']=$conf['send_name'];		//红包发送方名称
		$post_arr['re_openid']='oDKY5xGsf9QeBNnOI3-a2DcZl_X0';		
		//红包接收者openid='oGRGsuPd1v5e4OBPuJhksjRCqr4c'
		$post_arr['total_amount']=$money;			//红包金额(分)(发放金额、最小金额、最大金额必须相等)
		$post_arr['min_value']=$money;				//最小红包金额(发放金额、最小金额、最大金额必须相等)
		$post_arr['max_value']=$money;				//最大红包金额(发放金额、最小金额、最大金额必须相等)
		$post_arr['total_num']=1;					//红包发放总人数(total_num必须为1)
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
		echo "<pre>";
		print_r($return_arr);die();
		if($return_arr['result_code']=='SUCCESS'&&$return_arr['result_code']=='SUCCESS'){
			echo '发送红包成功';
		}
	}
	
	
}