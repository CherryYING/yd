<?php
//系统设置
class ConfAction extends PublicAction{
	public function index(){
		$con=M('cms_config')->find(1);
		$this->assign('conf',$con);
		$this->display();
	}
	
	public function editSlide(){
		$conf=M('cms_config')->field('web_url')->find(1);
                if(!empty($conf['web_url'])){
                   $silde=json_decode($conf['web_url'],true);
                    foreach($silde as $key=>$val){
                        $silde[$key]['pic']=$this->get_thumb($val['pic']);
                    }  
                }
		$this->assign('silde',$silde);
		$this->display();
	}
	
	public function slideHandle(){
		$db=M('cms_config');
		if($_POST){
			if($this->_post('picurl')){
				$num=count($this->_post('picurl'));
				for($i=0;$i<$num;$i++){
					$arr[$i]['pic']=$_POST['picurl'][$i];
					$arr[$i]['title']=$_POST['title'][$i];
					$arr[$i]['url']=$_POST['url'][$i];
				}
				$data['web_url']=json_encode($arr);
			}
			$res=$db->where(array('id'=>1))->save($data);
		}else{
			$data['web_url']='';
			$res=$db->where(array('id'=>1))->save($data);
		}
		$this->success('操作成功！');
	}
	
	public function confHandle(){
		M('cms_config')->where('id=1')->save($_POST);
		$this->success('设置成功！');
	}
	public function navlink(){
		$navdb=M('cms_navlink');
		if(IS_POST){
			$_POST['id']?$navdb->where('id='.I('post.id'))->save($_POST):$navdb->add($_POST);
		}
		$navdb->order('navlist DESC')->select();
		$this->assign('nav',$navdb);
		$this->display();
	}
	//根据原图地址获取缩略图地址
	private function  get_thumb($url){
		$path=  pathinfo($url);
		$path['basename']=  str_replace('thumb_', '', $path['basename']);
		return $path['dirname'].'/thumb_'.$path['basename'];
	}

}