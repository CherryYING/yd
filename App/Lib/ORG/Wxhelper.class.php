<?php
/*
*	微信帮助类
*/
class Wxhelper{
	
	public $appId;
	public $appSecret;
	public $access_token;
	
	public function __construct($option){
		$this->appId=$option['appid'];
		$this->appSecret=$option['appsecret'];
		$this->access_token=$this->get_access_token();
	}
	
	/*	功能：获取用户信息
	*	@param string $openid
	*/
	/*
		Array
		(
			[subscribe] => 1
			[openid] => oGRGsuPd1v5e4OBPuJhksjRCqr4c
			[nickname] => shaobo
			[sex] => 1
			[language] => zh_CN
			[city] => 西安
			[province] => 陕西
			[country] => 中国
			[headimgurl] => http://wx.qlogo.cn/mmopen/5icDxicP6svot755ovEicxsAMibKkY4dib10VEIq5ja93sh7CL8ICQO55OAYSicOxJfIc76P1M4ypl7mEN8PkCQ0icsZg/0
			[subscribe_time] => 1415782321
			[remark] => 
		)
	*/
	public function get_user_info($openid){
		
$url="https://api.weixin.qq.com/cgi-bin/user/info?access_token={$this->access_token}&openid={$openid}&lang=zh_CN";		
		$return=$this->httpGet($url);
		
		
		
		return json_decode($return,true);
	}
	
	/*	功能：获取微信粉丝列表【openid列表】
	*	@param string next_openid:第一个拉取的OPENID，不填默认从头开始拉取，一次最多拉取10000条
	*	错误返回：{"errcode":40013,"errmsg":"invalid appid"} 
	*/
	public function get_wxfans($next_openid=''){
		
		$url="https://api.weixin.qq.com/cgi-bin/user/get?access_token={$this->access_token}&next_openid={$next_openid}";
		$return=$this->httpGet($url);
		return json_decode($return,true);
	}
	
	/*	功能：获取用户增减数据
	*	@param json $date_range={ "begin_date": "2014-12-02", "end_date": "2014-12-07"}
	*	end_date允许设置的最大值为昨日
	*/
	public function getusersummary($date_range){
		$url="https://api.weixin.qq.com/datacube/getusersummary?access_token={$this->access_token}";
		$return=$this->httpPost($url,$date_range);
		return json_decode($return,true);
	}
	
	/*	功能：获取累计用户数据
	*	@param json $date_range={ "begin_date": "2014-12-02", "end_date": "2014-12-07"}
	*	end_date允许设置的最大值为昨日
	*/
	public function getusercumulate($date_range){
		$url="https://api.weixin.qq.com/datacube/getusercumulate?access_token={$this->access_token}";	
		$return=$this->httpPost($url,$date_range);
		return json_decode($return,true);
	}
	
	
	
	/*	功能：获取微信服务器IP
	*	
	*/
	public function get_wx_ip(){
		$url="https://api.weixin.qq.com/cgi-bin/getcallbackip?access_token={$this->access_token}";
		$return=$this->httpGet($url);
		
		
		
		return json_decode($return,true);
	}
	/*
	*	功能：获取access_token
	*/
	private function get_access_token(){
	  /*$url = "https://api.weixin.qq.com/cgi-bin/token?grant_type=client_credential&appid={$this->appId}&secret={$this->appSecret}";
      $res = json_decode($this->httpGet($url),true);
      $access_token = $res['access_token'];
	  return $access_token;
	  */
	   // access_token 应该全局存储与更新，以下代码以写入到文件中做示例
		$data = json_decode(file_get_contents("Data/wxcache/access_token.json"));
		if ($data->expire_time < time()) {
		  // 如果是企业号用以下URL获取access_token
		  // $url = "https://qyapi.weixin.qq.com/cgi-bin/gettoken?corpid=$this->appId&corpsecret=$this->appSecret";
		  $url = "https://api.weixin.qq.com/cgi-bin/token?grant_type=client_credential&appid={$this->appId}&secret={$this->appSecret}";
		  $res = json_decode($this->httpGet($url));
		  $access_token = $res->access_token;
		  if ($access_token) {
			$data->expire_time = time() + 7000;
			$data->access_token = $access_token;
			$fp = fopen("Data/wxcache/access_token.json", "w");
			fwrite($fp, json_encode($data));
			fclose($fp);
		  }
		} else {
		  $access_token = $data->access_token;
		}
		return $access_token;
	}

