<?php
class RBACAction extends PublicAction{
	//用户列表
	public function index(){
		import('@.ORG.Page');
		$count = D('UserRelation')->where()->count();
		$Page = new Page($count,10);	
		$pagestr = $Page->show();
		$this->assign('pagestr',$pagestr);
		$res=D('UserRelation')->field('password',true)->relation(true)->limit($Page->firstRow.','.$Page->listRows)->select();
		$this->assign('list',$res);
		$this->display();
	}
	//用户锁定处理
	public function indexlocked(){
		$id=$_GET['id'];
			//锁定用户
			if($id==1){
				$this->error('对不起，您不能对此用户做任何操作！');
			}else{
				$i=M('User')->where(array('id'=>$id))->setField('lock','1');
				$this->success('用户已关闭！');
				//$this->ajaxReturn('','用户已关闭！',1);
			}
	}
	//用户解锁处理
	public function indexlock(){
		$id=$_GET['id'];
		//解锁用户
			if($id==1){
				$this->error('对不起，您不能对此用户做任何操作！');
			}else{
				$i=M('User')->where(array('id'=>$id))->setField('lock','0');
				$this->success('用户已开启！');
			}
	}
	//角色列表
	public function role(){
		$role = M('role')->select();
		$this->assign('role',$role);
		$this->display();
	}
	//节点列表
	public function node(){
		$field=array('id','name','title','pid');
		$node = M('node')->field($field)->order('id desc')->select();
		$node=node_merge($node);
		$this->assign('node',$node);
		$this->display();
	}
	//添加用户
	public function addUser(){
		$role=M('role')->select();
		$this->assign('role',$role);
		$this->display();
	}
	public function editUser(){
		$role=M('role')->select();
		$id=I('get.id');
		$urid=M('role_user')->where(array('user_id'=>$id))->getField('role_id');
		$udb=M('user')->find($id);
		
		$udb['urid']=$urid;
		$this->assign('urs',$udb);
		$this->assign('role',$role);
		$this->display();
	}	
	public function editUserHandle(){
		$id=I('post.id');
		$urid=I('post.role_id');
		$data['id']=$id;
		!$_POST['password']||$data['password']=md5(I('post.password'));
		$data['lock']=I('post.lock');
		$data['role_id']=I('post.role_id');
		if(M('user')->save($data)){
			M('role_user')->where(array('user_id'=>$id))->save(array('role_id'=>$urid));
		}
		$this->success('修改账户成功');
	}
	public function delUser(){
		$id=I('get.id');
		if(M('user')->delete($id)){
			$this->success('删除用户成功',U('index'));
		}else{
			$this->success('删除用户失败',U('index'));
		}
	}
	//添加用户表单接受
	public function addUserHandle(){
		//判断注册用户是否存在
		$w=htmlspecialchars(trim($_POST['username']));
		$i=M('user')->where(array('username'=>$w))->select();
		if($i!=''){
			$this->error('用户名已存在！');
		}else{
		//组合用户信息并添加
			$user=array(
			'username'=>htmlspecialchars(trim($_POST['username'])),
			'password'=>md5($_POST['password']),
			'logintime'=>time(),
			'loginip'=>get_client_ip(),
			'lock'=>$_POST['lock'],
			'role_id'=>$_POST['role_id'][0]
			);
			//添加用户与角色关系
			$role=array();
			if($uid=M('user')->add($user)){
				foreach($_POST['role_id'] as $v){
					$role[]=array(
						'role_id'=>$v,
						'user_id'=>$uid
					);
				}
				M('role_user')->addAll($role);
				$this->success('添加成功！',U('index'));
			}else{
				$this->error('添加失败！');
			}
		}
	}
	//添加角色
	public function addRole(){
		$this->display();
	}
	//编辑角色
	public function editRole(){
		$db=M('role');
		$id=I('get.id');
		$info=$db->find($id);
		$this->assign('info',$info);
		if($arr=$this->_post()){
			$db->where(array('id'=>$id))->save($arr);
			$this->redirect('role');
		}
		$this->display();
	}
	//删除角色
	public function delRole(){
		$id=I('get.id');
		M('role')->delete($id);
		$this->redirect('role');
	}
	//添加角色接受表单
	public function addRoleHandle(){
		if(M('Role')->add($_POST)){
			$this->success('-_- yes！',U('role'));
		}else{
			$this->error('-_-。sorry！');
		}
	}
	//添加节点
	public function addNode(){
		$pid=isset($_GET['pid'])?$_GET['pid']:0;
		$level=isset($_GET['level'])?$_GET['level']:1;
		$this->assign('pid',$pid);
		$this->assign('level',$level);
		switch($level){
			case 1:
				$this->type='应用';
				break;
			case 2:
				$this->type='控制器';
				break;
			case 3:
				$this->type='动作方法';
				break;
		}
		$this->display();
	}
	public function editNode(){
		$id=I('get.id');
		$nrs=M('node')->find($id);

		$this->assign('nrs',$nrs);
		switch($nrs['level']){
			case 1:
				$this->type='应用';
				break;
			case 2:
				$this->type='控制器';
				break;
			case 3:
				$this->type='动作方法';
				break;
		}
		$this->display();
	}	
	public function delNode(){
		$nid=I('get.id');
		if(M('node')->delete($nid)){
			//删除子节点
			M('node')->where(array('pid'=>$nid))->delete();	
			$this->success('删除节点成功',U('node'));
		}else{
			$this->success('删除节点失败',U('node'));
		}		
	}
	//添加节点接受表单
	public function addNodeHandle(){
		if(M('Node')->add($_POST)){
			$this->success('-_- yes！',U('node'));
		}else{
			$this->error('-_-。sorry！');
		}
	
	}
	public function editNodeHandle(){
		//$w['id']=I('post.id');
		if(M('Node')->save($_POST)){
			$this->success('-_- yes！',U('node'));
		}else{
			$this->error('-_-。sorry！');
		}
	
	}	
	//配置权限
	public function access(){
		$rid=$_GET['rid'];
		//读取有用字段
		$field=array('id','name','title','pid');
		$node=M('node')->order('sort')->field($field)->select();
		
		//读取用户原有权限
		$access=M('access')->where(array('role_id'=>$rid))->getField('node_id',true);
		$node=node_merge($node,$access);
		
	
		$this->assign('rid',$rid);
		$this->assign('node',$node);
		$this->display();
	
	}
	//配置权限接受表单
	public function setAccess(){
		$rid=$_POST['rid'];
		$db=M('access');
		//删除原权限
		$db->where(array('role_id' => $rid))->delete();
		//组合新权限
		$data=array();
		foreach($_POST['access'] as $v){
			$tmp=explode('_',$v);
			$data[]=array(
				'role_id'=>$rid,
				'node_id'=>$tmp[0],
				'level'=>$tmp[1]
			);
		}
		//插入新权限
		if($db->addAll($data)){
			$this->success('修改成功！',U('role'));
		}else{
			$this->error('修改失败！');
		}
	}
	
}