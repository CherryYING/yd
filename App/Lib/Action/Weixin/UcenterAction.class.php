<?php
/*
	用户中心控制器-【微信商城】
*/
class UcenterAction extends BaseAction{
	public function _initialize(){
		parent::_initialize();
		import('@.ORG.Page');
		import("@.ORG.Http");
        import('@.ORG.Image.ThinkImage');
		$agent = $_SERVER['HTTP_USER_AGENT']; 
		if(!strpos($agent,"icroMessenger")) {
		   //exit('此功能只能在微信浏览器中使用');
		}else{
			$headimg="./Data/upload/headimg/".$this->user_id.'.jpg';
			$icon="./Data/upload/headimg/".$this->user_id.'_100x100.jpg';
			if(!is_file($headimg)||!is_file($icon)){
				unlink($headimg);
				//下载微信头像
				if(!empty($this->user_info['headimgurl'])){				//防止没有微信头像报错
				
					$qrcard="./Data/QR/qrcard/".$this->user_id.".jpg";
					
					if(!is_file($qrcard)){
						Http::curlDownload($this->user_info['headimgurl'],$headimg);
						if(is_file($headimg)){
							$img = new ThinkImage(THINKIMAGE_GD,$headimg); 
							$img->thumb(100,100,THINKIMAGE_THUMB_FIXED)->save($icon);
						}
					}
					
				}
				//更新数据库
				//M('wechat_user')->where(array('id'=>$this->user_id))->save(array('headimgurl'=>$headimg));
			}
		}
		$jump=get_curr_url();
		$jump=base64_encode($jump);
		if(!$this->user_id){
			//无登录信息，跳转到登录页
			$this->redirect('Weixin/Member/login',array('jump'=>$jump));
		}
		//底部导航
		$nav=M('navlink')->field('id,title,url')->where(array('fup'=>0,'cid'=>1))->order('id asc')->select();
		foreach($nav as $key=>$val){
			$nav[$key]['child']=M(navlink)->field('id,title,url')->where(array('fup'=>$val['id'],'cid'=>1))->order('list asc')->select();
		}
		$this->assign('nav',$nav);
		
		
		/*
			微信jssdk
		*/
		if(is_weixin()){
			import("@.ORG.Wxjssdk");
			$wx_config=F('wx_config');
			$jsobj=new Wxjssdk($wx_config['appid'],$wx_config['appsecret']);
			$jssign=$jsobj->getSignPackage();
			$this->assign('jssign',$jssign);
		}
		
		
	}
	
	/*
		用户中心
	*/
	public function index(){
		//用户总数
		$user_count=M('wechat_user')->count();
		$this->assign('user_count',$user_count);
        $this->display();
	}
	
	
	/*
		 代金券管理
	*/
	public function coupon_list(){
		$db=M('coupon');
		
		$map=array('uid'=>$this->user_id);
		
		$count = $db->where($map)->count();	
		$Page = new Page($count,20);
		$Page->setConfig('prev', '上一页');
		$Page->setConfig('next', '下一页');
		$Page->setConfig('theme',"%upPage% %downPage%");
		$page = $Page->show();
		$this->assign('page',$page);
		
		$list=$db->where($map)->order('id desc')->limit($Page->firstRow.','.$Page->listRows)->select();
		$this->assign('list',$list);
		$this->display();
	}
	
	/*
		我的资料
	*/
	public function profile(){
		$this->display();
	}
	/*
		个人信息
	*/
	public function info(){
		$db=M('wechat_user');
		$info=$db->find($this->user_id);
		$this->assign('info',$info);
		if($data=$this->_post()){
			$rs=$db->where(array('id'=>$this->user_id))->save($data);
			$info=$db->find($this->user_id);
			$this->redirect('info');
		}
		if(empty($info['username'])){
			$tpl='set_account';
		}else{
			$tpl='info';
		}
		
		$this->display();
	}
	
	/*
		基础信息
	*/
	public function person_info(){
		$this->display();
	}
	
	
	/*
		银行卡信息
	*/
	public function bank_info(){
		$this->display();
	}
	
