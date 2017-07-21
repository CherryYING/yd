<?php
class Shop2Action extends PublicAction{

public function store(){
		import("@.ORG.Page");
		$db=M('shop');
		$wei=M('wechat_user');
		
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
		
		
		$count = $wei->where(array('money_p'=>array('gt',0)))->count();
		$Page = new Page($count,10);
	$str='';
		// $list=$db->where($map)->order('shop_status asc,id DESC')->limit($Page->firstRow.','.$Page->listRows)->select();
		$yue=$wei->where(array('money_p'=>array('gt',0)))->select();
		foreach ($yue as $key => $val1) {
			
			$str.=$val1['id'].',';
		}
		$str = substr($str,0,strlen($str)-1); 
		$cc['id']=array('in',$str);
		$list=$db->where($cc)->where($map)->order('shop_status asc,id DESC')->limit($Page->firstRow.','.$Page->listRows)->select();
		foreach($list as $key=>$val){
				
					$list[$key]['goods_count1']=M('goods')->where(array('sid'=>$val['id'],'is_sale'=>1))->count();
					$list[$key]['goods_count2']=M('goods')->where(array('sid'=>$val['id'],'is_sale'=>2))->count();
					$list[$key]['user']=$wei->where(array('id'=>$val['id']))->find();
					
				
				
			}
			
		$show = $Page->show();
		$this->assign('show',$show);
		$this->assign('list',$list);
		$this->display();
	}




//导出excel
	public function export_excel(){
		$db=M('shop');
		$wei=M('wechat_user');
	
	 
	//excel名称
	$filename=date('YmdHis');
	
    header("Content-type:application/octet-stream");
    header("Accept-Ranges:bytes");
    header("Content-type:application/vnd.ms-excel");  
    header("Content-Disposition:attachment;filename=".$filename.".xls");
    header("Pragma: no-cache");
    header("Expires: 0");
	
	
	$field='id,shop_name,posttime,shop_status';
	$yue=$wei->where(array('money_p'=>array('gt',0)))->select();
		foreach ($yue as $key => $val1) {
			
			$str.=$val1['id'].',';
		}
		$str = substr($str,0,strlen($str)-1); 
		$cc['id']=array('in',$str);
		$list=$db->where($cc)->field($field)->order('id desc')->select();
		foreach($list as $key=>$val){	
				$list[$key]['posttime']=date('Y-m-d H:i',$val['posttime']);
		$arr=$wei->where(array('id'=>$val['id']))->find();	
					$list[$key]['user']=$arr['money_p'];
					$list[$key]['username']=$arr['username'];
					if($list[$key]['shop_status']==1){
						$list[$key]['shop_status']='已审核';
					}
					else{
						$list[$key]['shop_status']='未审核';
					}
			}
			
	$title=array('店铺ID','店铺名称','注册时间','审核状态','货款余额','登录帐号');
	
	
	
	
    //导出xls 开始
    if (!empty($title)){
        foreach ($title as $k => $v) {
            $title[$k]=iconv("UTF-8", "GB2312",$v);
        }
        $title= implode("\t", $title);
        echo "$title\n";
    }
    if (!empty($list)){
        foreach($list as $key=>$val){
            foreach ($val as $ck => $cv) {
                $list[$key][$ck]=iconv("UTF-8", "GB2312", $cv);
            }
            $list[$key]=implode("\t", $list[$key]);
            
        }
        echo implode("\n",$list);
    }
	
	
	
 }
 


 //店铺信息编辑
 public function shop_edit(){
		$db=M('shop');
		$id=I('get.id');
		$shop=$db->where(array('id'=>$id))->find();
		$user=M('wechat_user')->where(array('id'=>$id))->find();
		if(empty($shop)){
			$this->error('店铺不存在');
		}
		$this->assign('shop',$shop);
		$this->assign('user',$user);
		if($arr=$this->_post()){
			$db->where(array('id'=>$id))->save($arr);
			$this->success('保存成功');
		}else{
			$this->display();
		}
		
	}

}
?>