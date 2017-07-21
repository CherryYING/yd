<?php
/*
	支付页面
*/
header('content-type:text/html;charset=utf-8');
include_once("../class/db.class.php");
require_once("alipay.config.php");
require_once("lib/alipay_submit.class.php");

$db=new Connection();
if($order_id=$_GET['order_id']){
	$query=$db->query("select * from tp_agent_order where id='$order_id'");
	$order=$db->get_one($query);
	if($order['pay_status']==1){
		header('location:http://'.$_SERVER['HTTP_HOST'].'/index.php?g=Weixin&m=Ucenter&a=order_detail&order_id='.$order_id);
	}
	
	$query=$db->query("select * from tp_wechat_user where id={$order['uid']}");
	$user=$db->get_one($query);
}


	
//**req_data详细信息**

//服务器异步通知页面路径
$alipay_config['notify_url'] = "http://".$_SERVER['HTTP_HOST']."/wxpay/alipay/upgrade_notify_url.php";
//需http://格式的完整路径，不允许加?id=123这类自定义参数

//页面跳转同步通知页面路径
$alipay_config['return_url'] = "http://".$_SERVER['HTTP_HOST']."/wxpay/alipay/upgrade_return_url.php";
//需http://格式的完整路径，不允许加?id=123这类自定义参数


/**************************请求参数**************************/

//商户订单号
$out_trade_no = $order['out_trade_no'];
//商户网站订单系统中唯一订单号，必填

//订单名称
$subject = $order['agent_name'];
//必填

//付款金额
$total_fee = $order['total_fee'];
//必填

//商品描述，可空
$body = $subject;

/************************************************************/

//构造要请求的参数数组，无需改动
$parameter = array(
		"service"       => $alipay_config['service'],
		"partner"       => $alipay_config['partner'],
		"seller_id"  => $alipay_config['seller_id'],
		"payment_type"	=> $alipay_config['payment_type'],
		"notify_url"	=> $alipay_config['notify_url'],
		"return_url"	=> $alipay_config['return_url'],
		
		"anti_phishing_key"=>$alipay_config['anti_phishing_key'],
		"exter_invoke_ip"=>$alipay_config['exter_invoke_ip'],
		"out_trade_no"	=> $out_trade_no,
		"subject"	=> $subject,
		"total_fee"	=> $total_fee,
		"body"	=> $body,
		"_input_charset"	=> trim(strtolower($alipay_config['input_charset']))
		//其他业务参数根据在线开发文档，添加参数.文档地址:https://doc.open.alipay.com/doc2/detail.htm?spm=a219a.7629140.0.0.kiX33I&treeId=62&articleId=103740&docType=1
        //如"参数名"=>"参数值"
		
);


//建立请求
$alipaySubmit = new AlipaySubmit($alipay_config);
$html_text = $alipaySubmit->buildRequestForm($parameter,"get", "确认");
echo $html_text;
?>