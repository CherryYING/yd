<?php 
class GoodsBrandAction extends PublicAction{
		public function _initialize(){
			parent::_initialize();
			header("content-type:text/html;charset=utf-8");
			$_brands=M('goods_brand')->select();
			foreach($_brands as $val){
				$brands[$val['id']]=$val;
			}
			$_categorys=M('goods_category')->select();
			foreach($_categorys as $val){
				$categorys[$val['id']]=$val;
			}
			$this->assign('brands',$brands);
			$this->assign('categorys',$categorys);	
		}
		//品牌列表
		public function index(){
			import("@.ORG.Page");
			$db = M('goods_brand');
			$count = $db->count();
			$Page = new Page($count,10);
			$list = $db->order('list asc')->limit($Page->firstRow.','.$Page->listRows)->select();
			foreach($list as $key=>$val){
				$list[$key]['cid']=self::id2name($val['cid']);
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
			$id_arr=explode(',',$id_str);
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
		//添加品牌
		public function add(){
			$db=M('goods_brand');
			if($this->isPost()){
				$arr=$this->_post();
				$arr['posttime']=time();
				$rs=$db->data($arr)->add();
				if($rs){
					$this->redirect('index');
				}
			}
			$this->display();
		}
		//编辑品牌
		public function edit(){
			$db=M('goods_brand');
			$id=I('get.id');
			$brand_info=$db->find($id);
			$this->assign('info',$brand_info);
			if($this->isPost()){
				$map=array('id'=>$id);
				$arr=$this->_post();
				if(is_array($arr['cid'])){
					$arr['cid']=implode(',',$arr['cid']);
				}
				$rs=$db->where($map)->data($arr)->save();
				if($rs){
					$this->redirect('index',array('id'=>$id));
				}
			}
			$this->display();
		}
		//删除品牌
		public function del(){
			M('goods_brand')->delete(I('get.id'));
			$this->redirect('index');
		}
		
}