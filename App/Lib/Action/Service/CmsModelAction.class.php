<?php 
class CmsModelAction extends PublicAction{
		public function index(){
			import("@.ORG.Page");
			$db = M('cms_formmod');
			$count = $db->count();
			$Page = new Page($count,20);
			$plist = $db->limit($Page->firstRow.','.$Page->listRows)->select();
			$show = $Page->show();
			$this->assign('show',$show);
			$this->assign('plist',$plist);
			$this->display();    
		}
		//模型删除
		public function delModel(){
			M('cms_formmod')->delete(I('get.id'));
			redirect(U('index'));
		}
		//模型添加修改
		public function addModel(){
			if(isset($_GET['id'])){
				$nrs=M('cms_formmod')->find(I('get.id'));
				$this->assign('nrs',$nrs);
			}
			$this->display();
		}

		//模型添加修改句柄
		public function addModelhandle(){
			$db=M('cms_formmod');
			$data=$_POST;
			!$_GET['id']?$db->add($data):$db->where(array('id'=>I('get.id')))->save($data);
			redirect(U('index'));
		}

		public function confModel(){

			$this->display();
		}
		public function confModelhandle(){
			$tb=M('cms_formmod')->where('id='.I('get.id'))->getField('name');
			$sql="create table twotree_cms_form_".$tb." (id mediumint(9) NOT NULL AUTO_INCREMENT,aid mediumint(9) DEFAULT NULL";
			foreach ($_POST['name'] as $key => $value) {
				$sql.=",".$value." ".$_POST['type'][$key];
				$_POST['type'][$key]=='text'||$sql.="(".$_POST['size'][$key].")";
			}
			$sql.=",PRIMARY KEY (id)) ENGINE=MyISAM DEFAULT CHARSET=utf8;";
			$db=M();
			$db->execute($sql);
			redirect(U('index'));
		}		
}