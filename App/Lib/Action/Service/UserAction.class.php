<?php
/*
	账户信息
*/
class UserAction extends PublicAction{
	
	//用户列表
	public function index(){
			
		$this->display();
	}
	/*
		服务商信息
	*/
	public function info(){
		$db=M('wechat_user');
		$info=$db->where(array('id'=>$this->service_id))->find();
		$this->assign('info',$info);
		if($arr=$this->_post()){
			$db->where(array('id'=>$this->service_id))->save($arr);
			$this->success('修改成功',U('info'));
		}else{
			$this->display();
		}
		
	}
	
	/*
		资金账户
	*/
	public function fund(){
		import("@.ORG.Page");
		
		$db=M('wechat_user');
		$info=$db->where(array('id'=>$this->service_id))->find();
		$this->assign('info',$info);
		
		//资金明细
		$map=array('uid'=>$info['id']);
		$count = M('money_water')->where($map)->count();
		$Page = new Page($count,10);
		$money_list=M('money_water')->where($map)->order('id DESC')->limit($Page->firstRow.','.$Page->listRows)->select();
		foreach($money_list as $key=>$val){
			if(!empty($val['order_id'])){
				$money_list[$key]['order']=M('order_info')->where(array('id'=>$val['order_id']))->find();
			}
		}
		$show = $Page->show();
		$this->assign('show',$show);
		$this->assign('money_list',$money_list);
		
		$this->display();
	}
	
	
	/*
		修改密码
	*/
	public function pwd(){
		$this->display();
	}
	public function pwd_update(){
		$db=M('service');
		if($arr=$this->_post()){
			if(!empty($arr['password'])){

				$arr['password']=md5($arr['password']);
				
				$db->where(array('id'=>$this->service_id))->save($arr);
			}
			$this->success('登录密码修改成功',U('pwd'));
		}
	}
	
	/*
		店铺信息
	*/
	public function shop(){
		$db=M('shop');
		$info=$db->where(array('id'=>$this->service_id))->find();
		if($arr=$this->_post()){
			if(empty($info)){
				$arr['id']=$this->service_id;
				$arr['posttime']=time();
				$db->add($arr);
			}else{
				$db->where(array('id'=>$this->service_id))->save($arr);
			}
			$this->success('保存成功');
		}else{
			$this->assign('info',$info);
			$this->display();
		}
	}
	
	/*
		 服务区域
	*/
	public function area_list(){
		$db=M('service');
		$area_list=$db->where(array('id'=>I('session.service_id')))->getField('area_list');
		$area_list=array_filter(explode(',',$area_list));
		
		foreach($area_list as $key=>$val){
			$areas[]=M('region')->find($val);
		}
		
		
		foreach($areas as $key=>$val){
			if($val['region_type']==1){
				$pro[]=$val;
				unset($areas[$key]);
			}
			
		}
		
		foreach($areas as $key=>$val){
			if($val['region_type']==2){
				$city[]=$val;
				unset($areas[$key]);
			}
			
		}
		
		foreach($city as $key=>$val){
			foreach($areas as $k=>$v){
				if($v['parent_id']==$val['id']){
					$city[$key]['county'][]=$v;
				}
			}
			
		}
		
		
		foreach($pro as $key=>$val){
			foreach($city as $k=>$v){
				if($v['parent_id']==$val['id']){
					$pro[$key]['city'][]=$v;
				}
			}
			
		}
		$this->assign('areas',$pro);
		$this->display();
	}
}