<?php
/*
	内容管理-【微信商城】
*/
class CmsAction extends Action{
	 
	public function _initialize(){
		$agent = $_SERVER['HTTP_USER_AGENT']; 
		if(!strpos($agent,"icroMessenger")) {
		  // exit('此功能只能在微信浏览器中使用');
		}
		//底部导航
		$nav=M('navlink')->field('id,title,url')->where(array('fup'=>0,'cid'=>1))->order('list asc')->select();
		foreach($nav as $key=>$val){
			$nav[$key]['child']=M(navlink)->field('id,title,url')->where(array('fup'=>$val['id'],'cid'=>1))->order('list asc')->select();
		}
		$this->assign('nav',$nav);
		$webinfo=M('cms_config')->find(1);
        $this->assign('webinfo',$webinfo);
		import("@.ORG.Wxjssdk");
		$wx_config=F('wx_config');
		$jsobj=new Wxjssdk($wx_config['appid'],$wx_config['appsecret']);
		$jssign=$jsobj->getSignPackage();
		$this->assign('jssign',$jssign);
		
		
	}
	
	//文章内页
	public function read(){
		$db=M("cms_article");
		$sort=M("cms_sort");
		$id=I('get.id');
		$info=$db->where(array('id'=>$id))->find();
		if(empty($info)){
			$this->error('访问内容不存在！');
		}
		$db->where(array('id'=>$id))->setInc('hits',1);
		$this->assign('info',$info);
		$this->display();
	}
	
	/*
		文章列表
	*/
	public function lists(){
		$db=M('cms_article');
		$fid=I('get.fid');
		$map=array('fid'=>$fid);
		$info=M('cms_sort')->find($fid);
		$this->assign('info',$info);
		$list=$db->where($map)->order('id')->select();
		$this->assign('list',$list);
		$this->display();
	}	
	
}