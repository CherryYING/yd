<?php
/**
 * 通用通知接口
 * ====================================================
 * 支付完成后，微信会把相关支付和用户信息发送到商户设定的通知URL，
 * 商户接收回调信息后，根据需要设定相应的处理流程。
 * 
 * 这里举例使用log文件形式记录回调信息。
*/
	include_once("log_.php");
	include_once("WxPayPubHelper/WxPayPubHelper.php");
    include_once("class/db.class.php");

    //使用通用通知接口
	$notify = new Notify_pub();

	//存储微信的回调
	$xml = $GLOBALS['HTTP_RAW_POST_DATA'];	

	$notify->saveData($xml);
	
	//验证签名，并回应微信。
	//对后台通知交互时，如果微信收到商户的应答不是成功或超时，微信认为通知失败，
	//微信会通过一定的策略（如30分钟共8次）定期重新发起通知，
	//尽可能提高通知的成功率，但微信不保证通知最终能成功。
	if($notify->checkSign() == FALSE){
		$notify->setReturnParameter("return_code","FAIL");//返回状态码
		$notify->setReturnParameter("return_msg","签名失败");//返回信息
	}else{
		$notify->setReturnParameter("return_code","SUCCESS");//设置返回码
	}
	$returnXml = $notify->returnXml();
	echo $returnXml;
	
	//==商户根据实际情况设置相应的处理流程==//
	
	 //====================================更新订单状态==================================//
	$obj=(array)simplexml_load_string($xml,'SimpleXMLElement', LIBXML_NOCDATA);
	$result_code=$obj['result_code'];       //成功 SUCCESS
	$return_code=$obj['return_code'];       //成功 SUCCESS
	$out_trade_no=substr($obj['out_trade_no'],0,-4);    	//订单号	substr($obj['out_trade_no'],0,-4);
	//$out_trade_no=$obj['out_trade_no'];
	$trade_no=$obj['out_trade_no'];			//提交给微信的单号	
	$total_fee=($obj['total_fee'])*0.01;    //支付金额
	$openid=$obj['openid'];					//支付者微信openid
	$timestamp=time();
	$db=new Connection();               //创建数据库链接
	$sql="update tp_agent_order set out_trade_no='$trade_no',pay_status=1,pay_money=$total_fee,pay_time=$timestamp where out_trade_no='$out_trade_no'";
	
	$db->query($sql);
	
	
	$order_query=$db->query("select id from tp_agent_order where out_trade_no='$trade_no'");
	$order=$db->get_one($order_query);
	
	// 初始化一个 cURL 对象
	$curl = curl_init();
	// 设置你需要抓取的URL
	$url='http://'.$_SERVER['HTTP_HOST'].'/index.php?g=Weixin&m=Ajax&a=agent_notify_url&order_id='.$order['id'];
	curl_setopt($curl, CURLOPT_URL, $url);
	// 设置header
	curl_setopt($curl, CURLOPT_HEADER, 1);
	// 设置cURL 参数，要求结果保存到字符串中还是输出到屏幕上。
	curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
	// 运行cURL，请求网页
	$data = curl_exec($curl);
	// 关闭URL请求
	curl_close($curl);
	
	
	/*if($result_code=='SUCCESS'&&$return_code=='SUCCESS'){
		$db=new Connection();               //创建数据库链接
		$db->query("update wx_order set is_pay=1,pay_time=$timestamp where code=$out_trade_no");
	}*/
	//====================================更新订单状态==================================//

	//以log文件形式记录回调信息
	$log_ = new Log_();
	$log_name="./notify_url.log";//log文件路径
	$log_->log_result($log_name,"【接收到的notify通知】:\n".$xml."\n");

	if($notify->checkSign() == TRUE)
	{
		if ($notify->data["return_code"] == "FAIL") {
			//此处应该更新一下订单状态，商户自行增删操作
			$log_->log_result($log_name,"【通信出错】:\n".$xml."\n");
		}
		elseif($notify->data["result_code"] == "FAIL"){
			//此处应该更新一下订单状态，商户自行增删操作
			$log_->log_result($log_name,"【业务出错】:\n".$xml."\n");
		}
		else{
			//此处应该更新一下订单状态，商户自行增删操作
			$log_->log_result($log_name,"【支付成功】:\n".$xml."\n");
		}
		
		//商户自行增加处理流程,
		//例如：更新订单状态
		//例如：数据库操作
		//例如：推送支付完成信息
	}
?>