	/*
		积分榜
	*/
	public  function jifen_rank(){
		$db=M('wechat_user');
		$list=$db->where()->order('jifen desc')->limit(100)->select();
		foreach($list as $key=>$val){
			$list[$key]['rank']=$key+1;
			$list[$key]['son_count']=$db->where("p_1={$val['id']}")->count();
		}
		$this->assign('list',$list);
		$this->display();
	}
	
	
	/*
		资金管理【A网】
	*/
	public function fund_a(){
		import('@.ORG.Page');
		$db=M('money_water');
		
		$map=array('uid'=>$this->user_id,'money_type'=>'a');		//,'type'=>1
		
		$count =$db->where($map)->count();
		$Page = new Page($count,20);
		$Page->setConfig('prev', '上一页');
		$Page->setConfig('next', '下一页');
		$Page->setConfig('theme',"%upPage% %downPage%");
		$page = $Page->show();
		$this->assign('page',$page);
		
		$list=$db->where($map)->order('id desc')->limit($Page->firstRow.','.$Page->listRows)->select();
		$this->assign('list',$list);
		$this->display();
	}
	
	/*
		资金管理【货款】
	*/
	public function fund_p(){
		import('@.ORG.Page');
		$db=M('money_water');
		
		$map=array('uid'=>$this->user_id,'money_type'=>'p');		//,'type'=>1
		
		$count =$db->where($map)->count();
		$Page = new Page($count,20);
		$Page->setConfig('prev', '上一页');
		$Page->setConfig('next', '下一页');
		$Page->setConfig('theme',"%upPage% %downPage%");
		$page = $Page->show();
		$this->assign('page',$page);
		
		$list=$db->where($map)->order('id desc')->limit($Page->firstRow.','.$Page->listRows)->select();
		$this->assign('list',$list);
		$this->display();
	}
	
	
	/*
		资金管理【B网】
	*/
	public function fund(){
		import('@.ORG.Page');
		$db=M('money_water');
		
		$map=array('uid'=>$this->user_id,'money_type'=>'b');		//,'type'=>1
		
		$count =$db->where($map)->count();
		$Page = new Page($count,20);
		$Page->setConfig('prev', '上一页');
		$Page->setConfig('next', '下一页');
		$Page->setConfig('theme',"%upPage% %downPage%");
		$page = $Page->show();
		$this->assign('page',$page);
		
		$list=$db->where($map)->order('id desc')->limit($Page->firstRow.','.$Page->listRows)->select();
		$this->assign('list',$list);
		$this->display();
	}
	

	/*
		订单列表【我的购买订单】
	*/
	public function order_list(){
		import('@.ORG.Page');
		$db=M('order_info');
		$map=array('uid'=>$this->user_id);
		
		$count = $db->where($map)->count();	//
		$Page = new Page($count,20);
		$Page->setConfig('prev', '上一页');
		$Page->setConfig('next', '下一页');
		$Page->setConfig('theme',"%upPage% %downPage%");
		$page = $Page->show();
		$this->assign('page',$page);
		
		$list=$db->where($map)->order('id desc,pay_status asc')->limit($Page->firstRow.','.$Page->listRows)->select();
		foreach($list as $key=>$val){
			$list[$key]['goods']=M('order_goods')->where(array('order_id'=>$val['id']))->find();
		}
		$this->assign('list',$list);
		$this->display();
	}
	/*
		 我销售订单【仅限于微店主】
	*/
	public function sale_order(){
		import('@.ORG.Page');
		$db=M('order_info');
		$map=array('shop_id'=>$this->user_id);			//订单店铺id==微店主id
		
		$count = $db->where($map)->count();	//
		$Page = new Page($count,20);
		$Page->setConfig('prev', '上一页');
		$Page->setConfig('next', '下一页');
		$Page->setConfig('theme',"%upPage% %downPage%");
		$page = $Page->show();
		$this->assign('page',$page);
		
		$list=$db->where($map)->order('id desc,pay_status asc')->limit($Page->firstRow.','.$Page->listRows)->select();
		foreach($list as $key=>$val){
			$list[$key]['goods']=M('order_goods')->where(array('order_id'=>$val['id']))->find();
		}
		$this->assign('list',$list);
		$this->display();
	}
	
