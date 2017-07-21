<?php
/*
这是后台控制器！
*/
class HouseAction extends PublicAction{
	public function index(){
		$u=session('username');
		$con=M('house')->select();
		// $db=M('wish');
		// $wish=$db->select();
		// $this->assign('con',$con);
		$this->assign('list',$con);

		$this->assign('name',$u);
		$this->display();
	
	}
	public function show(){
		$u=session('username');
		$id=$_GET['id'];
		$data['id']=$id;
		$con=M('house')->where($data)->find();
		// $db=M('wish');
		// $wish=$db->select();
		// $this->assign('con',$con);
		$this->assign('v',$con);

		$this->assign('name',$u);
		$this->display();		

	}
	public function add(){
		$u=session('username');
		$this->assign('name',$u);
		$this->display();	
	}
	public function addHouse(){
		if(M('House')->add($_POST)){
			$this->success('-_- 添加成功！',U('index'));
		}else{
			$this->error('-_-。sorry！');
		}
	}	
	//退出登录
	public function loginout(){
		session('username',null);
		session_unset();
		session_destroy();
		$this->redirect('Admin/Login/index');
	}


}


?>