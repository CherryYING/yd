<?php 
class CmsSortAction extends PublicAction{
		public function sortlist(){
			import("@.ORG.Page");
			$db = M('cms_sort');
			$count = $db->count();
			$Page = new Page($count,50);
			$plist = $db->order('id')->limit($Page->firstRow.','.$Page->listRows)->select();
			$show = $Page->show();
			//对栏目按子级顺序重排
			$plist=sarr($plist,0);
			$this->assign('show',$show);
			$this->assign('plist',$plist);
			$this->display();    
		}
		//栏目删除
		public function sortdel(){
			M('cms_sort')->delete(I('get.id'));
			redirect(U('sortlist'));
		}
		//栏目添加修改
		public function sortadd(){
			if(isset($_GET['id'])){
				$nrs=M('cms_sort')->find(I('get.id'));

				$this->assign('nrs',$nrs);
			}
			$formd=M('cms_formmod')->field('id,name,title')->select();
			$sortd=M('cms_sort')->where('type=0')->select();
			$this->assign('formd',$formd);
			$this->assign('sortd',$sortd);
			$this->display();
		}
		//处理单篇栏目的修改
		public function sortaloneedit(){
			$nrs=M('cms_sort')->find(I('get.id'));
			$sortd=M('cms_sort')->where('type=0')->select();
			$this->assign('nrs',$nrs);
			$this->assign('sortd',$sortd);
			$this->display();
		}
		//栏目添加修改句柄
		public function sortaddhandle(){
			$db=M('cms_sort');
			$data=$_POST;
			if(array_filter($_FILES['spic']['name'])){
				$data['spic']=$this->getpic('spic');
			}
			!$_GET['id']?$db->add($data):$db->where(array('id'=>I('get.id')))->save($data);
			redirect(U('sortlist'));
		}
		private function getpic($input){	
			$savePath = "./Data/upload/thumb/";
			$fileFormat = array('gif','jpg','jpeg','png','bmp');
			$maxSize = 0;
			$overwrite = 0;
			$thumb=1;
			$thumbWidth = 200;
			$thumbHeight = 200;	
			import('@.ORG.clsUpload');
			$picmodel=new clsUpload($savePath,$fileFormat,$maxSize,$overwrite);		
			$picmodel->setThumb($thumb,$thumbWidth,$thumbHeight);
			 if (!$picmodel->run($input,1)){
				 echo $picmodel->errmsg()."<br>\n";
			 }
			$pic = $picmodel->getInfo();
		 return "/Data/upload/thumb/".$pic[0]["saveName"];
		}		
}