	/*
		订单详情
	*/
	public function order_detail(){
		$order_id=I('get.id');
		$db=M('order_info');
		$order=$db->where(array('id'=>$order_id))->find();
		
		if(empty($order)){
			$this->error('订单信息不存在',U('order_list'));
		}
		$order_goods=M('order_goods')->where(array('order_id'=>$order_id))->select();
		foreach($order_goods as $key=>$val){
			$order_goods[$key]['shop']=M('shop')->where(array('id'=>$val['sid']))->find();
		}
		$this->assign('order',$order);
		$this->assign('order_goods',$order_goods);
		//售后信息
		$refund_info=M('order_refund')->where(array('order_id'=>$order_id))->find();
		$this->assign('refund_info',$refund_info);
		$this->display();		
	}
	
	/*
		 积分兑换订单
	*/
	public function jifen_order(){
		import('@.ORG.Page');
		$db=M('jifen_order');
		
		$map=array('user_id'=>$this->user_id);
		
		$count = $db->where($map)->count();	//
		$Page = new Page($count,20);
		$Page->setConfig('prev', '上一页');
		$Page->setConfig('next', '下一页');
		$Page->setConfig('theme',"%upPage% %downPage%");
		$page = $Page->show();
		$this->assign('page',$page);
		
		$list=$db->where($map)->order('id desc')->limit($Page->firstRow.','.$Page->listRows)->select();
		
		foreach($list as $key=>$val){
			$list[$key]['goods']=M('jifen_order_goods')->where(array('order_id'=>$val['id']))->find();
		}
		
		$this->assign('list',$list);
		$this->display();
	}
	
	
	/*
		地址管理
	*/
	public function address_list(){
		import('@.ORG.Page');
		$db=M('user_address');
		
		$map=array('uid'=>$this->user_id);
		
		$count = $db->where($map)->count();	//
		$Page = new Page($count,20);
		$Page->setConfig('prev', '上一页');
		$Page->setConfig('next', '下一页');
		$Page->setConfig('theme',"%upPage% %downPage%");
		$page = $Page->show();
		$this->assign('page',$page);
		
		$list=$db->where($map)->order('id desc')->limit($Page->firstRow.','.$Page->listRows)->select();
		$this->assign('list',$list);
		
		if($id=I('get.id')){
			$info=$db->where(array('id'=>$id))->find();
			if(empty($info)){
				$this->redirect('Ucenter/address_list');
			}
			$this->assign('info',$info);
		}
		
		$this->display();
	}
	/*
		编辑地址
	*/
	public function address_edit(){
		$id=I('get.id');
		$db=M('user_address');
		$info=$db->find($id);
		$this->assign('info',$info);
		if($data=$this->_post()){
		   $db->where(array('id'=>$id))->save($data);
		   $this->redirect('address_list');
		}
		$this->display();

	}
	/*
		新增编辑
	*/
	public function address_add(){
		if($data=$this->_post()){
			$data['user_id']=$this->user_id;
			M('user_address')->add($data);
			$this->redirect('address_list');
		}
		$this->display();
	}
	/*
		我的二维码
	*/
	
	public function qrcode(){
		import("@.ORG.Wxhelper");
		import("@.ORG.Wxjssdk");
		import('@.ORG.Image.ThinkImage');
		
		$wxconf=M('wechat_config')->find(1);
		
		$jsobj=new Wxjssdk($wxconf['appid'],$wxconf['appsecret']);
		$jssign=$jsobj->getSignPackage();
		$this->assign('jssign',$jssign);
		

		$wxhelper=new Wxhelper($wxconf);
		
		if(!$parent_id=I('get.parent_id')){
			$parent_id=I('session.user_id');
		}
		
		$qr_local='./Data/QR/qrcode/'.$parent_id.'.jpg';
		$qrcard='./Data/QR/qrcard/'.$parent_id.'.jpg';
		
		if($this->user_id==7351||$this->user_info['tid']==7351){
			$bg_qrcard='./Public/Weixin/img/7351.jpg';			//名片背景
		}else{
			$qrcard_bg='./Public/Weixin/images/bg-qrcard.jpg';	//名片背景
		}
		
		
		$icon='./Data/upload/headimg/'.$parent_id.'_100x100.jpg';
		
		if(!is_file($qr_local)){
			$return=$wxhelper->qrcode($parent_id);
			$qrcode='https://mp.weixin.qq.com/cgi-bin/showqrcode?ticket='.$return['ticket'];
			//下载图片
			Http::curlDownload($qrcode,$qr_local);
			
			$img_obj = new ThinkImage(THINKIMAGE_GD,$qr_local); 
			$img_obj->thumb(266,266,THINKIMAGE_THUMB_FIXED)->save($qr_local);
			/*$img_obj->water($icon,THINKIMAGE_WATER_CENTER)->save($qr_local);		//添加图片水印*/
		}
		
		if(!is_file($qrcard)){
			copy($qrcard_bg,$qrcard);
			imageWater($qrcard,$qr_local,$dst_x=120,$dst_y=450);
			imageWater($qrcard,$icon,$dst_x=13,$dst_y=16);
			textWater($qrcard,16,$this->user_info['nickname'],165,65,array(218,249,97));
		}
		
		$this->assign('qrcode',$qr_local);
		
		$this->assign('qrcard',$qrcard);
		
		$this->assign('parent_id',$parent_id);
		
		$this->display();
	}
	
