
<?php
/*
	首页控制器-【微信商城】
*/
class IndexAction extends BaseAction{
	public $jump;
	public $cate_list;
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
		if(!$this->user_id&&in_array(ACTION_NAME,array('cart','cart2'))){
			$this->redirect('Member/login',array('jump'=>$this->jump));
		}
		
		$domian=I('server.HTTP_HOST');
		$tid=I('get.tid','','intval');
		if($tid){
			$t_user=M('wechat_user')->field('id,nickname')->where()->find();
			$this->assign('t_user',$t_user);
		}
		if($this->user_id&&$this->user_id!=$tid){
			//发送访客提醒
			visit_notice($this->user_id,$tid);
		}
		
		if($this->user_id&&empty($tid)){		//strpos($curr_url,$domian)!==false
			
			if(empty($tid)){
				if(strpos($curr_url,'?')===false){
					$redirect=$curr_url.'?tid='.$this->user_id;
				}else{
					$redirect=$curr_url.'&tid='.$this->user_id;
				}
				redirect($redirect);
			}elseif($tid!=$this->user_id){
				$preg="/(tid=\d)/";
				$redirect=preg_replace($preg,"tid=$this->user_id",$curr_url);
				//redirect($redirect);
			}
				
		}

	}
	//首页
	public function index(){
		//轮播图片
		$slide=M('slide')->where(array('cid'=>1))->order('list asc')->select();	
		$this->assign('slide',$slide);
		//商品按分类展示
		$_cate_list=M('goods_category')->where(array('is_show'=>1,'is_tui'=>1))->order('list asc')->select();
		
		foreach($_cate_list as $key=>$val){
			$_cate_list[$key]['goods_list']=M('goods')->where(array('cid'=>array('like','%,'.$val['id'].',%'),'is_sale'=>1,'is_tui'=>1))->limit(4)->order('lists asc,id desc')->select();	//
		}
		$this->assign('_cate_list',$_cate_list);
		//积分兑换商品
		$jf_goods=M('jifen_goods')->where(array('is_sale'=>1))->order('id desc')->limit(4)->select();
		$this->assign('jf_goods',$jf_goods);
		
		
		
		//首页导航
		$nav=M('navlink')->field('id,title,url,spic')->where(array('fup'=>0,'cid'=>1))->order('list asc')->select();
		$this->assign('nav',$nav);
		$this->display();
	}
	
	
	/*
		搜索
	*/
	public function search(){
		$list=cookie('so_history');
		$list=array_unique($list);
		$this->assign('list',$list);
		$this->display();
	}
	
	/*
		按分类展示商品
	*/
	public function cate_product(){
		import('@.ORG.Page');
		$db=M('goods');
		if($cate_id=I('get.cate_id')){
			$map=array('cid'=>array('like','%'.$cate_id.'%'));
		}
		$cate_info=M('goods_category')->field('id,name')->find($cate_id);
		$this->assign('page_title',$cate_info['name']);
		
		$count = $db->where($map)->count();
		$Page = new Page($count,20);
		$Page->setConfig('prev', '上一页');
		$Page->setConfig('next', '下一页');
		$Page->setConfig('theme',"%upPage% %downPage%");
		$page = $Page->show();
		$this->assign('page',$page);
		
		$list=$db->where($map)->order($order)->limit($Page->firstRow.','.$Page->listRows)->select();
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
			//cookie('so_history',$so_history);
		}	
		
		if($id>0){
			$map['cid']=array('like','%,'.$id.',%');
			$page_title=M('goods_category')->where(array('id'=>$id))->getField('name');
		}
		/*if($is_new=I('get.is_new')){
			$order="id desc";
		}
		if($is_tui=I('get.is_tui')){
			$map['is_tui']=1;
			$page_title='最新推荐';
		}
		
		if($is_active=I('get.is_active')){
			$map['is_active']=1;
			$page_title='活动商品';
		}*/
		
		if($is_hot=I('get.is_hot')){
			$map['is_hot']=1;
			$page_title='热销商品';
		}
		
		
		if($keyword=I('get.keyword')){
			$map['name']=array('like','%'.$keyword.'%');
			$cate=M('goods_category')->where(array('name'=>array('like','%'.$keyword.'%')))->find();
			if(empty($cate)){
				//$map['cid']=array('like','%,'.$cate['ic'].',%');
			}
		}
		$this->assign('page_title',$page_title);
		
		
		
		$shop_list=M('shop')->where(array('shop_status'=>0))->select();
		foreach($shop_list as $val){
			$unauth_shop[]=$val['id'];
		}
		
		//$map['sid']=array('not in',$unauth_shop);
		
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
		//echo $db->getlastsql();
	}
	/*
		团购列表
	*/
	public function group_list(){
		import('@.ORG.Page');
		$db=M('goods');
		
		$id=I('get.id');				//分类id
		
		$rank=I('get.rank');			//排序
		
		if($rank=='price'){
			$order='price asc';
		}elseif($rank=='sale_nums'){
			$order='sale_nums desc';
		}elseif($rank=='hits'){
			$order='hits desc';
		}else{
			$order='id asc';
		}
		
		
		$page_title='团购秒杀';
		$map=array('is_sale'=>1,'is_group'=>1);
		
		
		if($keyword=I('get.keyword')){
			$map['name']=array('like','%'.$keyword.'%');
			//记录历史搜索
			$so_history=cookie('so_history');
			if(empty($so_history)){
				$so_history=array($keyword);
			}else{
				array_unshift($so_history,$keyword);
			}
			cookie('so_history',$so_history);
		}	
		
		if($id>0){
			$map['cid']=array('like','%'.$id.',%');
			$page_title=M('goods_category')->where(array('id'=>$id))->getField('name');
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
/*		$style=I('get.style');
		if($style=='box'){
			$tpl="product_list_box";
		}else{
			$tpl="product_list";
		}*/
		$this->display();	
	}
	
	/*
		团购详情页
	*/
	public function group_show(){
		$db=M('goods');
		$id=I('get.id');
		$info=$db->where(array('id'=>$id))->find();
		if($info['is_group']!=1){
			$this->error('该商品暂时不能团购');
		}else{
			$this->assign('info',$info);
			$this->display();
		}
	}
	/*
		秒杀列表
	*/
	public function seckill_list(){
		import('@.ORG.Page');
		$db=M('goods');
		
		$id=I('get.id');				//分类id
		
		$rank=I('get.rank');			//排序
		
		if($rank=='price'){
			$order='price asc';
		}elseif($rank=='sale_nums'){
			$order='sale_nums desc';
		}elseif($rank=='hits'){
			$order='hits desc';
		}else{
			$order='id asc';
		}
		
		
		$page_title='团购秒杀';
		$map=array('is_sale'=>1,'is_group'=>1);
		
		
		if($keyword=I('get.keyword')){
			$map['name']=array('like','%'.$keyword.'%');
			//记录历史搜索
			$so_history=cookie('so_history');
			if(empty($so_history)){
				$so_history=array($keyword);
			}else{
				array_unshift($so_history,$keyword);
			}
			cookie('so_history',$so_history);
		}	
		
		if($id>0){
			$map['cid']=array('like','%'.$id.',%');
			$page_title=M('goods_category')->where(array('id'=>$id))->getField('name');
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
		$this->display();	
	}
	/*
		秒杀详情
	*/
	public function seckill_show(){
		$db=M('goods');
		$id=I('get.id');
		$info=$db->where(array('id'=>$id))->find();
		if($info['is_seckill']!=1){
			$this->error('该商品暂时不能秒杀');
		}else{
			$this->assign('info',$info);
			$this->display();
		}
	}
	
	/*
		商品详情页
	*/
	public function product(){
		$id=I('get.id');
		$db=M('goods');
		//增加人气
		$db->where(array('id'=>$id))->setInc('hits',1);
		$info=$db->find($id);
		if(empty($info)){
			$this->error('该商品不存在或已下架',U('product_list'));
		}
		$this->assign('info',$info);
		$param=I('get.param');
		
		if($param=='comment'){
			import('@.ORG.Page');
			$map=array('goods_id'=>$id);
			$count = M('goods_comment')->where($map)->count();
			$Page = new Page($count,20);
			$Page->setConfig('prev', '上一页');
			$Page->setConfig('next', '下一页');
			$Page->setConfig('theme',"%upPage% %downPage%");
			$page = $Page->show();
			$this->assign('page',$page);
			
			$tpl="product_comment";		//评论
			$comment_list=M('goods_comment')->where($map)->order('id desc')->limit($Page->firstRow.','.$Page->listRows)->select();
			$this->assign('comment_list',$comment_list);
			
		}elseif($param=='param'){
			$tpl="product_param";		//技术参数
		}elseif($param=='after_sale'){
			$buy_notes=M('cms_article')->where(array('id'=>1))->getField('content');
			$this->assign('buy_notes',$buy_notes);
			$tpl="product_after_sale";		//售后服务
		}else{
			$tpl="product";				//详细介绍
		}
		
		//商品评价
		$reply_list=self::reply_list($info['id']);
		$this->assign('reply_list',$reply_list);
		
		//商品可选规格
		$norm_list=M('goods_norm')->where(array('gid'=>$info['id']))->select();
		$this->assign('norm_list',$norm_list);
		
		if($info['sid']>0){
			$shop=M('shop')->where(array('id'=>$info['sid']))->find();
			if($shop['shop_status']==0){
				$this->error('很抱歉，该店铺已关闭，暂时不能购买相关产品！');
				die();
			}
			$this->assign('shop',$shop);
		}
		
		$this->display($tpl);
	}
	
	
	/*
		商品评论列表
	*/
	public function reply_list($gid){
		$db=M('goods_reply');
		$list=$db->where(array('gid'=>$gid))->order('id desc')->limit(6)->select();
		return $list;
	}
	
	
	public function cart0(){
		$this->display();
	}
	
	/*
		购物车
	*/
	public function cart(){

		$list=session('shop_cart_info');
		if(empty($list)){
			$this->redirect('cart0');
		}
		if(count($list)>0){			
			foreach($list as $key=>$val){
				//订单总价
				$total_price+=$val['goods_price']*$val['goods_nums'];
				
				$info=M('goods')->where(array('id'=>$val['goods_id']))->find();
				$list[$key]['name']=$info['name'];
				$list[$key]['spic']=$info['spic'];
				//首件商品邮费
				$list[$key]['express_price']=$info['express_price'];	
				//加购邮费	
				$list[$key]['express_price1']=$info['express_price1'];
				//免费件数	
				$list[$key]['express_free_nums']=$info['express_free_nums'];
				//$list[$key]['store_num']=$info['store_num'];
				//规格信息
				$norm=M('goods_norm')->where(array('id'=>$val['goods_norm']))->find();
				$list[$key]['norm']=$norm;
				
				unset($norm);
				unset($info);
			}
			
			foreach($list as $key=>$val){
				$goods_list[$val['goods_id']]['goods_id']=$val['goods_id'];
				$goods_list[$val['goods_id']]['goods_nums']+=$val['goods_nums'];
				$goods_list[$val['goods_id']]['express_price']=$val['express_price'];
				$goods_list[$val['goods_id']]['express_price1']=$val['express_price1'];
				$goods_list[$val['goods_id']]['express_free_nums']=$val['express_free_nums'];
			}
			//总邮费
			$express_fee=0;
			foreach($goods_list as $key=>$val){
				//计算邮费
				if($val['goods_nums']<$val['express_free_nums']){
					$express_fee+=$val['express_price'];
					$express_fee+=($val['goods_nums']-1)*$val['express_price1'];
				}
			}
			
			
			$total_fee=$total_price+$express_fee;	//订单总金额【商品价格+运费】
			$this->assign('total_price',$total_price);		//商品总金额【商品原始价格】
			$this->assign('express_fee',$express_fee);		//邮费总金额	
			$this->assign('total_fee',$total_fee);		
			$this->assign('list',$list);
			$tpl="cart";
		}else{
			$tpl="cart_empty";
		}
		
		
		//收货地址
		$addr_list=M('user_address')->where(array('user_id'=>$this->user_id))->select();
		$this->assign('addr_list',$addr_list);
		$this->display();
	}
	
	
	
	/*
		cart2
	*/
	public function cart2(){
		session_start();
		$list=session('shop_cart_info');				//购物车列表
		
		if(empty($list)){
			$this->redirect('cart0');
		}
		
		$total_money_cloud=0;							//可使用云金币总数
		
		if(count($list)>0){
			foreach($list as $key=>$val){
				$total_price+=$val['goods_price']*$val['goods_nums'];
				$info=M('goods')->find($val['goods_id']);
				$list[$key]['name']=$info['name'];
				$list[$key]['spic']=$info['spic'];
				$list[$key]['money_cloud']=$info['money_cloud'];
				$sid=$info['sid'];
				$this->assign('sid',$sid);
				
				$total_money_cloud+=$val['goods_nums']*$info['money_cloud'];	
				//$list[$key]['store_num']=$info['store_num'];
				
				//邮费计算方式
				$list[$key]['express_price_count_way']=$info['express_price_count_way'];
				
				if($info['express_price_count_way']==1){			//按件计费
					//首件商品邮费
					$list[$key]['express_price']=$info['express_price'];	
					//加购邮费	
					$list[$key]['express_price1']=$info['express_price1'];
					//免费件数	
					$list[$key]['express_free_nums']=$info['express_free_nums'];
					
				}elseif($info['express_price_count_way']==2){			//按重量计费
					//首重商品邮费
					$list[$key]['express_price']=$info['express_weight_price'];	
					//续重邮费	
					$list[$key]['express_price1']=$info['express_weight_price1'];
					//单品重量
					$list[$key]['weight']=$info['weight'];					
				}
				
				
				if(!empty($val['goods_norm'])){
					//规格信息
					$norm=M('goods_norm')->where(array('id'=>$val['goods_norm']))->find();
					$list[$key]['norm']=$norm;
				}
				unset($info);
			}
			
			
			foreach($list as $key=>$val){
				$goods_list[$key]['goods_id']=$val['goods_id'];
				$goods_list[$key]['goods_nums']+=$val['goods_nums'];
				$goods_list[$key]['express_price']=$val['express_price'];
				$goods_list[$key]['express_price1']=$val['express_price1'];
				$goods_list[$key]['express_free_nums']=$val['express_free_nums'];
				$goods_list[$key]['weight']=$val['weight'];	
				$goods_list[$key]['express_price_count_way']=$val['express_price_count_way'];		
			}
			//总邮费
			$express_fee=0;
			foreach($goods_list as $key=>$val){
				
				//计算邮费
				if($val['express_price_count_way']==1){				//按件计费
					if($val['goods_nums']<$val['express_free_nums']||$val['express_free_nums']==0){
						$express_fee+=$val['express_price'];
						$express_fee+=($val['goods_nums']-1)*$val['express_price1'];
						//当前商品邮费
						$_SESSION['shop_cart_info'][$key]['express_fee']=$val['express_price']+($val['goods_nums']-1)*$val['express_price1'];
					}
				}elseif($val['express_price_count_way']==2){		//按重量计费
				
					//总重量
					$total_weight=$val['weight']*$val['goods_nums'];
					if($total_weight<=1){
						$express_fee+=$val['express_price'];
						//当前商品邮费
						$_SESSOIN['shop_cart_info'][$key]['express_fee']=$val['express_price'];
					}else{
						$express_fee+=$val['express_price'];
						$express_fee+=ceil($total_weight-1)*$val['express_price1'];			//进一法
						
						//当前商品邮费
						$_SESSOIN['shop_cart_info'][$key]['express_fee']=$val['express_price']+ceil($total_weight-1)*$val['express_price1'];
					}
					
				}
				
			}
			
			$total_fee=$total_price+$express_fee;
			$this->assign('express_fee',$express_fee);		//邮费
			$this->assign('total_price',$total_price);		//商品总金额【商品原始价格】
			$this->assign('total_fee',$total_fee);			//订单总金额【+邮费】
			$this->assign('list',$list);
		}
		//收货地址
		$addr_list=M('user_address')->where(array('uid'=>$this->user_id))->select();
		$this->assign('addr_list',$addr_list);
		
		//配送地址
		$addr['province']=cookie('province');
		$addr['city']=cookie('city');
		$addr['district']=cookie('district');
		$this->assign('addr',$addr);
		//优惠券
		$coupon=M('coupon')->where(array('uid'=>$this->user_id,'status'=>0,'amount'=>array('lt',$total_fee)))->select();
		$this->assign('coupon',$coupon);
		
		//可使用云金币总数
		$this->assign('total_money_cloud',$total_money_cloud);
		
		$this->display();	
	}
	
	
	/*
		商品分类页
	*/
	public function category(){
		$db=M('goods_category');
		$map=array('is_show'=>1,'fup'=>0);
		//一级分类
		$plist=$db->where($map)->order('list asc')->select();
		foreach($plist as $key=>$val){
			$plist[$key]['child']=$db->where(array('fup'=>$val['id']))->count();
		}
		unset($map['fup']);
		
		$this->assign('plist',$plist);
		if($fup=I('get.fup',1)){
			$map['fup']=$fup;
		}
		$this->assign('fup',$fup);				//当前顶级分类
		$list=$db->where($map)->select();
		foreach($list as $key=>$val){
			$child=$db->where(array('is_show'=>1,'fup'=>$val['id']))->select();
			foreach($child as $kk=>$vv){
				$_child=$db->where(array('is_show'=>1,'fup'=>$vv['id']))->select();
				$child[$kk]['child']=$_child;
				unset($_child);
			}
			$list[$key]['child']=$child;
			unset($child);
		}
		$this->assign('list',$list);
		$this->display();
	}
	
	/*
		积分商城
	*/
	public function jifen_product_list(){
		$db=M('jifen_goods');
		$list=$db->where()->order('id desc')->select();
		$this->assign('list',$list);
		$this->display();
	}
	/*
		积分商品详情
	*/
	public function jifen_product(){
		$db=M('jifen_goods');
		$id=I('get.id');
		$info=$db->find($id);
		$this->assign('info',$info);
		$this->display();		
	} 
	
	/*
		积分兑换
	*/
	public function jifen_cart(){
		$db=M('jifen_goods');
		$id=I('get.id');
		$info=$db->find($id);
		$this->assign('info',$info);
		//收货地址
		$addr_list=M('user_address')->where(array('user_id'=>$this->user_id))->select();
		$this->assign('addr_list',$addr_list);
		$this->display();		
	}
	
	
	
	
	/*
		个人微店
	*/
	public function wshop(){
		import('@.ORG.Page');
		$db=M('goods');
		$id=I('get.id');
		if(!$id){
			$this->error('参数错误');
		}
		$shop=M('wechat_user')->where(array('id'=>$id))->find();
		if(empty($shop)){
			$this->error('微店不存在');
		}
		$this->assign('shop',$shop);
		
		//我的推荐列表
		$_list=M('goods_recommend')->where(array('uid'=>$id))->select();
		foreach($_list as $key=>$val){
			$tui_list[]=$val['goods_id'];
		}
		
		$map=array('id'=>array('in',$tui_list));
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
		pc订单支付中转页面
	*/
	public function pc_order_pay(){
		$order_id=I('get.id');
		//header('location:http://yd.wdkl168.com/wxpay/index.php?order_id='.$order_id);
		redirect('http://yd.wdkl168.com/wxpay/index.php?order_id='.$order_id);
	}
	
	
	/*
		账号禁用提示
	*/
	public function err_tips(){
		$this->display();
	}
	
	
}