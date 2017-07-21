<?php 
/*
	商品控制器
*/
class GoodsAction extends PublicAction{
		public function _initialize(){
			parent::_initialize();
			import("@.ORG.Page");
			$_brands=M('goods_brand')->select();
			foreach($_brands as $val){
				$brands[$val['id']]=$val;
			}
			$_categorys=M('goods_category')->select();
			/*foreach($_categorys as $val){
				$categorys[$val['id']]=$val;
			}*/
			$categorys=order($_categorys);
			$this->assign('brands',$brands);
			$this->assign('categorys',$categorys);	
			//供货商列表
			$service_list=M('service')->where(array('lock'=>0))->select();
			$this->assign('service_list',$service_list);
			
			//快递列表
			$express_list=M('express')->where(array('status'=>1))->select();
			$this->assign('express_list',$express_list);
		}
		//商品列表
		public function index(){
			
			$db = M('goods');
			$map=array('sid'=>$this->service_id);
			if($cid=I('get.cid')){
				$map['cid']=$cid;
			}
			if($bid=I('get.bid')){
				$map['bid']=$bid;
			}
			
			
			$so_key=I('get.key');
			$so_val=I('get.val');
			
			if(in_array($so_key,array('name'))){
				if(!empty($so_val)&&!empty($so_val)){
					$map[$so_key]=array('like','%'.$so_val.'%');
				}
			}
			
			$count = $db->where($map)->count();
			$Page = new Page($count,10);
			$list = $db->where($map)->order('id DESC')->limit($Page->firstRow.','.$Page->listRows)->select();
			foreach($list as $key=>$val){
				$list[$key]['cid']=self::id2name($val['cid']);
				$list[$key]['service']=M('service')->where(array('id'=>$val['sid']))->find();
			}
			$show = $Page->show();
			$this->assign('show',$show);
			$this->assign('list',$list);
			$this->display();    
		}
		/*
			分类id=>分类名称
			@params string 1,2,3
			@return string	a,b,c
		*/
		public function id2name($id_str){
			$id_arr=array_filter(explode(',',$id_str));
			$db=M('goods_category');
			$catelist=$db->select();
			foreach($catelist as $key=>$val){
				$c_list[$val['id']]=$val['name'];
			}
			foreach($id_arr as $val){
				$arr_id[]=$c_list[$val];
			}
			$name_str=implode(',',$arr_id);
			return $name_str;
		}
		//添加商品
		public function add(){
			$db=M('goods');
			if($this->isPost()){
				$arr=$this->_post();
				$arr['sid']=$this->service_id;		//店铺id
				$arr['posttime']=time();
				if(is_array($arr['cid'])){
					$arr['cid']=implode(',',$arr['cid']);
					$arr['cid']=','.$arr['cid'].',';
				}
				$id=$db->data($arr)->add();
				$this->success('发布成功',U('index'));
			}else{
				$this->display();
			}
			
		}
		//编辑商品
		public function edit(){
			$db=M('goods');
			$id=I('get.id');
			$p=I('post.p',1);								//分页值
			$info=$db->where(array('id'=>$id))->find();
			if(empty($info)){
				$this->error('商品不存在或已下架');die();
			}
			if($info['sid']!=$this->service_id){
				$this->error('无操作权限');die();
			}
			$this->assign('info',$info);
			if($this->isPost()){
				$map=array('id'=>$id);
				$arr=$this->_post();
				unset($arr['p']);
				if(!$arr['is_tui']){$arr['is_tui']=0;}
				if(!$arr['is_hot']){$arr['is_hot']=0;}
				if(!$arr['is_active']){$arr['is_active']=0;}
				if(is_array($arr['cid'])){
					$arr['cid']=implode(',',$arr['cid']);
					$arr['cid']=','.$arr['cid'].',';
				}
				$db->where($map)->data($arr)->save();
				$this->success('操作成功',U('index',array('p'=>$p)));
			}else{
				$this->display();
			}
			
		}
		//删除商品
		public function del(){
			M('goods')->delete(I('get.id'));
			$this->redirect('index');
		}
		
		/*
			快速上下架
		*/
		public function up2down(){
			$db=M('goods');
			$id=I('get.id');
			$info=$db->find($id);
			if($info['is_sale']==1){
				$data['is_sale']=2;
			}else{
				$data['is_sale']=1;
			}
			$db->where(array('id'=>$id))->save($data);
			$this->redirect('index');
		}
		
		
		
		/*
			商品规格管理
		*/
		public function norm_list(){
			$db=M('goods_norm');
			$gid=I('get.id');				//商品id
			
			if(empty($gid)){
				$this->error('参数错误');
			}
			
			//商品信息
			$goods=M('goods')->where(array('id'=>$gid))->find();
			$this->assign('goods',$goods);
			
			
			$map=array('gid'=>$gid);
			$count = $db->where($map)->count();
			$Page = new Page($count,10);
			$list = $db->where($map)->order('id DESC')->limit($Page->firstRow.','.$Page->listRows)->select();
			foreach($list as $key=>$val){
				$list[$key]['cid']=self::id2name($val['cid']);
				$list[$key]['service']=M('service')->where(array('id'=>$val['sid']))->find();
			}
			$show = $Page->show();
			$this->assign('show',$show);
			$this->assign('list',$list);
			$this->display();
		}
		