	public function qrcode_back(){
		import('@.ORG.QRcode');
		import('@.ORG.Image.ThinkImage');
		
		if(empty($_GET['tid'])){
			$this->redirect('Ucenter/qrcode',array('tid'=>$this->user_id));
		}
		
		if(I('get.tid')){
			$tid=I('get.tid');
		}else{
			$tid=$this->user_id;
		}
		
		$t_user=M('wechat_user')->where(array('id'=>$tid))->find();
		$this->assign('t_user',$t_user);
		
		$url='http://'.I('server.HTTP_HOST').U("Index/index",array('tid'=>$tid));
		$qrcode_name='./Data/upload/qrcode/'.$tid.'.jpg';
		//$logo="./Data/upload/headimg/".$parent_id.'_icon.jpg';
		if(!is_file($qrcode_name)||filesize($qrcode_name)==0){
			QRcode::png($url, $qrcode_name, 'L',8, 2); 			//生成图片
			/*if(is_file($logo)){
				$img = new ThinkImage(THINKIMAGE_GD,$qrcode_name);
				$img->water($logo,THINKIMAGE_WATER_CENTER)->save($qrcode_name);		//添加图片水印
			}*/
		}
		//二维码
		$this->assign('qrcode',$qrcode_name);
		//推广链接
		$share_url='http://'.I('server.HTTP_HOST').U('Weixin/Index/index',array('tid'=>$tid));
		$this->assign('share_url',$share_url);
		
		
		$this->assign('tid',$tid);
		$this->display();
	}
	
	
	/*
		我的积分
	*/
	public function jifen(){
		import('@.ORG.Page');
		$db=M('jifen_water');
		
		$map=array('user_id'=>$this->user_id);
		
		$count = $db->where($map)->count();
		$Page = new Page($count,20);
		$Page->setConfig('prev', '上一页');
		$Page->setConfig('next', '下一页');
		$Page->setConfig('theme',"%upPage% %downPage%");
		$page = $Page->show();
		$this->assign('page',$page);
		
		
		$list=$db->where($map)->order('id desc')->limit($Page->firstRow.','.$Page->listRows)->select();
		/*foreach($list as $key=>$val){
			$order=M('order_info')->find($val['order_id']);
			$list[$key]['order']=$order;
			unset($order);
		}*/
		
		$this->assign('list',$list);
		
		$this->display();
	}
	
	
	
