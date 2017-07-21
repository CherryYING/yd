<?php
/*
*	微信红包接口类
*/
class WxRedPack{

	private $app_id;		//【公众账号】appid	
	private $app_secret;	//【公众账号】appsecret
	private $mch_id;		//【微信支付】商户号
	private $partnerkey;	//【微信支付】秘钥
	
	public function __construct($option){
		$this->app_id=isset($option['appid'])?$option['appid']:'';//'wxe7e5a985ba3e3b17';//
		$this->app_secret=$option['appsecret'];//'a41db85681dc8343097916ef94a9fa93';//
		$this->mch_id=$option['mchid'];//'10011481';//
		$this->partnerkey=$option['partnerkey'];//'f4080e95335dc37ffac4c901b7d6dc11';//
	}
	
	/*	请求参数
		<xml>
            <sign></sign>						//签名
            <mch_billno></mch_billno>			//商户订单号（每个订单号必须唯一）
            <mch_id></mch_id>					//微信支付分配的商户号
            <wxappid></wxappid>					//商户appid
            <nick_name></nick_name>				//提供方名称
            <send_name></send_name>				//红包发送者名称
            <re_openid></re_openid>				//接受收红包的用户,用户在wxappid下的openid
            <total_amount></total_amount>		//付款金额，单位分
            <min_value></min_value>				//最小红包金额，单位分
            <max_value></max_value>				//最大红包金额，单位分
            <total_num></total_num>				//红包发放总人数
            <wishing></wishing>					//红包祝福语
            <client_ip></client_ip>				//调用接口的机器Ip地址
            <act_name></act_name>				//活动名称
            <act_id></act_id>					//
            <remark></remark>					//备注信息
            <logo_imgurl></logo_imgurl>			//商户logo的url
            <share_content></share_content>		//分享文案
            <share_url></share_url>				//分享链接
            <share_imgurl></share_imgurl>		//分享的图片url
            <nonce_str></nonce_str>				//随机字符串，不长于32位
        </xml>
	
	*/
	/*	成功返回
		<xml>
			<return_code><![CDATA[SUCCESS]]></return_code>
			<return_msg><![CDATA[发放成功.]]></return_msg>
			<result_code><![CDATA[SUCCESS]]></result_code>
			<err_code><![CDATA[0]]></err_code>
			<err_code_des><![CDATA[发放成功.]]></err_code_des>
			<mch_billno><![CDATA[0010010404201411170000046545]]></mch_billno>
			<mch_id>10010404</mch_id>
			<wxappid><![CDATA[wx6fa7e3bab7e15415]]></wxappid>
			<re_openid><![CDATA[onqOjjmM1tad-3ROpncN-yUfa6uI]]></re_openid>
			<total_amount>1</total_amount>
		</xml>
	*/
	/*	失败返回
		<xml> 
		 <return_code><![CDATA[FAIL]]></return_code> 
		 <return_msg><![CDATA[系统繁忙,请稍后再试.]]></return_msg> 
		 <result_code><![CDATA[FAIL]]></result_code> 
		 <err_code><![CDATA[268458547]]></err_code> 
		 <err_code_des><![CDATA[系统繁忙,请稍后再试.]]></err_code_des> 
		 <mch_billno><![CDATA[0010010404201411170000046542]]></mch_billno> 
		 <mch_id>10010404</mch_id> 
		 <wxappid><![CDATA[wx6fa7e3bab7e15415]]></wxappid> 
		 <re_openid><![CDATA[onqOjjmM1tad-3ROpncN-yUfa6uI]]></re_openid> 
		 <total_amount>1</total_amount> 
		</xml>
	*/
	
   /**
	* 发送红包
	* @param array $post_arr
	*/
	public function sendRedPack($post_arr){
		$url="https://api.mch.weixin.qq.com/mmpaymkttransfers/sendredpack";
		$xml=$this->arrayToXml($post_arr);
		$return_xml=$this->postXmlCurl($xml,$url);
		$return_arr=$this->xmlToArray($return_xml);
		return $return_arr;
	}
	
