<?php 
/*
	店铺管理
*/
class ShopAction extends PublicAction{
		public function _initialize(){
			parent::_initialize();
			$_level_list=M('user_level')->select();
			foreach($_level_list as $val){
				$level_list[$val['id']]=$val;
			}
			$this->assign('level_list',$level_list);
		}
		
		/*
			店铺列表
		*/
		public function index(){
			import("@.ORG.Page");
			$db=M('wechat_user');
			$map=array('role_id'=>array('neq',1));			//店铺
			$count = $db->where($map)->count();
			$Page = new Page($count,10);
			$show = $Page->show();
			$this->assign('show',$show);
			
			
			$list=$db->where($map)->order('id desc')->limit($Page->firstRow.','.$Page->listRows)->select();
			$this->assign('list',$list);
			$this->display();    
		}
		/*
			编辑店铺信息
		*/
		public function edit(){
			$db=M('wechat_user');
			$id=I('get.id');
			$info=$db->find($id);
			$this->assign('info',$info);
			if($arr=$this->_post()){
				$db->where(array('id'=>$id))->save($arr);
				$this->success('保存成功',U('edit',array('id'=>$id)));
			}else{
				$this->display();  
			}
			
		}
		
		/*
			待审核店铺列表
		*/
		public function audit(){
			import("@.ORG.Page");
			$db=M('wechat_user');
		
			$map=array('status'=>0,'role_id'=>array('neq',1));		//等待审核，店铺
			
			$count = $db->where($map)->count();
			$Page = new Page($count,10);
			$show = $Page->show();
			$this->assign('show',$show);
			
			
			$list=$db->where($map)->order('id desc')->limit($Page->firstRow.','.$Page->listRows)->select();
			$this->assign('list',$list);
			$this->display();    
		}
		/*
			审核店铺信息
		*/
		public function audit_pass(){
			$id=I('get.id');	
			M('wechat_user')->where(array('id'=>$id))->save(array('status'=>1));
			$this->redirect('audit');
		}
		
		/*
			店铺等级管理
		*/
		public function level(){
			import("@.ORG.Page");
			$db=M('shop_level');
			$map='';
			$count = $db->where($map)->count();
			$Page = new Page($count,10);
			$show = $Page->show();
			$this->assign('show',$show);
			$list=$db->where($map)->order('id desc')->limit($Page->firstRow.','.$Page->listRows)->select();
			$this->assign('list',$list);
			$this->display();    
		}
		/*
			新增店铺等级
		*/
		public function level_add(){
			$db=M('shop_level');
			if($arr=$this->_post()){
				$db->add($arr);
				$this->redirect('level');
			}
			$this->display();    
		}
		/*
			新增店铺等级
		*/
		public function level_edit(){
			$db=M('shop_level');
			$id=I('get.id');
			$info=$db->find($id);
			$this->assign('info',$info);
			if($arr=$this->_post()){
				$db->where(array('id'=>$id))->save($arr);
				$this->redirect('level');
			}
			$this->display();    
		}
		/*
			删除店铺等级
		*/
		public function level_del(){
			$id=I('get.id');
			M('shop_level')->delete($id);
			$this->redirect('level');
		}
		
		/*
			店铺主题风格管理
		*/
		public function theme(){
			$path=C('SHOP_THEME');
			$_list=scandir($path);
			foreach($_list as $key=>$val){
				if(!in_array($val,array('.','..'))&&is_dir($path.$val)){
					$list[$key]=scandir($path.$val);
				}else{
					unset($_list[$key]);
				}
			}
			foreach($list as $key=>$val){
				foreach($val as $k=>$v){
					if(in_array($v,array('.','..'))){
						unset($list[$key][$k]);
						unset($v);
					}else{
						$theme_list[$_list[$key]]['thumb']=$path.$_list[$key].'/'.$v;
					}
					
				}
			}
			/*dump($_list);
			dump($list);
			dump($theme_list);die();*/
			$this->assign('list',$theme_list);
			$this->display();
		}
		/*
			微店主题管理
		*/
		public function _theme(){
			$db=M('shop_theme');
			$list=$db->select();
			$this->assign('list',$list);
			$this->display();
		}
		/*
			添加主题
		*/
		public function theme_add(){
			$db=M('shop_theme');
			if($arr=$this->_post()){
				$db->add($arr);
				$this->success("添加主题成功！",U('theme'));
			}else{
				$this->display();
			}
		}
		/*
			添加主题
		*/
		public function theme_edit(){
			$db=M('shop_theme');
			$id=I('get.id');
			$info=$db->find($id);
			$this->assign('info',$info);
			if($arr=$this->_post()){
				$db->where(array('id'=>$id))->add($arr);
				$this->success("修改主题成功！",U('theme'));
			}else{
				$this->display();
			}
		}
		/*
			删除主题
		*/
		public function theme_del(){
			$db=M('shop_theme');
			$id=I('get.id');
			$db->delete($id);
			$this->success("删除成功！",U('theme'));
		}
		
	/*
		主题查看
	*/	
	public function theme_show(){
		$this->display();
	}
}