	public function  take_money_list(){
		$db=M('take_money');
		$map=array('user_id'=>$this->user_id);
		
		$count = $db->where($map)->count();
		$Page = new Page($count,20);
		$Page->setConfig('prev', '上一页');
		$Page->setConfig('next', '下一页');
		$Page->setConfig('theme',"%upPage% %downPage%");
		$page = $Page->show();
		$this->assign('page',$page);
		
		$list=$db->where($map)->order('id desc')->limit($Page->firstRow.','.$Page->listRows)->select();
		$this->assign('list',$list);
		$this->display();
	}
	/*
		店主管理中心
	*/
	public function shop_center(){
		$this->display();
	}
	/*
		店铺设置
	*/
	public function shop_setting(){
		$this->display();
	}
	
	
	/*
		注册开店
	*/	
	public function shop_reg(){
		$db=M('wechat_user');
		$parent_id=I('get.parent_id');		//父类id	
		$reg_code=I('get.reg_code');		//注册码
		$this->assign('parent_id',$parent_id);
		$this->assign('reg_code',$reg_code);
		$this->display();
	}
	/*
		店铺商品管理
	*/
	public function shop_goods(){
		import('@.ORG.Page');
		if($this->user_info['role_id']==1){
			$this->redirect('index');
		}
		$db=M('goods');
		
		
		$count = $db->where()->count();	//
		$Page = new Page($count,20);
		$Page->setConfig('prev', '上一页');
		$Page->setConfig('next', '下一页');
		$Page->setConfig('theme',"%upPage% %downPage%");
		$page = $Page->show();
		$this->assign('page',$page);
		
		$list=$db->where()->limit($Page->firstRow.','.$Page->listRows)->select();
		$fenyongconfig=M("resale_config")->where(array('id'=>1))->getField('parent_1');
		foreach($list as $key=>$val){
			$list[$key]['shopuongjin']=($fenyongconfig/100)*$val['yongjin'];
		}
		$this->assign('list',$list);
		//我的推荐列表
		$_list=M('goods_recommend')->where(array('user_id'=>$this->user_id))->select();
		foreach($_list as $key=>$val){
			$my_list[]=$val['goods_id'];
		}
		$this->assign('my_list',$my_list);
		$this->display();
	}
	/*
		申请提现，选择提现方式
	*/
	public function take_money_index(){

		$this->display();
	}
	
	/*
		提现申请
	*/
	public function take_money(){
		$pay_way=I('get.pay_way');		//1银行卡；2支付宝；3微信
		if(empty($pay_way)){
			$this->error('参数错误',U('take_money_index'));
		}
		
		if($this->user_info['role_id']!=2){
			//$this->error('您暂时不能申请提现，只有分销商才能申请提现！');
		}
		if(empty($this->user_info['bank_name'])||empty($this->user_info['bank_card'])){
			//$this->error('请先完善个人银行卡信息！',U('bank_info'));
		}
		$this->display();
	}
	/*
		修改登录
	*/
	public function pwd(){
		$this->display();
	}
	/*
		商品评价
	*/
	public function goods_comment(){
		$db=M('order_goods');
		$id=I('get.id');
		$info=$db->where(array('id'=>$id))->find();
		$this->assign('info',$info);
		if($info['status']==1){	
			$this->error('您已经评价过了！');
		}else{
			$this->display();
		}
	}
	
	/*
		我的评论列表
	*/
	public function comment_list(){
		
		$db=M('goods_comment');
		
		$map=array('user_id'=>$this->user_id);
		
		$count = $db->where($map)->count();	//
		$Page = new Page($count,20);
		$Page->setConfig('prev', '上一页');
		$Page->setConfig('next', '下一页');
		$Page->setConfig('theme',"%upPage% %downPage%");
		$page = $Page->show();
		$this->assign('page',$page);
		
		$list=$db->where($map)->order('id desc')->limit($Page->firstRow.','.$Page->listRows)->select();
		$this->assign('list',$list);
		$this->display();
	}
	
	/*
		设置账户名，密码【微信用户】
	*/
	public function set_account(){
		$this->display();
	}
	