	/*
	*	功能：curl发送方get请求
	*	@param string $url
	*/
	private function httpGet($url) {
		$curl = curl_init();
		curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($curl, CURLOPT_TIMEOUT, 500);
		curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
		curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, false);
		curl_setopt($curl, CURLOPT_URL, $url);
	
		$res = curl_exec($curl);
		curl_close($curl);
	
		$return=json_decode($res);
		//如果返回值$res->errcode>0说明access_token非正常失效；删除老的access_token；重新获取access_token
		if(isset($return->{'errcode'})&&$return->{'errcode'}==40001){		
			unlink('Data/wxcache/access_token.json');
			$this->get_access_token();
		}
	
		return $res;
	 }
	  
	 /*
	*	功能：curl发送方post请求
	*	@param string $url
	*	@param string|json|array|xml $data
	*/
	private function httpPost($url,$data) {
		$curl = curl_init();
		//post提交方式
		curl_setopt($curl, CURLOPT_URL, $url);
		curl_setopt($curl, CURLOPT_POST, TRUE);
		curl_setopt($curl, CURLOPT_CUSTOMREQUEST, "POST");
		curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, FALSE);
		curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, FALSE);
		curl_setopt($curl, CURLOPT_POSTFIELDS, $data);
		
		curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);	//要求结果为字符串且输出到屏幕上
		curl_setopt($curl, CURLOPT_TIMEOUT, 500);
		
		$res = curl_exec($curl);	
		curl_close($curl);
		
		$return=json_decode($res);
		//如果返回值$res->errcode>0说明access_token非正常失效；删除老的access_token；重新获取access_token
		if(isset($return->{'errcode'})&&$return->{'errcode'}==40001){		
			unlink('Data/wxcache/access_token.json');
			$this->get_access_token();
		}
		
		return $res;
	}
	
	function httpPost_($url, $data){
		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL, $url);
		curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");
		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
		curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, FALSE);
		curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/5.0 (compatible; MSIE 5.01; Windows NT 5.0)');
		curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
		curl_setopt($ch, CURLOPT_AUTOREFERER, 1);
		curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		$res = curl_exec($ch);	
		curl_close($ch);
		return $res;
	}
	//====================模板消息接口===开始====================//
	/*
	*	功能：设置所属行业	
	*	@param array $data
	*	{"industry_id1":"1", "industry_id2":"4"}
	*	@return array		
	*/
	public function api_set_industry($data){
		$data=json_encode($data);
		$url="https://api.weixin.qq.com/cgi-bin/template/api_set_industry?access_token={$this->access_token}";
		$return=$this->httpPost($url,$data);	  
		return json_decode($return,true);
	} 
	/*
	*	功能：获得模板ID	
	*	@param array $data
	*	{"template_id_short":"TM00015"}
	*	@return array		
	*/
	public function api_add_template($data){
		$data=json_encode($data);
		$url="https://api.weixin.qq.com/cgi-bin/template/api_add_template?access_token={$this->access_token}";
		$return=$this->httpPost($url,$data);	  
		return json_decode($return,true);
	}
	/*
	*	功能：发送模板消息	
	*	@param array $data
	*	{
           "touser":"OPENID",
           "template_id":"ngqIpbwh8bUfcSsECmogfXcV14J0tQlEpBO27izEYtY",
           "url":"http://weixin.qq.com/download",
           "topcolor":"#FF0000",
           "data":{
                   "first": {
                       "value":"恭喜你购买成功！",
                       "color":"#173177"
                   },
                   "keynote1":{
                       "value":"巧克力",
                       "color":"#173177"
                   },
                   "keynote2": {
                       "value":"39.8元",
                       "color":"#173177"
                   },
                   "keynote3": {
                       "value":"2014年9月16日",
                       "color":"#173177"
                   },
                   "remark":{
                       "value":"欢迎再次购买！",
                       "color":"#173177"
                   }
           }
       }

	*	@return array		
	*/
	public function send_tpl_msg($data){
		$data=json_encode($data);
		$url="https://api.weixin.qq.com/cgi-bin/message/template/send?access_token={$this->access_token}";
		$return=$this->httpPost($url,$data);	  
		return json_decode($return,true);
	}
	//====================模板消息接口===结束====================//
	//=====================微信用户组管理=======================//
	/*
		创建用户分组
		@param json {"group":{"name":"test"}}
		@return 
		 error:		{"errcode":40013,"errmsg":"invalid appid"}
		 success:	{"group": {"id": 107,"name": "test"}}
	*/
	public function create_group($name){
		$url="https://api.weixin.qq.com/cgi-bin/groups/create?access_token={$this->access_token}";	
		$json='{"group":{"name":"'.$name.'"}}';
		$return=$this->httpPost($url,$json);
		return json_decode($return,true);
	}
	/*
		获取用户分组列表
	*/
	public function list_group(){
		$url="https://api.weixin.qq.com/cgi-bin/groups/get?access_token={$this->access_token}";	
		$return=$this->httpGet($url);
		return json_decode($return,true);
	}
	/*
		修改用户分组名称
		@params json {"group":{"id":108,"name":"test2_modify2"}}
	*/
	public function update_group($id,$name){
		$url="https://api.weixin.qq.com/cgi-bin/groups/update?access_token={$this->access_token}";	
		$json='{"group":{"id":'.$id.',"name":"'.$name.'"}}';
		$return=$this->httpPost($url,$json);
		return json_decode($return,true);
	}
	
	/*
		查询用户分组
		@params json {"openid":"od8XIjsmk6QdVTETa9jLtGWA6KBc"}
	*/
	public function get_user_group($json){
		$url="https://api.weixin.qq.com/cgi-bin/groups/getid?access_token={$this->access_token}";	
		$return=$this->httpPost($url,$json);
		return json_decode($return,true);
	}
	
	/*
		移动用户分组
		@params json {"openid":"oDF3iYx0ro3_7jD4HFRDfrjdCM58","to_groupid":108}
		@return json {"errcode": 0, "errmsg": "ok"}
	*/
	public function change_user_group($openid,$group_id){
		$url="https://api.weixin.qq.com/cgi-bin/groups/members/update?access_token={$this->access_token}";	
		$json='{"openid":"'.$openid.'","to_groupid":'.$group_id.'}';
		$return=$this->httpPost($url,$json);
		//return json_decode($return,true);
	}
	//======================================带参二维码==================================//
	/*
		生成永久二维码
		@method post
		@params json {"action_name": "QR_LIMIT_SCENE", "action_info": {"scene": {"scene_id": 123}}}
					 {"action_name": "QR_LIMIT_STR_SCENE", "action_info": {"scene": {"scene_str": "123"}}} 		
		@return json 		{"ticket":"gQH47joAAAAAAAAAASxodHRwOi8vd2VpeGluLnFxLmNvbS9xL2taZ2Z3TVRtNzJXV1Brb3ZhYmJJAAIEZ23sUwMEmm3sUw==","expire_seconds":60,"url":"http:\/\/weixin.qq.com\/q\/kZgfwMTm72WWPkovabbI"}
	*/
	public function qrcode($groupid){
		$data=array('action_name'=>'QR_LIMIT_STR_SCENE','action_info'=>array('scene'=>array('scene_str'=>$groupid)));
		$json=json_encode($data);
		$url="https://api.weixin.qq.com/cgi-bin/qrcode/create?access_token={$this->access_token}";	
		$return=$this->httpPost($url,$json);
		return json_decode($return,true);
	}
	/*
		用ticket换取二维码
		@提醒：TICKET记得进行UrlEncode
		@return 二维码地址url
	*/
	public function showqrcode($ticket){
		$url="https://mp.weixin.qq.com/cgi-bin/showqrcode?ticket={$ticket}";
		return $url;
	}
	
	/*
		上传多媒体文件
		type:分别有图片（image）、语音（voice）、视频（video）和缩略图（thumb）
	*/
	public function upload_media($type,$media){
		$url="https://api.weixin.qq.com/cgi-bin/media/upload?access_token={$this->access_token}&type=$type";
		$return=$this->httpPost($url,$data=array('file1'=>"@".$media));
		return $return;
	}
}
?>