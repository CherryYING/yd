<?php
class IndexAction extends Action {
    private $weObj;
    private $token;
    private $wechatid;
    private $pwechat;
    private $content;
    private $event;
    private $type;
    private $db;
    private $URL;
    private $helper;
    public function _initialize(){
    	import("@.ORG.Wchat");
        import("@.ORG.Wxhelper");
    	$this->URL = 'http://'.I('server.HTTP_HOST');
    	$this->token = I('get.token');
    	$options=array(
			// 'debug'=>true,
			'token'=>$this->token
		);
        $this->weObj = new Wchat($options);
		
        $this->type = $this->weObj->getRev()->getRevType();
        $this->wechatid = $this->weObj->getRevFrom();
        $this->pwechat = $this->weObj->getRevTo();
        $this->event = $this->weObj->getRevEvent();
        $this->content = $this->weObj->getRevContent();
		
        $this->db = M('wechat_replyconf');
		//$reqdata=file_get_contents("php://input");
		
		$this->pubwechat=M('wechat_config')->find(1);	//公众号信息
		$this->helper=new Wxhelper($this->pubwechat);
		
    }
    public function index() {
        switch($this->type) {
            case Wchat::MSGTYPE_TEXT:
				//转发到客服
				//$this->weObj->transmitService()->reply();
				$this->sendReply(1,$this->content);
				//$this->weObj->text("help info")->reply();
                exit;
                break;
            case Wchat::MSGTYPE_EVENT:
                $ev=$this->event;
                if($ev['event']=='CLICK'){	//菜单事件
                    switch ($ev['key']){
						case 'QRCARD':
							$this->send_qrcard();
							//$this->weObj->text("二维码正在生成中...")->reply();
						break;
                        default :
                            $this->sendReply(0,$ev['key']);
                        break;
                    }
                        
                }elseif($ev['event']=='subscribe'){	//关注事件
				
						 $udata=array(
                            'wechatid'=>$this->wechatid,
                            'pwechat'=>$this->pwechat,
                            'token'=>$this->token,
                            'state'=>0,
							'subscribe'=>1,		//已关注
							'subscribe_time'=>time(),
                            'posttime'=>time()
                        );
						$wx_info=$this->helper->get_user_info($this->wechatid);

						$udata['nickname']=$wx_info['nickname'];
						$udata['province']=$wx_info['province'];
						$udata['city']=$wx_info['nickname'];
						$udata['sex']=$wx_info['sex'];
						$udata['headimgurl']=$wx_info['headimgurl'];
						$udata['unionid']=$wx_info['unionid'];		
						if($ev['key']){
							$parent_id=str_replace('qrscene_','',$ev['key']);
							
							//推荐人信息
							$fup=M('wechat_user')->where(array('id'=>$parent_id))->find();
							$udata['p_1']=$parent_id;
							$udata['p_2']=$fup['p_1']>0?$fup['p_1']:0;
							$udata['p_3']=$fup['p_2']>0?$fup['p_2']:0;
							$udata['p_4']=$fup['p_3']>0?$fup['p_3']:0;				//上4级
							
							$udata['tid']=$fup['tid']>0?$fup['tid']:0;			//最顶级推广人
							
							//移动用户分组
							//$this->helper->change_user_group($this->wechatid,$group_id);
						}
						
						$uid=$this->reg($udata);
						
						
						//如果有上级用户，模板通知上级用户
						$user=M('wechat_user')->where(array('wechatid'=>$this->wechatid))->find();
						if(!empty($user)&&$user['p_1']>0){			//存在上级用户
							subscribe_notice($user['id']);
							//上级用户返积分
							return_jifen(1,'reg_tui',$user['p_1']);
						}
						
                        $this->sendReply(0,'subscribe');
						
						
                }elseif($ev['event']=='unsubscribe'){	//取消关注事件

                }elseif($ev['event']=='SCAN'){		//已关注用户扫面带参二维码
				
					$this->weObj->text("您已经关注过我们了，快将您的二维码分享给好友，让更多的小伙伴加入我们吧！！！/::)")->reply();		
				}
                exit;
                break;
            case Wchat::MSGTYPE_IMAGE:
                $this->weObj->text("你发的是什么图片？")->reply();
                exit;
                break;
            default:
                $this->weObj->text("help info")->reply();
                exit;
                break;
        }
    }

