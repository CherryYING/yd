
<?php
/*
	代理操作-【微信商城】
*/
class AgentAction extends BaseAction{
	public $jump;
	public function _initialize(){
		parent::_initialize();
		if(!is_weixin()) {
		   //exit('此功能只能在微信浏览器中使用');
		}
		if(is_weixin()){
			//微信js接口
			import("@.ORG.Wxjssdk");
			$wx_config=M('wechat_config')->find(1);
			$jsobj=new Wxjssdk($wx_config['appid'],$wx_config['appsecret']);
			$jssign=$jsobj->getSignPackage();
			$this->assign('jssign',$jssign);
		}
		//微信js接口
		
		//当前地址
		$curr_url=get_curr_url();
		$this->jump=base64_encode($curr_url);
		
		//需要登录的操作，无登录信息，跳转到登录页
		if(!$this->user_id&&in_array(ACTION_NAME,array('upgrade','set_account','set_pwd','tips'))){
			$this->redirect('Member/login',array('jump'=>$this->jump));
		}
		
		

	}
	/*
		升级代理商
	*/
	public function upgrade(){
		
		//$this->redirect('Ucenter/index');
		
		$list=M('agent_config')->where()->select();
		switch($this->user_info['role_id']){
			case 2:			//初级
				$list[1]['price']=$list[1]['price']-$list[0]['price'];
				$list[2]['price']=$list[2]['price']-$list[0]['price'];		//价格
				
				$list[1]['money_cloud']=$list[1]['money_cloud']-$list[0]['money_cloud'];
				$list[2]['money_cloud']=$list[2]['money_cloud']-$list[0]['money_cloud'];	//赠送金币
				unset($list[0]);
			break;
			case 3:			//中级												
				$list[2]['price']=$list[2]['price']-$list[1]['price'];						//价格
				$list[2]['money_cloud']=$list[2]['money_cloud']-$list[1]['money_cloud'];	//赠送金币
				unset($list[0]);													
				unset($list[1]);
			break;
			case 4:			//高级
				unset($list[0]);
				unset($list[1]);
				unset($list[2]);
				$this->error('您已经是钻石会员了，无需升级！',U('Ucenter/index'));
			break;
		}
		$this->assign('list',$list);
		if(is_weixin()){
			$pay_way=1;
		}else{
			$pay_way=2;
		}
		$this->assign('pay_way',$pay_way);
		$this->display();
	}
	
	/*
		代理商设置登录名密码
	*/
	public function set_account(){
		if($this->user_info['role_id']!=4){
			//$this->redirect('Ucenter/index');
			//$this->redirect('tips');
		}
		if($this->user_info['username']){
			$this->redirect('set_pwd');
		}
		$this->display();
	}
	
	/*
		重置密码
	*/
	public function set_pwd(){
		if($this->user_info['role_id']!=4){
			//$this->redirect('tips');
		}
		if(!$this->user_info['username']){
			$this->redirect('set_account');
		}
		$this->display();
	}
	
	/*
		店铺主页
	*/
	public function shop(){
		import('@.ORG.Page');
		$id=I('get.id');			//店铺ID
		$db=M('wechat_user');
		$user=$db->where(array('id'=>$id))->find();
		
		$shop=M('shop')->where(array('id'=>$id))->find();
		$this->assign('user',$user);
		$this->assign('shop',$shop);
		if($user['role_id']!=4){				//全国代理，才能开通店铺
			$this->redirect('tips');
		}else{
			if(empty($user['username'])){
				$this->redirect('set_account');
			}
			
			if($shop['shop_status']==0){
				$this->error('店铺未审核！');
				die();
			}
			//商品列表
			$map=array('sid'=>$id,'is_sale'=>1);
			
			$count = M('goods')->where($map)->count();
			$Page = new Page($count,20);
			$Page->setConfig('prev', '上一页');
			$Page->setConfig('next', '下一页');
			$Page->setConfig('theme',"%upPage% %downPage%");
			$page = $Page->show();
			$this->assign('page',$page);
			
			$list=M('goods')->where($map)->limit($Page->firstRow.','.$Page->listRows)->select();
			$this->assign('list',$list);
			//轮播图
			$slide=M('slide')->where(array('sid'=>$id,'status'=>1))->order('list asc')->select();
			$this->assign('slide',$slide);
			
			//推荐商品
			/*$recom_goods_list=M('goods_recommend')->where(array('uid'=>$id))->select();
			foreach($recom_goods_list as $val){
				$recom_goods_arr[]=$val['goods_id'];
			}
			$recom_list=M('goods')->where(array('id'=>array('in',$recom_goods_arr)))->limit(10)->select();
			$this->assign('recom_list',$recom_list);*/
			
			$this->display();
		}
		
	}
	
