<?php
class Shop2{

public function store(){
		import("@.ORG.Page");
		$db=M('shop');
		$wei=M('wechat_user');
		$yue=$wei->select();
		/*$map['role_id']=array('egt',2);
		
		if($role_id=I('get.role_id')){
			$map['role_id']=$role_id;
		}
		$role=I('get.role');
		if($role=='agent'){
			$map['role_id']=array('egt',2);
		}
	
		
		if($begin_time>0){
			$map['posttime']=array('egt',$begin_time);
		}
		
		if($end_time>0){
			$map['posttime']=array('elt',$end_time);
		}*/
		
		$map=array();
		
		$so_key=I('get.key');
		$so_val=I('get.val');
		
		$begin_time=strtotime(I('get.begin_time'));
		$end_time=strtotime(I('get.end_time'));
		
		if(in_array($so_key,array('id','shop_name','shop_tel'))){
			if(!empty($so_val)&&!empty($so_val)){
				if($so_key=='id'&&is_numeric($so_val)){
					$map[$so_key]=$so_val;
				}else{
					$map[$so_key]=array('like','%'.$so_val.'%');
				}
				
			}
		}	
		
		
		
		$count = $wei->where(array('money_p'=>array('gt',0))->count();
		$Page = new Page($count,10);

		$list=$db->where($map)->order('shop_status asc,id DESC')->limit($Page->firstRow.','.$Page->listRows)->select();
		foreach($list as $key=>$val){
			
				if($yue[$val['id']]['money_p']>0){
					$list[$key]['goods_count1']=M('goods')->where(array('sid'=>$val['id'],'is_sale'=>1))->count();
					$list[$key]['goods_count2']=M('goods')->where(array('sid'=>$val['id'],'is_sale'=>2))->count();
					$list[$key]['user']=M('wechat_user')->where(array('id'=>$val['id']))->find();
				}
				
			
			
		}

		$show = $Page->show();
		$this->assign('show',$show);
		$this->assign('list',$list);
		$this->display();
	}
}
?>