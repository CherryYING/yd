<?php
//订单管理
class OrderAction extends PublicAction{
	public $sid;		//供货商uid
	public function _initialize(){
		parent::_initialize();
		$this->sid=I('session.service_id');		
	}
	public function index(){
		import("@.ORG.Page");
		$db=M('order_goods');
		
		$map['sid']=$this->sid;
		
		if(isset($_GET['order_status'])){
			$order_status=I('get.order_status');
			$map['order_status']=$order_status;
		}
		
		$so_key=I('get.key');
		$so_val=I('get.val');
		$begin_time=strtotime(I('get.begin_time'));
		$end_time=strtotime(I('get.end_time'));

		
		if(in_array($so_key,array('out_trade_no','mobile','consignee'))){
			if(!empty($so_key)&&!empty($so_val)){
				$where[$so_key]=array('like','%'.$so_val.'%');
			}
		}
		
		
		if($end_time>0&&$begin_time>0){
			$map['order_time']=array('between',array($begin_time,$end_time));
		}
		
		$order_list=M('order_info')->where($where)->select();
		foreach($order_list as $val){
			$oid_arr[]=$val['id'];
		}
		
		$map['order_id']=array('in',$oid_arr);				
		
		$count = $db->where($map)->count();
		$Page = new Page($count,10);
		
		$list=$db->where($map)->order('id desc')->limit($Page->firstRow.','.$Page->listRows)->select();
		
		foreach($list as $key=>$val){
			$list[$key]['order']=M('order_info')->where(array('id'=>$val['order_id']))->find();
		}
		
		$show = $Page->show();

		$this->assign('show',$show);
		$this->assign('list',$list);
		$this->display();
	}
	
	/*
		订单商品详情
	*/
	public function edit(){
		$db=M('order_goods');
		$id=I('get.id');
		$goods=$db->where(array('id'=>$id))->find();	
		$order=M('order_info')->where(array('id'=>$goods['order_id']))->find();
		$this->assign('goods',$goods);
		$this->assign('order',$order);
		$this->display();
	}
	
	
	//增加
	public function add(){
		$db=M('order_info');
		if(IS_POST){
			$data=$this->_post();
			$db->add($data);
			$this->redirect('index');
		}
		$this->display();
	}
	
	
	
	
	//删除
	public function del(){
            if($id=I('get.id')){
                M('order_info')->where(array('id'=>$id))->delete();
                M('order_goods')->where(array('order_id'=>$id))->delete();
                $this->redirect('index');
            }
	}  
	
	
	/*
		退款申请
	*/
	public function refund_list(){
		import("@.ORG.Page");
		$db=M('order_refund');
		
		$map['sid']=$this->sid;
		$count = $db->where($map)->count();
		$Page = new Page($count,10);
		$show=$Page->show();
		$this->assign('show',$show);
		
		$list=$db->where($map)->order('id desc')->limit($Page->firstRow.','.$Page->listRows)->select();
		
		foreach($list as $key=>$val){
			$list[$key]['order']=M('order_info')->find($val['order_id']);
			$list[$key]['goods']=M('order_goods')->where(array('order_id'=>$val['order_id'],'goods_id'=>$val['goods_id']))->find();
		}
		
		$this->assign('list',$list);
		$this->display();
		
	}
	
	/*
		退款详情
	*/
	public function refund_detail(){
		$db=M('order_refund');
		$id=I('get.id');
		//退款申请
		$info=$db->where(array('id'=>$id))->find();
		if($info['sid']!=$this->sid){
			$this->error('无权操作');
		}
		$this->assign('info',$info);
		//订单信息
		$data=M('order_info')->where(array('id'=>$info['order_id']))->find();
		
		//下单人
		$user=M('wechat_user')->where(array('id'=>$info['uid']))->find();
		$this->assign('user',$user);
		//商品信息
		$order_goods=M('order_goods')->where(array('order_id'=>$info['order_id'],'goods_id'=>$info['goods_id']))->select();
		
		//店主信息
		$shop=M('shop')->where(array('id'=>$order_goods[0]['sid']))->find();
		
		$this->assign('order_goods',$order_goods);
		
		$this->assign('info',$info);
		
		$this->assign('data',$data);
		
		$this->assign('shop',$shop);
		
		$this->display();
	}
	
}