	/*
		店长推荐
	*/
	public function recom_list(){
		import('@.ORG.Page');
		$db=M('goods');
		$id=I('get.id');
		$shop=M('shop')->where("id=$id")->find();
		$this->assign('shop',$shop);
		//推荐商品
		$recom_goods_list=M('goods_recommend')->where(array('uid'=>$id))->select();
		foreach($recom_goods_list as $val){
			$recom_goods_arr[]=$val['goods_id'];
		}
		
		$map=array('id'=>array('in',$recom_goods_arr));
		
		$count = $db->where($map)->count();
		$Page = new Page($count,20);
		$Page->setConfig('prev', '上一页');
		$Page->setConfig('next', '下一页');
		$Page->setConfig('theme',"%upPage% %downPage%");
		$page = $Page->show();
		$this->assign('page',$page);
		
		
		$list=$db->where($map)->limit($Page->firstRow.','.$Page->listRows)->select();
		$this->assign('list',$list);
		
		$this->display();
	}
	
	/*
		 商品列表
	*/
	public function product_list(){
		import('@.ORG.Page');
		$db=M('goods');
		
		$id=I('get.id');				//分类id
		
		$sid=I('get.sid');				//店铺id
		
		$rank=I('get.rank');			//排序
		
		if($rank=='price'){
			$order='price asc';
		}elseif($rank=='sale_nums'){
			$order='sale_nums desc';
		}elseif($rank=='hits'){
			$order='hits desc';
		}else{
			$order='lists asc,id desc';
		}
		
		
		$page_title='全部商品';
		$map=array('is_sale'=>1);
		
		
		if($keyword=I('get.keyword')){
			$map['name']=array('like','%'.$keyword.'%');
			//记录历史搜索
			$so_history=cookie('so_history');
			if(empty($so_history)){
				$so_history=array($keyword);
			}else{
				array_unshift($so_history,$keyword);
			}
		}	
		
		$map['sid']=$sid;
		
		$shop=M('shop')->where(array('id'=>$sid))->find();
		
		$this->assign('shop',$shop);
		
		if($id>0){
			$map['cid']=array('like','%,'.$id.',%');
			$page_title=M('goods_category')->where(array('id'=>$id))->getField('name');
		}
		
		if($is_hot=I('get.is_hot')){
			$map['is_hot']=1;
			$page_title='热销商品';
		}
		
		
		if($keyword=I('get.keyword')){
			$map['name']=array('like','%'.$keyword.'%');
		}
		$this->assign('page_title',$page_title);
		
		
		
		$count = $db->where($map)->count();
		$Page = new Page($count,20);
		$Page->setConfig('prev', '上一页');
		$Page->setConfig('next', '下一页');
		$Page->setConfig('theme',"%upPage% %downPage%");
		$page = $Page->show();
		$this->assign('page',$page);
		
		$list=$db->where($map)->order($order)->limit($Page->firstRow.','.$Page->listRows)->select();
		$this->assign('list',$list);
		$style=I('get.style');
		if($style=='box'){
			$tpl="product_list_box";
		}else{
			$tpl="product_list";
		}
		$this->display($tpl);	
	}
	
	/*
		提示页面
	*/
	public function tips(){
		$this->display();
	}
	
	
}