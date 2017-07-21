<?php
//服务商管理
class ServiceAction extends PublicAction{

	public function _initialize(){
		parent::_initialize();
	}
	/*
		服务商列表
	*/	
	public function index(){
		import("@.ORG.Page");
		$map=array();
		
		
		/*$so_key=I('post.key');
		$map['username']  = array('like', '%'.$so_key.'%');*/
		
		$so_key=I('get.key');
		$so_val=I('get.val');
		
		if(in_array($so_key,array('username','name'))){
			if(!empty($so_val)&&!empty($so_val)){
				$map[$so_key]=array('like','%'.$so_val.'%');
			}
		}
			
			
		$db=M('service');
		$count = $db->where($map)->count();
		$Page = new Page($count,10);

		$list=$db->where($map)->order('id DESC')->limit($Page->firstRow.','.$Page->listRows)->select();

		$show = $Page->show();
		$this->assign('show',$show);
		$this->assign('list',$list);
		$this->display();
	}
	
	/*
		新增
	*/
	public function add(){
		
		$this->display();
	}
	public function insert(){
		$db=M('service');
		if($arr=$this->_post()){
			$info=$db->where(array('username'=>$arr['username']))->find();
			if(!empty($info)){
				$this->error('账户名已存在，请重新输入账户名');
			}else{
				$arr['posttime']=time();
				$arr['password']=md5($arr['password']);
				$db->add($arr);
				$this->redirect('操作成功！',U('index'));
			}
			
		}
	}
	/*
		编辑服务商信息
	*/
	public function edit(){
		
		$id=I('get.id');
		$info=M('service')->find($id);
		$this->assign('info',$info);
		
		if($data=$this->_post()){
			$w['id']=I('get.id');
			M('service')->where($w)->save($data);
			$this->success('保存成功',U('edit',array('id'=>$w['id'])));
		}else{
			$this->display();
		}
	}
	/*
		删除服务商
	*/
	public function del(){
		$user_id=I('get.id');
		if(M('service')->delete($user_id)){
			$this->success('操作成功！');
		}
	}
	
	/*
		服务区域
	*/
	public function area_edit(){
		header("content-type:text/html;charset=utf-8");
		$db=M('region');
		$id=I('get.id');					//服务商id
		$province=$db->where(array('region_type'=>1))->select();
		foreach($province as $key=>$val){
			$city=$db->where(array('parent_id'=>$val['id']))->select();
			foreach($city as $k=>$v){
				$city[$k]['county']=$db->where(array('parent_id'=>$v['id']))->select();
			}
			$province[$key]['city']=$city;
			unset($city);
		}
		$this->assign('province',$province);
		if($arr=$this->_post()){
			$arr['area_list']=implode(',',$arr['area_list']);
			$arr['area_list'].=',';
			M('service')->where(array('id'=>$id))->save($arr);
			$this->redirect('index');
		}
		//已分配区域
		$info=M('service')->find($id);
		$info['area_list']=array_filter(explode(',',$info['area_list']));
		$this->assign('area_list',$info['area_list']);
		$this->display();
	}
	
	/*
		修改密码
	*/
	public function pwd(){
		$db=M('service');
		$id=I('get.id');
		$info=$db->find($id);
		$this->assign('info',$info);
		if($arr=$this->_post()){
			$data['password']=md5($arr['password']);
			$db->where(array('id'=>$id))->save($data);
			$this->success('密码修改成功！');
		}else{
			$this->display();	
		}
		
	}
	
	/*
		禁用账号
	*/
	public function lock(){
		$db=M('service');
		$id=I('get.id');
		$info=$db->find($id);
		if($info['lock']==0){
			$data=array('lock'=>1);
		}elseif($info['lock']==1){
			$data=array('lock'=>0);
		}
		$db->where(array('id'=>$id))->save($data);
		$this->success('操作成功！');
	}
}