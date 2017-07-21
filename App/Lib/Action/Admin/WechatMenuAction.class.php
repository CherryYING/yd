<?php 
class WechatMenuAction extends PublicAction{
		private $db;
		private $pubwechat;
		public function _initialize(){
			parent::_initialize();
			$this->db = M('wechat_menu');
			$this->pubwechat=M('wechat_config')->find(1);	//公众号信息
		}

		public function index(){
			$plist=$this->db->field('id,fup,type,name,key,list')->order('list asc')->select();
			//对菜单按子级顺序重排
			$plist=sarr($plist,0);
			$this->assign('plist',$plist);
			$this->display();    
		}
		//菜单删除
		public function menudel(){
			$this->db->delete(I('get.id'));
			redirect(U('index'));
		}
		//菜单添加修改
		public function menuadd(){
			//获取网站栏目
			$sortlist=M('cms_sort')->where(array('type'=>1))->select();
			$this->assign('sortlist',$sortlist);
			if(isset($_GET['id'])){
                                $menuInfo=M('wechat_menu')->where(array('id'=>I('get.id')))->find();
				$nrs=$this->db->find(I('get.id'));
				$nrs['conf']=M('wechat_replyconf')->where(array('_logic'=>'OR','mid'=>I('get.id'),'menukey'=>$menuInfo['key']))->getField('conf');
				$nrs['type']==1||$nrs['conf']=unserialize($nrs['conf']);
				$this->assign('nrs',$nrs);
			}
			$sortd=$this->db->where('fup=0')->select();
			$this->assign('sortd',$sortd);
			if($tid=I('get.tid')){
				$this->assign('news',$nrs['conf'][$tid-1]);
				 $this->display('newsEdit');
			}else{
				$this->display();
			}
		}
		//菜单添加修改句柄
		public function menuaddhandle(){
			$data=$_POST;
			!$_GET['id']?$this->db->add($data):$this->db->where(array('id'=>I('get.id')))->save($data);
			//redirect(U('index'));
            $this->success("操作成功！",U('index'));
		}
		public function menucreate(){
			$plist=$this->db->select();
			// var_dump(json_encode($plist));
		}
		public function menuget(){
			$access_token=self::get_access_token();
			$url='https://api.weixin.qq.com/cgi-bin/menu/get?access_token='.$access_token;//$json->access_token;
			$jsonmenu=$this->curlGet($url);
			echo $jsonmenu.'<hr>'.$access_token;
		}

		//为菜单增加图文动作回复
		public function menuaddrep(){
            header("content-type:text/html;charset=utf-8");
			if(array_filter($_FILES['spic']['name'])){
				$picurl=$this->getpic('spic');
			}else{
                            $picurl=I('post.picurl');
                        }
			$id=I('get.id');
                        $tid=I('post.tid');
			$title=I('post.title');
			$descrip=I('post.descrip');
			$url=I('post.url');
			$data['conf']=array(array('Title'=>$title,'Description'=>$descrip,'PicUrl'=>$picurl,'Url'=>$url));
			$arr=M('wechat_replyconf')->where('mid='.$id)->getField('conf');
                        
			if($arr && $arr<>''){
				$oldarr=unserialize($arr);
                                if(empty($tid)){    //新增
                                     $data['conf']=array_merge($oldarr,$data['conf']);
                                }else{  //编辑
                                    $oldarr[$tid-1]=$data['conf'][0];
                                    $data['conf']=$oldarr;
                                }
				$data['conf']=serialize($data['conf']);
				M('wechat_replyconf')->where('mid='.$id)->save($data);
			}else{
				$data['conf']=serialize($data['conf']);
				$data['mid']=$id;
				$data['type']=0;
				$data['menukey'] = $this->db->where('id='.$id)->getField('key');
				$data['textkey'] = $this->db->where('id='.$id)->getField('word');
				M('wechat_replyconf')->add($data);
			}
			
			redirect(U('menuadd',array('id'=>$id)));
			
		}
		//删除某个指定的图文信息
		public function menudelrep(){
			$id=I('get.id');
			$tid=I('get.tid')-1;
			$arr=unserialize(M('wechat_replyconf')->where('mid='.$id)->getField('conf'));
			unset($arr[$tid]);
			$data['conf']=serialize(array_values($arr));
			M('wechat_replyconf')->where('mid='.$id)->save($data);
			redirect(U('menuadd',array('id'=>$id)));
		}

