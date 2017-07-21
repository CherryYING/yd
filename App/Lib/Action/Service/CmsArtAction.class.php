<?php
class CmsArtAction extends PublicAction{
	public function artlist(){
		import("@.ORG.Page");
		$where=array('1=1');
		!isset($_GET['fid'])||$where['fid']=I('get.fid');
		if(!$_GET['mid']>0){
			$addtpl='art';
			$db=M('cms_article');
			$count = $db->where($where)->count();
			$Page = new Page($count,20);		
			$plist = $db->where($where)->order('id DESC')->limit($Page->firstRow.','.$Page->listRows)->select();
		}else{
			$addtpl=M('cms_formmod')->where('id='.I('get.mid'))->getField('name');
			$db=D('Art'.$addtpl);
			$count = $db->relation(true)->where($where)->count();
			$Page = new Page($count,20);		
			$plist = $db->relation(true)->where($where)->order('id DESC')->limit($Page->firstRow.','.$Page->listRows)->select();
		}
		//$db = M('cms_article');
		
		$show = $Page->show();
		$this->assign('show',$show);
		$this->assign('plist',$plist);
		$this->display($addtpl.'list');    
	}

	public function artdel(){
		if(!$_GET['mid']>0){
			$addtpl='art';
			$db=M('cms_article');
			$db->delete(I('get.id'));
		}else{
			$addtpl=M('cms_formmod')->where('id='.I('get.mid'))->getField('name');
			$db=D('Art'.$addtpl);
			$db->relation(true)->delete(I('get.id'));
		}
		
		redirect(U('artlist',array('fid'=>I('get.fid'),'mid'=>I('get.mid'))));
	}

	public function artadd(){
		!$_GET['mid']>0?$addtpl='art':$addtpl=M('cms_formmod')->where('id='.I('get.mid'))->getField('name');
		if(isset($_GET['id'])){
			!$_GET['mid']>0?$nrs=M('cms_article')->find(I('get.id')):$nrs=D('Art'.$addtpl)->relation(true)->find(I('get.id'));
			!$nrs['cms_form_house']['h_piclist']||$nrs['piclist']=unserialize($nrs['cms_form_house']['h_piclist']);
			if($nrs['fid']==23){	//团队相册
				$nrs['picurl']=unserialize($nrs['picurl']);
			}
			//var_dump($nrs['piclist']);
			$this->assign('nrs',$nrs);
			
		}
		$sort=M('cms_sort')->where('type=1')->select();
		$this->assign('sort',$sort);
		$this->display($addtpl.'add');
	}
	
	public function artaddhandle(){
		if(!$_GET['mid']>0){
			if(array_filter($_FILES['spic']['name'])){
				$data['spic']=$this->getpic('spic');
			}else{
				$data['spic']=I('post.spic');
			}
			$db=M('cms_article');
			unset($_FILES['spic']['name']);
		}else{
			$addtpl=M('cms_formmod')->where('id='.I('get.mid'))->getField('name');
			$db=D('Art'.$addtpl);
		}
		
		if(array_filter($_FILES['files']['name'])){
			import('ORG.Net.UploadFile');
			$upload = new UploadFile();// 实例化上传类
			$upload->maxSize  = 1024*1024*2 ;// 设置附件上传大小
			$upload->savePath =  './Data/upload/files/'.date('Ymd',time()).'/';// 设置附件上传目录
			if(!$upload->upload()) {// 上传错误提示错误信息
				$this->error($upload->getErrorMsg());
			}
			$file_info=$upload->getUploadFileInfo();
			$data['picurl']=substr($file_info[0]['savepath'].$file_info[0]['savename'],1);
		}

		$data['istop']=I('post.istop',0);
		$data['istui']=I('post.istui',0);
		$data['iswx']=I('post.iswx',0);
		if(empty($_GET['id'])){
			$data['uid']=I('session.uid');
			$data['uname']=I('session.username');
			$data['author']=I('post.author');
		}
		$data['posttime']=time();
		$data['title']=I('post.title');
		$data['fid']=I('post.fid');
		$data['mid']=I('get.mid');
		$data['title']=I('post.title');
		$data['lists']=I('post.lists',1);
		$data['mvurl']=I('post.mvurl');
		$data['descrip']=I('post.descrip');
		$data['content']=I('post.content');
		$data['cms_form_house']=array(
				'h_kaifashang'=>I('post.h_kaifashang'),
				'h_lpname'=>I('post.h_lpname'),
				'h_tgtime'=>I('post.h_tgtime'),
				'h_address'=>I('post.h_address'),
				'h_kptime'=>I('post.h_kptime'),
				'h_kftime'=>I('post.h_kftime'),
				'h_wytype'=>I('post.h_wytype'),
				'h_area'=>I('post.h_area'),
				'h_selladdress'=>I('post.h_selladdress'),
				'h_kaifashang'=>I('post.h_kaifashang'),
				'h_selltel'=>I('post.h_selltel'),
				'h_piclist'=>serialize(array_filter(I('post.pic')))
			);
		$data['fname']=M('cms_sort')->where(array('id'=>$_POST['fid']))->getField('name');
		!$_GET['id']?!$_GET['mid']>0?$db->add($data):$db->relation(true)->add($data):!$_GET['mid']>0?$db->where(array('id'=>I('get.id')))->save($data):$db->relation(true)->where(array('id'=>I('get.id')))->save($data);
		redirect(U('artlist',array('fid'=>I('post.fid'),'mid'=>I('get.mid'))));
	}
	
	public function formlist(){
		$id=I('get.id');
		$form=M('cms_form_house_reply')->where('aid='.$id)->select();
		$this->assign('formlist',$form);
		$this->display();
	}

	public function uppic(){
		if(array_filter($_FILES['pic']['name'])){
			$picurl=$this->getpic('pic');
			$time=time();
		}
		echo '<textarea><span id="'.$time.'" class="imgDiv" style="float:left"><input type="hidden" name="pic[]" value="'.$picurl.'"><img src="'.$picurl.'" width=100 height=100><br><a href=javascript:del("'.$time.'");>删除</span></textarea>';

	}

	private function getpic($input){	
		$savePath = "./Data/upload/thumb/";
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
     return "/Data/upload/thumb/".$pic[0]["saveName"];
	}		
}