	//================================工具函数====开始=====================================//
	/**
	 * 	功能：产生随机字符串，不长于32位
	 */
	public function createNoncestr( $length = 32 ) 
	{
		$chars = "abcdefghijklmnopqrstuvwxyz0123456789";  
		$str ="";
		for ( $i = 0; $i < $length; $i++ )  {  
			$str.= substr($chars, mt_rand(0, strlen($chars)-1), 1);  
		}  
		return $str;
	}
	/**
	 * 	功能：格式化参数，签名过程需要使用
	 *	@param array $paraMap
	 *	@param bool $urlencode
	 */
	function formatQueryPara($paraMap, $urlencode)
	{
		$buff = "";
		ksort($paraMap);
		foreach ($paraMap as $k => $v)
		{
		    if($urlencode)
		    {
			   $v = urlencode($v);
			}
			//$buff .= strtolower($k) . "=" . $v . "&";
			$buff .= $k . "=" . $v . "&";
		}
		$reqPar;
		if (strlen($buff) > 0) 
		{
			$reqPar = substr($buff, 0, strlen($buff)-1);
		}
		return $reqPar;
	}
	/**
	 * 	功能：生成签名
	 *	@param array $Obj
	 */
	public function getSign($Obj)
	{
		foreach ($Obj as $k => $v)
		{
			$Parameters[$k] = $v;
		}
		//签名步骤一：按字典序排序参数
		ksort($Parameters);
		$String = $this->formatQueryPara($Parameters, false);
		//echo '【string1】'.$String.'</br>';
		//签名步骤二：在string后加入KEY
		$String = $String."&key=".$this->partnerkey;			//微信支付key
		//echo "【string2】".$String."</br>";
		//签名步骤三：MD5加密
		$String = md5($String);
		//echo "【string3】 ".$String."</br>";
		//签名步骤四：所有字符转为大写
		$result_ = strtoupper($String);
		//echo "【result】 ".$result_."</br>";
		return $result_;
	}

	
	/**
	 * 	功能：array转xml
	 *	@param array $arr
	 */
	function arrayToXml($arr)
    {
        $xml = "<xml>";
        foreach ($arr as $key=>$val)
        {
        	 if (is_numeric($val))
        	 {
        	 	$xml.="<".$key.">".$val."</".$key.">"; 

        	 }
        	 else
        	 	$xml.="<".$key."><![CDATA[".$val."]]></".$key.">";  
        }
        $xml.="</xml>";
        return $xml; 
    }
	
	/**
	 * 	功能：将xml转为array
	 *	@param xml $xml
	 */
	public function xmlToArray($xml)
	{		
        //将XML转为array        
        $array_data = json_decode(json_encode(simplexml_load_string($xml, 'SimpleXMLElement', LIBXML_NOCDATA)), true);		
		return $array_data;
	}
	/**
	 * 	功能：以post方式提交xml到对应的接口url
	 *  @param xml $xml
	 *	@param string $url
	 *	@param int $second
	 */
	public function postXmlCurl($xml,$url,$second=30)
	{		
        //初始化curl        
       	$ch = curl_init();
		//设置超时
		curl_setopt($ch, CURLOP_TIMEOUT, $second);
        //这里设置代理，如果有的话
        //curl_setopt($ch,CURLOPT_PROXY, '8.8.8.8');
        //curl_setopt($ch,CURLOPT_PROXYPORT, 8080);
        curl_setopt($ch,CURLOPT_URL, $url);
        curl_setopt($ch,CURLOPT_SSL_VERIFYPEER,FALSE);
        curl_setopt($ch,CURLOPT_SSL_VERIFYHOST,FALSE);
		//设置header
		curl_setopt($ch, CURLOPT_HEADER, FALSE);
		//要求结果为字符串且输出到屏幕上
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
		
		//发送证书请求【证书路径,注意应该填写绝对路径】
		
		curl_setopt($ch,CURLOPT_SSLCERT,getcwd()."/wxpay/cert/apiclient_cert.pem");	
		curl_setopt($ch,CURLOPT_SSLKEY,getcwd()."/wxpay/cert/apiclient_key.pem");	
        curl_setopt($ch,CURLOPT_CAINFO,getcwd()."/wxpay/cert/rootca.pem");	
		
		//post提交方式
		curl_setopt($ch, CURLOPT_POST, TRUE);
		curl_setopt($ch, CURLOPT_POSTFIELDS, $xml);
		//运行curl
        $data = curl_exec($ch);
		curl_close($ch);
		//返回结果
		if($data)
		{
			curl_close($ch);
			return $data;
		}
		else 
		{ 
			$error = curl_errno($ch);
			echo "curl出错，错误码:$error"."<br>"; 
			echo "<a href='http://curl.haxx.se/libcurl/c/libcurl-errors.html'>错误原因查询</a></br>";
			curl_close($ch);
			return false;
		}
	}
	//================================工具函数===结束======================================//
}
?>