<?php
/*
	微信会话api接口
*/
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
		//file_put_contents('log.txt',$reqdata);
		$this->pubwechat=M('wechat_config')->find(1);	//公众号信息
		$this->helper=new Wxhelper($this->pubwechat);
		
    }
    public function index() {
        switch($this->type) {
            case Wchat::MSGTYPE_TEXT:
				$this->sendReply(1,$this->content);
                exit;
                break;
            case Wchat::MSGTYPE_EVENT:
                $ev=$this->event;
                if($ev['event']=='CLICK'){	//菜单事件
                    switch ($ev['key']){
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
						//file_put_contents('log.txt',var_export($wx_info,true));
						$udata['nickname']=$wx_info['nickname'];
						$udata['province']=$wx_info['province'];
						$udata['city']=$wx_info['nickname'];
						$udata['sex']=$wx_info['sex'];
						$udata['headimgurl']=$wx_info['headimgurl'];
						
						if($ev['key']){
							$group_id=str_replace('qrscene_','',$ev['key']);
							//移动用户分组
							$this->helper->change_user_group($this->wechatid,$group_id);
							$udata['group_id']=$group_id;
						}
                        $this->reg($udata);
                        $this->sendReply(0,'subscribe');
                }elseif($ev['event']=='unsubscribe'){	//取消关注事件
                        $this->reg(array('subscribe'=>0,'subscribe_time'=>0,'posttime'=>time()));
                }elseif($ev['event']=='SCAN'){		//已关注用户扫面带参二维码
						//移动用户到对应分组
						$this->helper->change_user_group($this->wechatid,$this->event['key']);	
						$data=array(
                            'wechatid'=>$this->wechatid,
							'group_id'=>$this->event['key'],
                            'pwechat'=>$this->pwechat,
                            'token'=>$this->token,
                            'state'=>0,
							'subscribe'=>1,		//已关注
							'subscribe_time'=>time(),
                            'posttime'=>time()
                        );
                        $this->reg($data);
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
				$output=$this->freeChat($question);
				$this->createReply(1,$output); 
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
					if(substr($menu[$key]['Url'],0,4)!='http'){
						 $menu[$key]['Url']=  htmlspecialchars_decode($menu[$key]['Url'] . '&wechatid=' . $this->wechatid. '&token='.$this->token);
					}
                    substr($menu[$key]['Url'],0,4)=='http'||$menu[$key]['Url'] = $this->URL.$menu[$key]['Url'];
            }
            $this->weObj->news($menu)->reply();
        //text    
    	}else{  
			//微信信息
			$wx_info=$this->helper->get_user_info($this->wechatid);
			//<a href="'.$this->URL.'/index.php?g=Weixin&m=Ucenter&a=index">'.$wx_info['nickname'].'</a>
			$conf=str_replace('{nickname}',$wx_info['nickname'],$conf);
			//本地用户信息
			$info=M('wechat_user')->where(array('wechatid'=>$this->wechatid))->find();
			$conf=str_replace('{id}',intval($info['id'])+528,$conf);
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
    //记录聊天者信息[注册]
    private function reg($data){
        $db=M('wechat_user');
        $map=array('wechatid'=>$data['wechatid']);
        $info=$db->where($map)->find();
        if(empty($info)){
            $db->data($data)->add();
        }else{
            unset($data['wechatid']);
            $db->where($map)->data($data)->save();
        }
    }
    //根据wechatid获取用户uid
    private function getuid($wechatid){
        $db=M('wechat_user');
        $map=array('wechatid'=>$wechatid);
        $info=$db->where($map)->find();
        return $info['id'];
    }
    //获取最近一次聊天内容
    private function getLastChat(){
       $db=M('wechat_user');
       $map=array('wechatid'=>$this->wechatid,'token'=>$this->token);
       return $lastChat=$db->where($map)->getField('lastchat');
    }
    //清空最近一次聊天内容
    private function clearLastChat(){
       $db=M('wechat_user');
       $map=array('wechatid'=>$this->wechatid,'token'=>$this->token);
       $db->where($map)->setField('lastchat',NULL);
       return;
    }
    //free chat
    private function freeChat($msg){
    	$url='http://api.qingyunke.com/api.php?key=free&appid=0&msg='.$msg;
        $ch = curl_init($url) ;  
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true) ; 
        curl_setopt($ch, CURLOPT_BINARYTRANSFER, true) ; 
        $output = curl_exec($ch); 
        curl_close($ch);
        $output=json_decode($output,true);
        return $output['content'];
    }
}
?>