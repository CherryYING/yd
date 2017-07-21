<?php
class IndexAction extends BaseAction{

	public function _initialize(){
		parent::_initialize();
		import('@.ORG.Page');
		
	}
	/*
		PC商城首页
	*/
	public function index(){
		$map=array('is_show'=>1,'is_tui'=>1);
		$class=M('goods_category')->where($map)->order('list asc')->select();
		foreach($class as $key=>$val){
			$where=array('cid'=>array('like','%,'.$val['id'].',%'),'is_sale'=>1);
			$class[$key]['goods']=M('goods')->where($where)->field('id,spic,name,price,market_price,sale_num,yongjin')->order('lists asc')->limit('4')->select();
			unset($where);
		}
		$this->assign('class',$class);
		
		//轮播图
		$slide=M('slide')->where(array('cid'=>2,'sid'=>0))->order('list asc')->select();
		$this->assign('slide',$slide);
		
		//商家列表
		$shop=M('shop')->where(array('shop_status'=>1,'shop_logo'=>array('neq','')))->select();
		$this->assign('shop',$shop);
		
		$this->display();
	}
	
	/*
		注册
	*/
	public function reg(){
		$this->display();
	}
	
	/*
		登录	
	*/
	public function login(){
		$db=M('wechat_user');
		$jump=I('get.jump');
		if(!empty($jump)){
			$jump=base64_decode($jump);
		}
		
		if($arr=$this->_post()){
			$user=$db->where(array('username'=>$arr['username']))->find();
			if(empty($user)){
				$this->error('用户名或密码错误');
			}else{
				
				if($user['status']==2){
					$this->error('账户异常，已被禁用，如有疑问请咨询平台管理员');	
				}else{
					
					if($user['password']==md5($arr['password'])){
						session('user_id',$user['id']);				//登录session
						if(empty($jump)){
							$this->redirect('Ucenter/index');
						}else{
							redirect($jump);
						}
						
					}else{
						$this->error('用户名或密码错误');
					}	
								
				}
				
			}
		}
		$this->display();
	}
	
	public function logout(){
		session('user_id',null);
		$this->redirect('index');
	}
	
	/*
		商品详情
	*/
	public function product(){
		$db=M('goods');
		$id=I('get.id');
		if(!$id){
			$this->error('参数错误');
		}
		$map=array('id'=>$id,'is_sale'=>1);	
		$info=$db->where($map)->find();
		
		if(empty($info)){
			$this->error('商品不存在或已下架');
		}
		$this->assign('info',$info);
		
		//商品可选规格
		$norm_list=M('goods_norm')->where(array('gid'=>$info['id']))->select();
		$this->assign('norm_list',$norm_list);
		
		//商家信息
		$shop=M('shop')->where(array('id'=>$info['sid']))->find();
		
		if(!empty($shop)){
			$this->assign('shop',$shop);
		}
		
		//推荐商品
		$tui_list=M('goods')->where(array('is_sale'=>1,'cid'=>array('like','%'.$info['cid'].'%')))->order('id desc,lists asc')->limit('8')->select();
		
		$this->assign('tui_list',$tui_list);
		
		$collect=M('goods_collect')->where(array('uid'=>$this->user_id))->select();
		foreach($collect as $val){
			$col_arr[]=$val['gid'];
		}
		$this->assign('col_arr',$col_arr);
		
		$this->display();
	}
	
	/*
		商品列表
	*/
	public function product_list(){
		$db=M('goods');
		$cid=I('get.cid');
		
		$map=array();
		
		if($cid){
			$map=array('cid'=>array('like','%,'.$cid.',%'));
		}
		
		if($keyword=I('get.keyword')){
			$map['name']=array('like','%'.$keyword.'%');
		}
		
		$count = $db->where($map)->count();
		$Page = new Page($count,15);
		$list = $db->where($map)->order('id desc')->limit($Page->firstRow.','.$Page->listRows)->select();
		$page = $Page->show();
		$this->assign('page',$page);
		$this->assign('list',$list);
		
		//推荐商品
		$tui_list=M('goods')->where(array('is_sale'=>1))->order('sale_num desc,hits desc')->limit('8')->select();
		
		$this->assign('tui_list',$tui_list);
		
		//轮播图
		$slide=M('slide')->where(array('cid'=>3,'sid'=>0))->order('list asc')->select();
		$this->assign('slide',json_encode($slide));
		
		$this->display();
		
	}
	
