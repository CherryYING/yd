<?php
/*
	账户信息管理
*/
class UserAction extends PublicAction{
	/*
		修改密码
	*/
	public function pwd(){
		$this->display();
	}
	public function pwd_update(){
		$db=M('wechat_user');
		if($this->_post()){
			$pwd=md5(I('post.password'));
			$db->where(array('id'=>$this->resaler_id))->save(array('password'=>$pwd));
			$this->success('密码修改成功！',U('pwd'));
		}
	}
	/*
		编辑账户信息
	*/
	public function edit(){
		$db=M('wechat_user');
		$id=I('session.resaler_id');
		$info=$db->find($id);
		
		$this->assign('info',$info);
		if($arr=$this->_post()){
			$db->where(array('id'=>$id))->save($arr);
			$this->success('保存成功',U('edit'));
		}else{
			$this->display();
		}
	}	


}