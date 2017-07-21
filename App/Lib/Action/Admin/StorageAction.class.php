<?php
/*
	供货仓库管理
*/
class storageAction extends PublicAction{
	public function index(){
		$db=M('storage');
		$list=$db->select();
		$this->assign('list',$list);
		$this->display();
	}
	/*
		新增供货仓库
	*/
	public function add(){
		$db=M('storage');
		if($arr=$this->_post()){
			$db->add($arr);
			$this->redirect('index');
		}
		$this->display();
	}
	/*
		编辑仓库信息
	*/
	public function edit(){
		$db=M('storage');
		$id=I('get.id');
		$info=$db->find($id);
		$this->assign('info',$info);
		if($arr=$this->_post()){
			$db->where(array('id'=>$id))->save($arr);
			$this->redirect('index');
		}
		$this->display();
	}
	/*
		删除供货仓库
	*/
	public function del(){
		$db=M('storage');
		$id=I('get.id');
		$db->delete($id);
		$this->success('删除成功！',U('index'));
	}
	/*
		分配供货区域
	*/
	public function assign_area(){
		header("content-type:text/html;charset=utf-8");
		$db=M('region');
		$id=I('get.id');		//仓库id
		$province=$db->where(array('region_type'=>1))->select();
		foreach($province as $key=>$val){
			$city=$db->where(array('parent_id'=>$val['id']))->select();
			foreach($city as $k=>$v){
				$city[$k]['county']=$db->where(array('parent_id'=>$v['id']))->select();
			}
			$province[$key]['city']=$city;
			unset($city);
		}
		$this->assign('province',$province);
		if($arr=$this->_post()){
			$arr['area_list']=implode(',',$arr['area_list']);
			$arr['area_list'].=',';
			M('storage')->where(array('id'=>$id))->save($arr);
			$this->redirect('index');
		}
		//已分配区域
		$info=M('storage')->find($id);
		$info['area_list']=array_filter(explode(',',$info['area_list']));
		$this->assign('area_list',$info['area_list']);
		$this->display();
	}
	
	/*
		库存商品
	*/
	public function goods_list(){
		$db=M('goods_store');
		$id=I('get.id');
		$list=$db->where(array('storage_id'=>$id))->select();
		foreach($list as $key=>$val){
			$list[$key]['goods']=M('goods')->field('id,name,price,spic')->find($val['id']);
		}
		$this->assign('list',$list);
		$this->display();
		//dump($list);
	}
	/*
		库存商品编辑
	*/
	public function goods_edit(){
		$db=M('goods_store');
		$id=I('get.id');
		$info=$db->find($id);
		$this->assign('info',$info);
		//仓库列表
		$storage=M('storage')->select();
		$this->assign('storage',$storage);
		//商品列表
		$goods_list=M('goods')->select();
		$this->assign('goods_list',$goods_list);
		if($arr=$this->_post()){
			// 仓库名称
			$arr['storage']=M('storage')->where(array('id'=>$arr['storage_id']))->getField('name');
			//商品名称
			$arr['goods_name']=M('goods')->where(array('id'=>$arr['goods_id']))->getField('name');
			M('goods_store')->where(array('id'=>$id))->save($arr);
			$this->success('保存成功',U('goods_list',array('id'=>$info['storage_id'])));
		}else{
			$this->display();
		}
	}
	
	
}