	/*
		所有店铺
	*/
	public function shop_list(){
		$db=M('shop');

		$map=array('shop_status'=>1);
		
		if($keyword=I('get.keyword')){
			$map['name']=array('like','%'.$keyword.'%');
		}
		
		$count = $db->where($map)->count();
		$Page = new Page($count,15);
		$list = $db->where($map)->order('id desc')->limit($Page->firstRow.','.$Page->listRows)->select();
		$page = $Page->show();
		$this->assign('page',$page);
		$this->assign('list',$list);
		
		//推荐商品
		$tui_list=M('goods')->where(array('is_sale'=>1))->order('sale_num desc,hits desc')->limit('8')->select();
		
		$this->assign('tui_list',$tui_list);
		
		//轮播图
		$slide=M('slide')->where(array('cid'=>3,'sid'=>0))->order('list asc')->select();
		$this->assign('slide',json_encode($slide));
		
		$this->display();
		
	}
	
	/*
		商家主页
	*/
	public function shop(){
		$db=M('goods');
		
		$id=I('get.id');				//店铺id
		
		$shop=M('shop')->where(array('id'=>$id))->find();
		
		$this->assign('shop',$shop);
		
		if($shop['shop_status']==0){
			$this->error('该店铺未审核');
		}
		
		$map=array('sid'=>$id);
		
		$count = $db->where($map)->count();
		$Page = new Page($count,15);
		$list = $db->where($map)->order('id desc')->limit($Page->firstRow.','.$Page->listRows)->select();
		$page = $Page->show();
		$this->assign('page',$page);
		$this->assign('list',$list);
		
		//推荐商品
		$tui_list=M('goods')->where(array('is_sale'=>1))->order('sale_num desc,hits desc')->limit('8')->select();
		
		$this->assign('tui_list',$tui_list);
		
		//轮播图
		$slide=M('slide')->where(array('cid'=>$id,'sid'=>$id))->order('list asc')->select();
		$this->assign('slide',json_encode($slide));
		
		$this->display();
		
	}
	
	/*
		购物车
	*/
	public function cart0(){
		$this->display();
	}
	
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
		//dump($count);
		//dump($list);
		//session('shop_cart_info',null);
	}
	/*
		cart2
	*/
	public function cart2(){
		
		$jump=get_curr_url();
		$jump=base64_encode($jump);
		
		$list=session('shop_cart_info');				//购物车列表
		
		if(empty($this->user_info)){
			$this->error('您还没有登录！',U('Index/login',array('jump'=>$jump)));	
		}
		
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
				$goods_list[$val['goods_id']]['goods_id']=$val['goods_id'];
				$goods_list[$val['goods_id']]['goods_nums']+=$val['goods_nums'];
				$goods_list[$val['goods_id']]['express_price']=$val['express_price'];
				$goods_list[$val['goods_id']]['express_price1']=$val['express_price1'];
				$goods_list[$val['goods_id']]['express_free_nums']=$val['express_free_nums'];
				$goods_list[$val['goods_id']]['weight']=$val['weight'];	
				$goods_list[$val['goods_id']]['express_price_count_way']=$val['express_price_count_way'];	
			}
			//总邮费
			$express_fee=0;
			foreach($goods_list as $key=>$val){
				
				//计算邮费
				if($val['express_price_count_way']==1){				//按件计费
					if($val['goods_nums']<$val['express_free_nums']||$val['express_free_nums']==0){
						$express_fee+=$val['express_price'];
						$express_fee+=($val['goods_nums']-1)*$val['express_price1'];
					}
				}elseif($val['express_price_count_way']==2){		//按重量计费
				
					//总重量
					$total_weight=$val['weight']*$val['goods_nums'];
					if($total_weight<=1){
						$express_fee+=$val['express_price'];
					}else{
						$express_fee+=$val['express_price'];
						$express_fee+=ceil($total_weight-1)*$val['express_price1'];			//进一法
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
}