		/*
			新增商品规格
		*/
		public function norm_add(){
			$db=M('goods_norm');
			$id=I('get.id');			//商品id
			
			if(empty($id)){
				$this->error('参数错误');
			}
			
			//商品信息
			$goods=M('goods')->where(array('id'=>$id))->find();
			$this->assign('goods',$goods);
			
			if($data=$this->_post()){
				$data['gid']=$id;
				$db->add($data);
				$this->redirect('norm_list',array('id'=>$id));
			}
			$this->display();
		}
		/*
			编辑商品规格
		*/
		public function norm_edit(){
			$db=M('goods_norm');
			$id=I('get.id');			//规格id
			$info=$db->where(array('id'=>$id))->find();
			$this->assign('info',$info);
			//商品信息
			$goods=M('goods')->where(array('id'=>$info['gid']))->find();
			$this->assign('goods',$goods);
			if($data=$this->_post()){
				$db->where(array('id'=>$id))->save($data);
				$this->redirect('norm_list',array('id'=>$info['gid']));
			}
			$this->display();
		}
		
		public function norm_del(){
			$db=M('goods_norm');
			$id=I('get.id');
			$info=$db->where(array('id'=>$id))->find();
			$db->where(array('id'=>$id))->delete();
			$this->redirect('norm_list',array('id'=>$info['gid']));
		}
		
		/*
			评论列表
		*/
		public function reply_list(){
			$db=M('goods_reply');
			
			$map=array();
			
			$so_key=I('get.key');
			$so_val=I('get.val');
			
			if(in_array($so_key,array('uname'))){
				if(!empty($so_val)&&!empty($so_val)){
					$map[$so_key]=array('like','%'.$so_val.'%');
				}
			}
			
			
			$count = $db->where($map)->count();
			$Page = new Page($count,10);
			$list = $db->where($map)->order('id DESC')->limit($Page->firstRow.','.$Page->listRows)->select();
			foreach($list as $key=>$val){
				$list[$key]['goods']=M('goods')->where(array('id'=>$val['gid']))->find();
			}
			$show = $Page->show();
			$this->assign('show',$show);
			$this->assign('list',$list);
			$this->display();
		}
		
		public function reply_del(){
			$db=M('goods_reply');
			$id=I('get.id');
			$p=I('get.p',1);
			$db->where(array('id'=>$id))->delete();
			$this->redirect('reply_list',array('p'=>$p));
		}
		/*
			选择商城商品在个人店铺展示
		*/
		public function goods_manage(){
			$db = M('goods');
			$map=array('sid'=>0);			//只列举官方自营商品
			if($cid=I('get.cid')){
				$map['cid']=$cid;
			}
			if($bid=I('get.bid')){
				$map['bid']=$bid;
			}
			
			$map['is_sale']=1;
			
			$so_key=I('get.key');
			$so_val=I('get.val');
			
			if(in_array($so_key,array('name'))){
				if(!empty($so_val)&&!empty($so_val)){
					$map[$so_key]=array('like','%'.$so_val.'%');
				}
			}
			
			$count = $db->where($map)->count();
			$Page = new Page($count,10);
			$list = $db->where($map)->order('id DESC')->limit($Page->firstRow.','.$Page->listRows)->select();
			foreach($list as $key=>$val){
				$list[$key]['cid']=self::id2name($val['cid']);
				$list[$key]['service']=M('service')->where(array('id'=>$val['sid']))->find();
			}
			$show = $Page->show();
			$this->assign('show',$show);
			$this->assign('list',$list);
			//当前商家推荐列表
			$recom_goods_list=M('goods_recommend')->where(array('uid'=>$this->service_id))->select();
			foreach($recom_goods_list as $val){
				$recom_list[]=$val['goods_id'];
			}
			$this->assign('recom_list',$recom_list);
			$this->display();    
		}
		
		public function ajax_recom_goods(){
			$db=M('goods_recommend');	
			$json=array('errcode'=>0,'msg'=>'操作成功');	
			if($arr=$this->_post()){
				
				$i=$db->where($map)->find();	
				if($arr['act']=='add'){
					$data['uid']=$this->service_id;
					$data['goods_id']=$arr['goods_id'];
					$data['posttime']=time();
					$db->add($data);
				}elseif($arr['act']=='cancel'){
					$map=array('goods_id'=>$arr['goods_id'],'uid'=>$this->service_id);
					$db->where($map)->delete();
				}
				echo json_encode($json);
				
			}
		}
}