    private function sendReply($evtype,$key){
    	if($evtype==0){     //菜单事件
            $w['menukey']=$key;
    	}else{      //关键字[模糊查询]
            $w['textkey']=array('like',"%$key%");
    	}
    	$menu=$this->db->where($w)->find();
        //后台没有设置相关回复信息
        if(empty($menu)){       //交给机器人处理
            $question=$key;
			
			$return=$this->chat_config($this->content);
			if(!empty($return)){
				$this->weObj->text($return)->reply();
			}else{
				echo 'success';
				//$output=$this->freeChat($question);
				//$this->createReply(1,$output); 
			}
        }else{
            $this->createReply($menu['type'],$menu['conf']); 
        }
    }
    private function createReply($type,$conf){
        //news
    	if($type==0){
            $menu = unserialize($conf);
            foreach ($menu as $key => $value) {
                    $menu[$key]['PicUrl'] = $this->URL.$menu[$key]['PicUrl'];
					$menu[$key]['Url']=htmlspecialchars_decode($menu[$key]['Url']);
					if(substr($menu[$key]['Url'],0,4)!='http'){
						 $menu[$key]['Url'] = $this->URL.$menu[$key]['Url'];
					}
					if(!strpos($menu[$key]['Url'],'?')){
						$menu[$key]['Url'].='?wxid='.$this->wechatid;
					}else{
						$menu[$key]['Url'].='&wxid='.$this->wechatid;
					}
					
            }
            $this->weObj->news($menu)->reply();
        //text    
    	}else{  
			//如果有上级用户，模板通知上级用户
			/*$user=M('wechat_user')->where(array('wechatid'=>$this->wechatid))->find();
			if(!empty($user)&&$user['p_1']>0){			//存在上级用户
				subscribe_notice($user['id']);
			}*/
		
			//微信信息
			//$wx_info=$this->helper->get_user_info($this->wechatid);
			//<a href="'.$this->URL.'/index.php?g=Weixin&m=Ucenter&a=index">'.$wx_info['nickname'].'</a>
			//$conf=str_replace('{nickname}',$wx_info['nickname'],$conf);
			//本地用户信息
			$info=M('wechat_user')->where(array('wechatid'=>$this->wechatid))->find();
			//$conf=str_replace('{id}',intval($info['id']),$conf);
			$conf=str_replace('{nickname}',$info['nickname'],$conf);
            $this->weObj->text($conf)->reply();
    	}
    }
	
	/*
		查询所有关键字
	*/
	public function chat_config($input){
		$list=M('wechat_replyconf')->where(array('type'=>1))->select();
		foreach($list as $val){
			if(strpos($input,$val['textkey'])!==false){
				$return=$val['conf']; 
				break;
			}
		}
		return $return;
	}
	
