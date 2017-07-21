<?php
/*
	这是后台控制器！
*/
class IndexAction extends PublicAction{
	public function index(){
		$this->redirect('Order/index');
		$this->display();
	}
	
	//基本信息编辑
	public function edit(){
		$user=M('user_data');
		$data=$user->where(array('id'=>1))->find();
		$this->assign('data',$data);
		if(array_filter($_FILES['spic']['name'])){
			$_data['icon']=$this->getpic('spic');
		}
		if(IS_POST){
			$_data['id']=1;
			$_data['role_id']=1;
			$_data['username']='admin';
			$_data['truename']=I('post.truename');
			$_data['address']=I('post.address');
			$_data['special']=I('post.special');
			$_data['jobtitle']=I('post.jobtitle');
			$_data['posttime']=time();
			//$res=$user->add($_data);
			$res=$user->where(array('id'=>1))->save($_data);
			if($res){
				$this->redirect(U('Index/edit'));	
			}
		}
		$this->display();
	}
	private function getpic($input){	
		$savePath = "./Data/upload/icon";
        // tcmkdir($savePath);
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
     return "/Data/upload/icon/".$pic[0]["saveName"];
	}	
	
	/*
		图片预览
	*/
	public function show_img(){
		$picurl=I('get.picurl','','base64_decode');
		echo "<img src='$picurl' style='width:500px'/>";
	}
	
	/*
		模板演示
	*/
	public function demo(){
		$this->display();
	}
}