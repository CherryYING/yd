<?php
class AjaxAction extends Action{
	private $webinfo = array();
	private $wechatid;

	public function _initialize(){
		header("content-type:text/html;charset=utf-8");
        $agent = $_SERVER['HTTP_USER_AGENT']; 
        if(!strpos($agent,"MicroMessenger")) {
            //echo '此功能只能在微信浏览器中使用';exit;
        }
		$this->wechatid=I('get.wechatid');
		$this->webinfo=M('cms_config')->find(1);
		$this->assign('webinfo',$this->webinfo);
		$this->assign('wechatid',$wechatid);
	}
	
	/*
		删除收藏
	*/
	public function col_del(){
		$db=M('goods_collect');
		if($arr=$this->_post()){
			$db->where($arr)->delete();
		}
	}
	
	//图片上传
	public function upload(){
		import('@.ORG.Image.ThinkImage');
		$dir="./Data/upload/photo/".date('Ymd',time());
		if(!is_dir($dir)){
			mkdir($dir);
		}
		$return=array('flag'=>0,'msg'=>'','img'=>'');

		$extArr = array("jpg", "png", "gif");
		
		if(isset($_POST) and $_SERVER['REQUEST_METHOD'] == "POST"){
			$name = $_FILES['photoimg']['name'];
			$size = $_FILES['photoimg']['size'];
			
			if(empty($name)){
				$return['msg']='请选择要上传的图片!';
				echo json_encode($return);
				exit;
			}
			$ext=$this->extend($name);
			if(!in_array($ext,$extArr)){
				$return['msg']='图片格式错误!';
				echo json_encode($return);
				exit;
			}
			if($size>(100*1024*1024)){
				$return['msg']='图片大小不能超过100KB!';
				echo json_encode($return);
				exit;
			}
			$image_name = time().rand(100,999).".".$ext;
			$tmp = $_FILES['photoimg']['tmp_name'];
			if(move_uploaded_file($tmp, $dir.'/'.$image_name)){
				
				$return['flag']=1;
				$return['msg']='上传成功!';
				$return['img']=$dir.'/'.$image_name;
				//生成缩略图
				$thumb=$dir.'/thumb_'.$image_name;
				$img = new ThinkImage(THINKIMAGE_GD,$return['img']); 
        		$img->thumb(200,200,THINKIMAGE_THUMB_FIXED)->save($thumb);
				//入库
				$db=M('photo');
				$data=array('photo'=>$return['img'],'posttime'=>time(),'wechatid'=>$this->wechatid);
				$db->data($data)->add();
				echo json_encode($return);
				exit;
			}else{
				$return['msg']='上传失败';
				echo json_encode($return);
				exit;
			}
		}
	}
	public function prise(){
		if($_POST){
			$id=I('post.id');
			$db=M('photo');
			$rs=$db->where(array('id'=>$id))->setInc('prise',1);	
			if($rs){
				echo 1;
			}else{
				echo 0;		
			}
			exit();
		}
	}
	//图片异步加载
	public function photo_load(){
		$db=M('photo');
		if($_POST){
			$firstRow=I('post.offset');		//从第几条开始
			$listRows=8;		//每次加载条数
		}
		//limit($Page->firstRow.','.$Page->listRows)从那一条开始，加载多少条
		$list=$db->order('id desc')->limit($firstRow.','.$listRows)->select();
		foreach($list as $key=>$val){
			$list[$key]['nickname']=M('wechatuser')->where(array('wechatid'=>$this->wechatid))->getField('nickname');
			$list[$key]['thumb']=get_thumb($val['photo']);
		}
		echo json_encode($list);
		die();
	}
	private function extend($file_name){
		$extend = pathinfo($file_name);
		$extend = strtolower($extend["extension"]);
		return $extend;
	}
	//根据微信wechatid获取uid
	private function getUid($wechatid){
		$db=M('wechatuser');
		$uid=$db->where(array('wechatid'=>$wechatid))->getField();
		return $uid;
	}
	
}