		//调用微信api创建公众号自定义菜单
		public function menusend(){
			$access_token=self::get_access_token();
			$url='https://api.weixin.qq.com/cgi-bin/menu/create?access_token='.$access_token;//$json->access_token;

			$mlist=$this->db->field('id,fup,type,name,key,list')->select();

			//列出一级菜单并排序
			$muplist=array_values(array_sort(seekarr($mlist,'fup',0),'list','asc'));

			$data='{
					"button":[';
			foreach ($muplist as $k => $val) {
				$k==0||$data.=',';
				//列出二级菜单并排序
				$mson=array_values(array_sort(seekarr($mlist,'fup',$val['id']),'list','desc'));
				if(array_filter($mson)){
					$data.='{
								"name":"'.$val['name'].'",
								"sub_button":[';
					foreach ($mson as $key => $value) {
						$value['type']==0?$type='click':$type='view';
						$value['type']==0?$but='key':$but='url';
						$key==0||$data.=',';
						$data.='{
									"type":"'.$type.'",
									"name":"'.$value['name'].'",
									"'.$but.'":"'.$value['key'].'"
								}';
					}

					$data.=']}';
				}else{
					$val['type']==0?$type='click':$type='view';
					$val['type']==0?$but='key':$but='url';

					$data.='{
								"type":"'.$type.'",
						        "name":"'.$val['name'].'",
						        "'.$but.'":"'.$val['key'].'"
							}';
				}
			}
			$data.=']}';
			$return=$this->api_notice_increment($url,$data);
			if($return['errcode']==0&&$return['errmsg']=='ok'){
				echo '菜单创建成功！'.$data;
			}else{
				echo '菜单创建失败！'.$return['errmsg'];
			}
			// $this->display();
		}

	function  api_notice_increment($url, $data){
		$ch = curl_init();
		$header = "Accept-Charset: utf-8";
		curl_setopt($ch, CURLOPT_URL, $url);
		curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");
		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
		curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, FALSE);
		curl_setopt($curl, CURLOPT_HTTPHEADER, $header);
		curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/5.0 (compatible; MSIE 5.01; Windows NT 5.0)');
		curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
		curl_setopt($ch, CURLOPT_AUTOREFERER, 1);
		curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		$res = curl_exec($ch);
		return json_decode($res,true);
	}
	function curlGet($url){
		$ch = curl_init();
		$header = "Accept-Charset: utf-8";
		curl_setopt($ch, CURLOPT_URL, $url);
		curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "GET");
		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
		curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, FALSE);
		curl_setopt($curl, CURLOPT_HTTPHEADER, $header);
		curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/5.0 (compatible; MSIE 5.01; Windows NT 5.0)');
		curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
		curl_setopt($ch, CURLOPT_AUTOREFERER, 1);
		curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		$temp = curl_exec($ch);
		return $temp;
	}	
	private function getpic($input){	
		$savePath = "./Data/upload";
        // tcmkdir($savePath);
        $fileFormat = array('gif','jpg','jpeg','png','bmp');
        $maxSize = 0;
        $overwrite = 0;
		$thumb=1;
		$thumbWidth = 200;
		$thumbHeight = 200;	
		import('@.ORG.clsUpload');
		$picmodel=new clsUpload($savePath,$fileFormat,$maxSize,$overwrite);		
		$picmodel->setThumb($thumb,$thumbWidth,$thumbHeight);
         if (!$picmodel->run($input,1)){
             echo $picmodel->errmsg()."<br>\n";
         }
		$pic = $picmodel->getInfo();
     return "/Data/upload/".$pic[0]["saveName"];
	}	
	
	/*
	*	功能：获取access_token
	*/
	private function get_access_token(){
		$appId=$this->pubwechat['appid'];
		$appSecret=$this->pubwechat['appsecret'];
	   // access_token 应该全局存储与更新，以下代码以写入到文件中做示例
		$data = json_decode(file_get_contents("Data/wxcache/access_token.json"));
		if ($data->expire_time < time()) {
		  $url = "https://api.weixin.qq.com/cgi-bin/token?grant_type=client_credential&appid={$appId}&secret={$appSecret}";
		  $res = json_decode($this->curlGet($url));
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

}