    //微信头像加V
    private  function getWxpic($picurl){
        import("@.ORG.Http");
        import('@.ORG.Image.ThinkImage');
        $pic='Data/Wxrev/icon/'.time().'.jpg';
        Http::curlDownload($picurl,$pic);
        $img = new ThinkImage(THINKIMAGE_GD,$pic); 
        $img->thumb(200,200,THINKIMAGE_THUMB_FIXED)->water('Data/Wxrev/v.png', THINKIMAGE_WATER_SOUTHEAST)->save($pic);
        return $pic;
    }
    //free chat
    private function freeChat($msg){
		return '您的反馈我们已经收到，我们会及时处理！';
    	$url='http://api.qingyunke.com/api.php?key=free&appid=0&msg='.$msg;
        $ch = curl_init($url) ;  
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true) ; 
        curl_setopt($ch, CURLOPT_BINARYTRANSFER, true) ; 
        $output = curl_exec($ch); 
        curl_close($ch);
        $output=json_decode($output,true);
        //return $output['content'];
    }
	
	
	//记录聊天者信息[注册]
    private function reg($data){
        $db=M('wechat_user');
        $map=array('wechatid'=>$data['wechatid']);
        $info=$db->where($map)->find();
        if(empty($info)){
            $uid=$db->data($data)->add();			//Last Insert Id 
        }else{
            //unset($data['wechatid']);
            //$db->where($map)->data($data)->save();
			$uid=$info['id'];						//用户id
        }
		return $uid;			//会员ID
    }
	
	
	/*
		获取带参二维码
		@bruce
		2016-6-16
	*/
	public function scene_qrcode($scene_id){
		import("@.ORG.Wxhelper");
		$wxconf=M('wechat_config')->find(1);
		$wxhelper=new Wxhelper($wxconf);
		
		$return=$wxhelper->qrcode($scene_id);
		$qrcode='https://mp.weixin.qq.com/cgi-bin/showqrcode?ticket='.$return['ticket'];
		return $qrcode;
	}
	
	/*
		发送二维码
	*/
	public function send_qrcard(){
		
		
		import("@.ORG.Http");
		//import('@.ORG.QRcode');
		import('@.ORG.Image.ThinkImage');
		
		$info=M('wechat_user')->where(array('wechatid'=>$this->wechatid))->find();
		
		$uid=$info['id'];
		
		$user=$this->helper->get_user_info($this->wechatid);	//微信资料
		
		$qrcard='./Data/QR/qrcard/'.$uid.'.jpg';				//名片
		
		if($info['tid']==7351||$uid==7351){
			$bg_qrcard='./Public/Weixin/img/7351.jpg';		//名片背景
		}else{
			$bg_qrcard='./Public/Weixin/images/bg-qrcard.jpg';		//名片背景
		}
		
		$qrcode='./Data/QR/qrcode/'.$uid.'.jpg';				//二维码
		
		$headimg="./Data/upload/headimg/".$uid.'.jpg';				//头像
		
	 	$icon="./Data/upload/headimg/".$uid.'_100x100.jpg';			//头像缩略图
		
	 	
		if(!is_file($headimg)||!is_file($icon)){
			//下载图片
			Http::curlDownload($user['headimgurl'],$headimg);
			if(is_file($headimg)&&filesize($headimg)>0){
				$img = new ThinkImage(THINKIMAGE_GD,$headimg); 
				$img->thumb(100,100,THINKIMAGE_THUMB_FIXED)->save($icon);
			}
			//更新数据库
			$udata=array('headimgurl'=>$headimg,'nickname'=>$user['nickname']);
			M('wechat_user')->where(array('id'=>$uid))->save($udata);
		}
		
		if(!is_file($qrcode)){
			$scene_qrcode=$this->scene_qrcode($uid);
			//下载图片
			Http::curlDownload($scene_qrcode,$qrcode);
			
			$qrcode_obj = new ThinkImage(THINKIMAGE_GD,$qrcode); 
			$qrcode_obj->thumb(266,266,THINKIMAGE_THUMB_FIXED)->save($qrcode);
			
			//$qrcode_obj = new ThinkImage(THINKIMAGE_GD,$qrcode);
			//$qrcode_obj->water($icon,THINKIMAGE_WATER_CENTER)->save($qrcode);		//添加图片水印
		}
		
		if(!is_file($qrcard)){
			copy($bg_qrcard,$qrcard);
			imageWater($qrcard,$qrcode,$dst_x=120,$dst_y=450);
			imageWater($qrcard,$icon,$dst_x=13,$dst_y=16);
			textWater($qrcard,14,$user['nickname'],165,65,array(218,249,97));
		}
		
		 
		$return=$this->helper->upload_media('image',$qrcard);
		$arr=json_decode($return,true);
		$media_id=$arr['media_id'];
		$this->weObj->image($media_id)->reply();
	}
	
}
?>