	/*
		申请退款
	*/
	public function order_refund(){
		//订单商品表id
		$id=I('get.id');			
		$goods=M('order_goods')->where(array('id'=>$id))->find();
		$order=M('order_info')->where(array('id'=>$goods['order_id']))->find();
		
		$i=M('order_refund')->where(array('goods_id'=>$goods['goods_id'],'order_id'=>$goods['order_id']))->find();
		if(!empty($i)){
			$this->redirect('order_refund_list');
		}
		$refund_arr=array(1=>'退款',2=>'换货');
		
		if(empty($order)){
			$this->error('订单信息不存在！');
		}elseif($order['pay_status']==0){
			$this->error('未付款订单，暂时不能申请售后服务！');
		}
		$this->assign('refund_arr',$refund_arr);
		$this->assign('order',$order);
		$this->assign('goods',$goods);
		
		$this->display();
	}
	
	
	/*
		售后列表
	*/
	public function order_refund_list(){
		$db=M('order_refund');
		
		$map=array('uid'=>$this->user_id);
		
		$count = $db->where($map)->count();	
		$Page = new Page($count,20);
		$Page->setConfig('prev', '上一页');
		$Page->setConfig('next', '下一页');
		$Page->setConfig('theme',"%upPage% %downPage%");
		$page = $Page->show();
		$this->assign('page',$page);
		
		$list=$db->where($map)->order('id desc')->limit($Page->firstRow.','.$Page->listRows)->select();
		foreach($list as $key=>$val){
			//订单信息
			$list[$key]['order']=M('order_info')->where(array('order_id'=>$val['order_id']))->find();
			//订单商品信息
			$list[$key]['goods']=$goods=M('order_goods')->where(array('order_id'=>$val['order_id'],'goods_id'=>$val['goods_id']))->find();
			//店铺信息
			$list[$key]['shop']=M('shop')->where(array('id'=>$goods['sid']))->find();
		}
		$this->assign('list',$list);
		$this->display();
	}
	
	/*
		 我的分销
	*/
	public function resale(){
		$this->display();
	}
	/*
		我的佣金
	*/
	public function yongjin_list(){
		$db=M('money_water');
		$map=array('user_id'=>$this->user_id);
		$list=$db->where($map)->limit(20)->select();
		$this->assign('list',$list);
		$this->display();
	}
	
	/*
		我的收藏
	*/
	public function goods_collect(){
		$db=M('goods_collect');
		
		$map=array('uid'=>$this->user_id);
		
		$count = $db->where($map)->count();	
		$Page = new Page($count,20);
		$Page->setConfig('prev', '上一页');
		$Page->setConfig('next', '下一页');
		$Page->setConfig('theme',"%upPage% %downPage%");
		$page = $Page->show();
		$this->assign('page',$page);
		
		$list=$db->where($map)->order('id desc')->limit($Page->firstRow.','.$Page->listRows)->select();
		$this->assign('list',$list);
		$this->display();
	}

	/*
		支付成功，中转页面
	*/
	public function call_back_url(){
		//订单id
		$order_id=I('get.id');		
		//模板消息通知用户
		order_pay_ok_notice($order_id);		
		//通知上级
		order_pay_ok_parent_notice($order_id);
		//订单金额超过360或累计消费金额查过500;升级分销商
		up_resaler($order_id);
		
		//订单分佣
		order_fenyong($order_id);
		
		//重定向到订单详情页
		$this->redirect('order_detail',array('id'=>$order_id));
	}
	
	/*
		 我的上级
	*/
	public function parents(){
		$db=M('wechat_user');
		$info=$db->where(array('id'=>$this->user_id))->find();
		
		$parent=$db->where(array('id'=>array('in',array($info['p_1'],$info['p_2'],$info['p_3']))))->order('id desc')->select();
		foreach($parent as $key=>$val){
			if($val['id']==$info['p_1']){
				$parent[$key]['relation']='上一级';
			}
			if($val['id']==$info['p_2']){
				$parent[$key]['relation']='上二级';
			}
			if($val['id']==$info['p_3']){
				$parent[$key]['relation']='上三级';
			}
		}
		
		$this->assign('parent',$parent);
		$this->display();	
	}
	
	/*
		我的团队
	*/
	public function team(){
		$db=M('wechat_user');
		//下一级
		$count[1]=$db->where(array('p_1'=>$this->user_id))->count();
		$count[2]=$db->where(array('p_2'=>$this->user_id))->count();
		$count[3]=$db->where(array('p_3'=>$this->user_id))->count();
		$this->assign('count',$count);
		$this->display();	
	}
	
