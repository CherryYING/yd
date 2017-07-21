<?php
/*
	会员中心
*/
class UcenterAction extends BaseAction{

	public function _initialize(){
		parent::_initialize();
		import('@.ORG.Page');
		
		
		$jump=get_curr_url();
		$jump=base64_encode($jump);
		
		if(!$this->user_info||empty($this->user_info)){
			$this->redirect('Index/login',array('jump'=>$jump));
		}
	}
	/*
		个人中心
	*/
	public function index(){
		$this->redirect('order_list');
		/*$map=array('is_show'=>1,'is_tui'=>1);
		$class=M('goods_category')->where($map)->order('list asc')->select();
		foreach($class as $key=>$val){
			$where=array('cid'=>array('like','%,'.$val['id'].',%'),'is_sale'=>1);
			$class[$key]['goods']=M('goods')->where($where)->field('id,name,price,market_price,sale_num,yongjin')->order('lists asc')->limit('4')->select();
			unset($where);
		}
		$this->assign('class',$class);*/
		$this->display();
	}
	
	/*
		我的收藏
	*/
	public function collect(){
		$db=M('goods_collect');
		
		$map=array('uid'=>$this->user_id);
		
		$count = $db->where($map)->count();	
		$Page = new Page($count,15);
/*		*/
		$page = $Page->show();
		$this->assign('page',$page);
		
		$list=$db->where($map)->order('id desc')->limit($Page->firstRow.','.$Page->listRows)->select();
		$this->assign('list',$list);
		$this->display();		
	}
	
	/*
		订单列表
	*/
	public function order_list(){
		import('@.ORG.Page');
		$db=M('order_info');
		$map=array('uid'=>$this->user_id);
		
		$count = $db->where($map)->count();	//
		$Page = new Page($count,20);
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
		
		
		//删除订单金额为0的订单
		if($order['total_fee']<=0){
			$db->where(array('id'=>$order_id))->delete();
			M('order_goods')->where(array('order_id'=>$order_id))->delete();
		}
		
		if(empty($order)){
			$this->error('订单信息不存在',U('order_list'));
		}
		$order_goods=M('order_goods')->where(array('order_id'=>$order_id))->select();
		foreach($order_goods as $key=>$val){
			$order_goods[$key]['shop']=M('shop')->where(array('id'=>$val['sid']))->find();
		}
		
		//售后信息
		$refund_info=M('order_refund')->where(array('order_id'=>$order_id))->find();
		$this->assign('refund_info',$refund_info);
		
		//微信支付二维码
		import('@.ORG.QRcode');
		//$qrcode_val='http://'.I('server.HTTP_HOST').U("Weixin/Index/pc_order_pay",array('id'=>$order['id']));
		/*$qrcode_val='http://yd.wdkl168.com'.U("Weixin/Index/pc_order_pay",array('id'=>$order['id']));
		$dir='./Data/QR/order/'.date('Ymd',time()).'/';
		
		if(!is_dir($dir)){
			mkdir($dir);
		}
		
		$order['qrcode']=$qrcode_path=$dir.$order['id'].'.jpg';
		
		
		if(!is_file($qrcode_path)){
			QRcode::png($qrcode_val, $qrcode_path, 'L',8, 2); 			//生成图片
		}*/
		
		
		$this->assign('order',$order);
		$this->assign('order_goods',$order_goods);
		
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
		账户信息	
	*/
	public function info(){
		$db=M('wechat_user');
		if($arr=$this->_post()){
			$db->where(array('id'=>$this->user_id))->save($arr);
			$this->success('保存成功');	
		}else{
			$this->display();
		}
		
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
		$page = $Page->show();
		$this->assign('page',$page);
		
		$list=$db->where($map)->order('id desc')->limit($Page->firstRow.','.$Page->listRows)->select();
		$this->assign('list',$list);
		$this->display();
	}
	
	/*
		云金币
	*/
	public function money_cloud(){
		import('@.ORG.Page');
		$db=M('money_cloud_water');
		
		$map=array('uid'=>$this->user_id);
		
		$count =$db->where($map)->count();
		$Page = new Page($count,20);
		$page = $Page->show();
		$this->assign('page',$page);
		
		$list=$db->where($map)->order('id desc')->limit($Page->firstRow.','.$Page->listRows)->select();
		$this->assign('list',$list);
		$this->display();
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
		地址管理
	*/
	public function addr_list(){
		import('@.ORG.Page');
		$db=M('user_address');
		
		$map=array('uid'=>$this->user_id);
		
		$count = $db->where($map)->count();	//
		$Page = new Page($count,20);
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
	public function addr_edit(){
		$id=I('get.id');
		$db=M('user_address');
		$info=$db->find($id);
		$this->assign('info',$info);
		if($data=$this->_post()){
		   $db->where(array('id'=>$id))->save($data);
		   $this->redirect('addr_list');
		}
		$this->display();

	}
	/*
		新增编辑
	*/
	public function addr_add(){
		if($data=$this->_post()){
			$data['user_id']=$this->user_id;
			M('user_address')->add($data);
			$this->redirect('addr_list');
		}
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
		提现历史
	*/
	public function  take_money_list(){
		$db=M('take_money');
		$map=array('user_id'=>$this->user_id);
		
		$count = $db->where($map)->count();
		$Page = new Page($count,20);
		
		$page = $Page->show();
		$this->assign('page',$page);
		
		$list=$db->where($map)->order('id desc')->limit($Page->firstRow.','.$Page->listRows)->select();
		$this->assign('list',$list);
		$this->display();
	}
	
}