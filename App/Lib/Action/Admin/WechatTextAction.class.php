<?php 
class WechatTextAction extends PublicAction{
		private $db;
		public function _initialize(){
			parent::_initialize();
			$this->db = M('wechat_replyconf');
			$wx_nav=array(
					array('name'=>'商城首页','url'=>U('Weixin/Index/index')),
					array('name'=>'商品列表','url'=>U('Weixin/Index/product_list')),
					array('name'=>'热销商品','url'=>U('Weixin/Index/product_list',array('is_hot'=>1))),
					array('name'=>'活动商品','url'=>U('Weixin/Index/product_list',array('is_active'=>1))),
					array('name'=>'个人中心','url'=>U('Weixin/Ucenter/index')),
					array('name'=>'我的订单','url'=>U('Weixin/Ucenter/order_list')),
					array('name'=>'我的钱包','url'=>U('Weixin/Ucenter/wallet')),
					array('name'=>'我的二维码','url'=>U('Weixin/Ucenter/qrcode')),
					array('name'=>'新人礼包','url'=>U('Weixin/Ucenter/coupon_list')),
			);
			$this->assign('wx_nav',$wx_nav);
			//系统内容
			$cmslist=M('cms_sort')->field('id,name')->select();
			foreach($cmslist as $key=>$val){
				$artlist=M('cms_article')->field('id,title')->where(array('fid'=>$val['id']))->select();
				$cmslist[$key]['artlist']=$artlist;
			}
			$this->assign('cmslist',$cmslist);
			//dump($cmslist);die();
		}

		public function index(){
			import("@.ORG.Page");
			//$w['textkey']=array('neq','');
			$count = $this->db->count();
			$Page = new Page($count,20);
			$show = $Page->show();
			$this->assign('show',$show);
			
			$plist=$this->db->field('id,menukey,textkey,type,conf')->where($w)->limit($Page->firstRow.','.$Page->listRows)->select();        
			$menu=M('wechat_menu')->where(array('type'=>0,'key'=>array('neq','')))->select();
			foreach($menu as $key=>$val){
				$_menu[$val['key']]=$val['name'];
			}
			$_menu['subscribe']='关注';
			//对菜单按子级顺序重排
			// $plist=sarr($plist,0);
            $this->assign('menu',$_menu);
			$this->assign('plist',$plist);
			$this->display();  
		}
		//菜单删除
		public function textdel(){
			$this->db->delete(I('get.id'));
			redirect(U('index'));
		}
		//菜单添加修改
		public function textadd(){
			if(isset($_GET['id'])){
				$nrs=$this->db->find(I('get.id'));
				$nrs['type']==1||$nrs['conf']=unserialize($nrs['conf']);
				$this->assign('nrs',$nrs);
			}
			//获取菜单
			$menulist=M('wechat_menu')->where(array('type'=>0,'key'=> array('neq','')))->select();
			$this->assign('menulist',$menulist);
			//获取栏目
			$sortlist=M('cms_sort')->select();
			$this->assign('sortlist',$sortlist);
			$this->display();
		}

		public function textaddhandle(){
			$data=$_POST;
			if(!empty($data['menukey'])){
				$data['mid']=$this->getMenuId($data['menukey']);
			}
			!$_GET['id']?$_id=$this->db->add($data):$this->db->where(array('id'=>I('get.id')))->save($data);
			$id=!empty($_id)?$_id:I('get.id');
			$this->success("操作成功！",U('textadd',array('id'=>$id)));
		}
		public function menucreate(){
			$plist=$this->db->select();
			// var_dump(json_encode($plist));
		}
		private function getMenuId($menukey){
			$db=M('wechat_menu');
			$data=$db->where(array('key'=>$menukey))->find();
			return $data['id'];
		}
                
		//为菜单增加图文动作回复
		public function newsaddrep(){
            header("content-type:text/html;charset=utf-8");
			if(array_filter($_FILES['spic']['name'])){
				$picurl=$this->getpic('spic');
			}else{
				$picurl=I('post.picurl');
			}
			$id=I('get.id');
            $tid=I('get.tid');
			$title=I('post.title');
			$descrip=I('post.descrip');
			$url=I('post.url');
			
			$newconf=array('Title'=>$title,'Description'=>$descrip,'PicUrl'=>$picurl,'Url'=>$url);
			
			$news=M('wechat_replyconf')->where('id='.$id)->getField('conf');
			
			$news_arr=unserialize($news);
                        
			if(!empty($tid)){   //编辑某条图文
				$news_arr[$tid-1]=$newconf;
				$data['conf']=$news_arr;
			}else{      //增加一条图文
				if(!empty($news_arr)&&is_array($news_arr)){
					$data['conf']=array_merge($news_arr,array($newconf));
				}else{
					$data['conf'][]=$newconf;
				}
			}
            $data['conf']=serialize($data['conf']);
                       
			M('wechat_replyconf')->where('id='.$id)->save($data);
			redirect(U('textadd',array('id'=>$id)));
		}
                //编辑某条图文
        public function newsEdit(){
			if(array_filter($_FILES['spic']['name'])){
				$picurl=$this->getpic('spic');
			}
			$id=I('get.id');
            $tid=I('get.tid');
			$title=I('post.title');
			$descrip=I('post.descrip');
			$url=I('post.url');
			$data['conf']=array(array('Title'=>$title,'Description'=>$descrip,'PicUrl'=>$picurl,'Url'=>$url));
			$arr=M('wechat_replyconf')->where('id='.$id)->getField('conf');
			$news=M('wechat_replyconf')->where('id='.$id)->find();
			$news['conf']=unserialize($news['conf']);
			$news['conf']=$news['conf'][$tid-1];
			$this->assign('news',$news);
			//获取网站栏目
			$sortlist=M('cms_sort')->select();
			$this->assign('sortlist',$sortlist);
			$this->display();

		}
		//删除某个指定的图文信息
		public function newsdelrep(){
            header("content-type:text/html;charset=utf-8");
			$id=I('get.id');
			$tid=I('get.tid')-1;
			$arr=unserialize(M('wechat_replyconf')->where('id='.$id)->getField('conf'));
			unset($arr[$tid]);
			$data['conf']=serialize(array_values($arr));
			M('wechat_replyconf')->where('id='.$id)->save($data);
			redirect(U('textadd',array('id'=>$id)));
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
}