	/*
		 我的团队列表
	*/
	public function team_list(){
		import('@.ORG.Page');
		$db=M('wechat_user');
		$type=I('get.type');
		
		switch($type){
			case 1:
				$map['p_1']=$this->user_id;
			break;
				
			case 2:
				$map['p_2']=$this->user_id;
			break;
				
			case 3:
				$map['p_3']=$this->user_id;
			break;
		}

		$count =$db->where($map)->count();
		$Page = new Page($count,20);
		$Page->setConfig('prev', '上一页');
		$Page->setConfig('next', '下一页');
		$Page->setConfig('theme',"%upPage% %downPage%");
		$page = $Page->show();
		$this->assign('page',$page);
		
		$son_list=$db->where($map)->order('id asc')->limit($Page->firstRow.','.$Page->listRows)->select();
		foreach($son_list as $key=>$val){
			if($val['p_1']==$this->user_id){
				$son_list[$key]['relation']='下一级';
			}elseif($val['p_2']==$this->user_id){
				$son_list[$key]['relation']='下二级';
			}elseif($val['p_3']==$this->user_id){
				$son_list[$key]['relation']='下三级';
			}
			$son_list[$key]['p_user']=$db->where(array('id'=>$val['p_1']))->find();
		}
		$this->assign('son_list',$son_list);
		$this->display();
		

	}
	
	/*
		 我的下级
	*/
	public function children(){
		import('@.ORG.Page');
		$db=M('wechat_user');
		$map="p_1=$this->user_id OR p_2=$this->user_id OR p_3=$this->user_id";
		
		$count =$db->where($map)->count();
		$Page = new Page($count,20);
		$Page->setConfig('prev', '上一页');
		$Page->setConfig('next', '下一页');
		$Page->setConfig('theme',"%upPage% %downPage%");
		$page = $Page->show();
		$this->assign('page',$page);
		
		$son_list=$db->where($map)->order('id asc')->limit($Page->firstRow.','.$Page->listRows)->select();
		foreach($son_list as $key=>$val){
			if($val['p_1']==$this->user_id){
				$son_list[$key]['relation']='下一级';
			}elseif($val['p_2']==$this->user_id){
				$son_list[$key]['relation']='下二级';
			}elseif($val['p_3']==$this->user_id){
				$son_list[$key]['relation']='下三级';
			}
		}
		$this->assign('son_list',$son_list);
		$this->display();
	}
	
	/*
		云金币
	*/
	public function cloud_coins(){
		import('@.ORG.Page');
		$db=M('money_cloud_water');
		
		$map=array('uid'=>$this->user_id);
		
		$count =$db->where($map)->count();
		$Page = new Page($count,20);
		$Page->setConfig('prev', '上一页');
		$Page->setConfig('next', '下一页');
		$Page->setConfig('theme',"%upPage% %downPage%");
		$page = $Page->show();
		$this->assign('page',$page);
		
		$list=$db->where($map)->order('id desc')->limit($Page->firstRow.','.$Page->listRows)->select();
		$this->assign('list',$list);
		$this->display();
	}
	
	
	/*
		提现详情
	*/
	public function take_money_detail(){
		$db=M('take_money');
		$id=I('get.id');
		$info=$db->where(array('id'=>$id))->find();
		$this->assign('info',$info);
		$this->display();
	}
	
	/*
		资金明细
	*/
	public function fund_detail(){
		$db=M('money_water');
		$id=I('get.id');
		$info=$db->where(array('id'=>$id))->find();
		if($info['order_id']>0){
			if($info['money_type']=='a'){
				$order=M('agent_order')->where(array('id'=>$info['order_id']))->find();
			}else{
				$order=M('order_info')->where(array('id'=>$info['order_id']))->find();
			}
			$f_user=M('wechat_user')->where(array('id'=>$order['uid']))->find();
			$this->assign('f_user',$f_user);
		}
		
		$this->assign('info',$info);
		$this->display();
	}
	
	
	/*
		商品管理-个人微店
	*/
	public function goods_manage(){
		$db=M('goods');
		$map=array();
		
		$count =$db->where($map)->count();
		$Page = new Page($count,20);
		$Page->setConfig('prev', '上一页');
		$Page->setConfig('next', '下一页');
		$Page->setConfig('theme',"%upPage% %downPage%");
		$page = $Page->show();
		$this->assign('page',$page);
		
		$list=$db->where($map)->order('id desc')->limit($Page->firstRow.','.$Page->listRows)->select();
		$this->assign('list',$list);
		
		
		//我的推荐列表
		$_list=M('goods_recommend')->where(array('uid'=>$this->user_id))->select();
		foreach($_list as $key=>$val){
			$my_list[]=$val['goods_id'];
		}
		$this->assign('my_list',$my_list);
		
		$this->display();
	}
	
	
	
}