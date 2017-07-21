<?php
class AjaxAction extends Action{
	
	/*
		 在线充值
	*/
	public function recharge(){
		$json=array('errcode'=>0,'msg'=>'变更成功');
		if($arr=$this->_post()){
			$user=M('wechat_user')->where(array('id'=>$arr['uid']))->find();
			//减少
			if($arr['type']==2){
				switch($arr['money_type']){
					case 'a':
						if($arr['money']>$user['money_a']){
							$json['errcode']=1;
							$json['msg']='当前会员A网资金小于您要减少的金额';
							echo json_encode($json);
							die();
						}	
					break;
					
					case 'b':
						if($arr['money']>$user['money']){
							$json['errcode']=1;
							$json['msg']='当前会员B网资金小于您要减少的金额';
							echo json_encode($json);
							die();
						}	
					
					break;	
					case 'p':
						if($arr['money']>$user['money_p']){
							$json['errcode']=1;
							$json['msg']='当前会员货款资金小于您要减少的金额';
							echo json_encode($json);
							die();
						}	
					
					break;	
				}
				
			}
			money_change($arr['uid'],$arr['type'],$arr['money'],'amdin_recharge',$arr['remark'],0,$arr['money_type']);
			echo json_encode($json);
		}
	}
	
	 /*
	 	更新物流信息
	 */	
	 public function order_update(){
		$db=M('order_info');
		$id=I('get.id');			//订单id
		$order=$db->where(array('id'=>$id))->find();
		$json=array('errcode'=>0,'msg'=>'操作成功');
		if($arr=$this->_post()){
			if($order['pay_status']==0){
				$json['errcode']=1;
				$json['msg']='订单未支付';
			}else{
				$arr['order_status']=2;			//已发货
				$db->where(array('id'=>$id))->save($arr);
				M('order_goods')->where(array('order_id'=>$id))->save($arr);
				//订单状态更新通知
				order_status_notice($id,'已发货');
			}
			echo json_encode($json);	
		}	
	 }
	
	/*
		修改订单物流状态 	
	 */
	 public function order_status(){
		$db=M('order_info');
		$id=I('get.id');			//订单id
		$json=array('errcode'=>0,'msg'=>'操作成功');
		if($arr=$this->_post()){
			
			$order=$db->where(array('id'=>$id))->find();
			
			if($order['pay_status']==1){
				$db->where(array('id'=>$id))->save($arr);
			
				if($arr['order_status']==2){
					$order_status='已发货';	
					
				}elseif($arr['order_status']==3){
					$order_status=' 已签收';
				}
				
				//更新每个商品的订单状态
				$g_data=array('order_status'=>$arr['order_status'],
				'express_name'=>$order['express_name'],
				'express_no'=>$order['express_no'],
				'express_tel'=>$order['express_tel']
				);
				M('order_goods')->where(array('order_id'=>$id))->save($g_data);
				
				//订单状态更新通知
				order_status_notice($id,$order_status);
			}else{
				$json['errcode']=1;			//未支付
				$json['msg']='订单未支付';
			}
			echo json_encode($json);	
		}	
	 }
	
	
	/*
		订单分佣【ajax】
	 */
	 public function fenyong(){
		 $db=M('order_info');
		 $order_id=I('get.id');			//订单id
		 $json['errcode']=0;
		 if($arr=$this->_post()){
			 //订单信息
			$order=$db->where(array('id'=>$order_id))->find();
			if($order['pay_status']==1){
				if($order['fy_status']==0){
					//订单分佣
					order_fenyong($order_id);
					$db->where(array('id'=>$order_id))->save(array('fy_status'=>1));
				}
			}else{
				$json['errcode']='订单未支付！';
			}
			echo json_encode($json);
			
		 }//end  if
		
	 }
	
	/*
		售后处理
	*/
	public function goods_refund(){
		$db=M('order_refund');
		$json=array('errcode'=>0,'msg'=>'操作成功');
		if($arr=$this->_post()){
			$info=$db->where(array('id'=>$arr['refund_id']))->find();
			if($info['status']==0){
				if($info['type']==1){			//退款
					goods_refund($arr['refund_id']);
				}else{							//换货
					$info=$db->where(array('id'=>$arr['refund_id']))->save(array('status'=>1,'shop_handle_status'=>1));
				}	
			}
			
			echo json_encode($json);
		}
		
	}
	
	
	/*
		设置区域代理
	*/
	public function agent_b_set(){
		$db=M('area_agent');
		$json=array('errcode'=>0);
		if($arr=$this->_post()){
			$map=array('area_id'=>$arr['area_id']);
			$info=$db->where($map)->find();
			if(!empty($info)){				//该地区已存在代理
				$json['errcode']=1;
				$json['msg']='该地区已存在代理，不能重复分配';
			}else{
				$data['uid']=$arr['uid'];
				$data['area_id']=$arr['area_id'];
				$user=M('wechat_user')->where(array('id'=>$arr['uid']))->find();
				$area=M('region')->where(array('id'=>$arr['area_id']))->find();
				$data['nickname']=$user['nickname'];
				$data['name']=$user['name'];
				$data['mobile']=$user['mobile'];
				
				$data['area_name']=$area['region_name'];
				$data['area_type']=$area['region_type'];
				$data['posttime']=time();
				$db->add($data);
				$json['msg']='设置成功';
			}
			echo json_encode($json);
		}
	}
	
	/*
		搜索地区
	*/
	public function search_area(){
		$db=M('region');
		$json=array('errcode'=>0);
		if($arr=$this->_post()){
			$map=array('region_name'=>array('like','%'.$arr['area_name'].'%'));
			$list=$db->where($map)->select();	
			if(count($list)>0){
				$json['data']=$list;	
			}else{
				$json['errcode']=1;				//无搜索结果
				$json['msg']='无搜索结果';
			}
			$json['sql']=$db->getlastsql();
			echo json_encode($json);
		}
	}
	
	
	/*
		搜索地区
	*/
	public function search_user(){
		$db=M('wechat_user');
		$json=array('errcode'=>0);
		if($arr=$this->_post()){
			if(is_numeric($arr['user'])){
				$map['id']=$arr['user'];
			}else{
				$map="(name like '%{$arr['user']}%') OR (nickname like '%{$arr['user']}%')";
			}
			$list=$db->where($map)->select();	
			if(count($list)>0){
				$json['data']=$list;	
				$json['sql']=$db->getlastsql();
			}else{
				$json['errcode']=1;				//无搜索结果
				$json['msg']='无搜索结果';
			}
			echo json_encode($json);
		}
	}
	
	
	/*
		订单退款
	*/
	public function order_refund(){
		$db=M('order_info');
		if($arr=$this->_post()){
			$id=$arr['id'];			//订单id
			$order=$db->where(array('id'=>$id))->find();
			if(!empty($order)){
				//修改订单支付状态为2[已退款]
				$db->where(array('id'=>$order['id']))->save(array('pay_status'=>2));
				//佣金撤回【货款退回】
				yongjin_refund($order['id']);
				
				$refund=M('order_refund')->where(array('order_id'=>$order['id']))->find();
				//如果用户已经提交退款申请（改为已处理）
				if(!empty($refund)){
					M('order_refund')->where(array('order_id'=>$order['id']))->save(array('status'=>1));			
				}
				
				echo 1;
			}
			
		}	
	}
	
	/*
		搜索用户
	*/
	public function user_search(){
		$db=M('wechat_user');
		$output=array('errcode'=>0);
		if($arr=$this->_post()){
			if(is_numeric($arr['search_key'])){
				$map=array('id'=>$arr['search_key']);
			}else{
				$map="(nickname LIKE '%{$arr['search_key']}%')";
			}
			
			$list=$db->where($map)->field('id,p_1,p_2,p_3,nickname,username')->select();
			if(empty($list)){
				$output['errcode']=1;
				$output['msg']='无相关用户';
			}else{
				$output['data']=$list;
			}
			echo json_encode($output);
			die();
		}
	}
	
	/*
		修改用户关系
	*/
	public function update_user_relation(){
		$db=M('wechat_user');
		if($arr=$this->_post()){
			//顶级用户
			if($arr['p_1']==0){
				$data['p_1']=$data['p_2']=$data['p_3']=$data['p_4']=0;
				$db->where(array('id'=>$arr['uid']))->save($data);
				echo 1;
				die();
			}else{
				$p_1=$db->where(array('id'=>$arr['p_1']))->find();				
				$user=$db->where(array('id'=>$arr['uid']))->find();
				if($arr['uid']!=$arr['p_1']){					
					$data['p_1']=$p_1['id'];
					$data['p_2']=$p_1['p_1']>0&&$p_1['p_1']!=$arr['uid']?$p_1['p_1']:0;
					$data['p_3']=$p_1['p_2']>0&&$p_1['p_2']!=$arr['uid']?$p_1['p_2']:0;
					
					$data['p_4']=$p_1['p_3']>0&&$p_1['p_3']!=$arr['uid']?$p_1['p_3']:0;
					$db->where(array('id'=>$arr['uid']))->save($data);
					//更新下一级
					$data1['p_1']=$user['id'];
					$data1['p_2']=$data['p_1'];
					$data1['p_3']=$data['p_2'];
					
					$data1['p_4']=$data['p_3'];
					$db->where(array('p_1'=>$arr['uid']))->save($data1);
					
					//更新下二级
					$data2['p_2']=$user['id'];
					$data2['p_3']=$data['p_1'];
					
					$data2['p_4']=$data['p_2'];
					$db->where(array('p_2'=>$arr['uid']))->save($data2);
					
					//更新下3级
					$data3['p_3']=$user['id'];
					$data3['p_4']=$data['p_1'];
					$db->where(array('p_3'=>$arr['uid']))->save($data3);

					
					/*if($user['id']==$p_1['p_1']){
						$db->where(array('id'=>$p_1['id']))->save(array('p_1'=>0,'p_2'=>0,'p_3'=>0));
					}
					if($user['id']==$p_1['p_2']){
						$db->where(array('id'=>$p_1['id']))->save(array('p_1'=>0,'p_2'=>0,'p_3'=>0));
					}
					if($user['id']==$p_1['p_3']){
						$db->where(array('id'=>$p_1['id']))->save(array('p_1'=>0,'p_2'=>0,'p_3'=>0));
					}*/
					
					
				}
			}
			
			
		
			
			
		}
	}
	
	/*
		资金变更
	*/
	public function money_change(){
		$db=M('wechat_user');
		$id=I('get.id');
		$info=$db->where(array('id'=>$id))->find();
		$info['money']=intval($info['money']);
		if($arr=$this->_post()){
			$arr['money']=intval($arr['money']);
			$money=0;
			if($info['money']>=$arr['money']){
				$money=$info['money']-$arr['money'];
				$type=2;			//减少
			}elseif($info['money']<$arr['money']){
				$money=$arr['money']-$info['money'];
				$type=1;			//增加
			}
			//$uid,$type,$amount,$way,$remark,$order_id
			if($money>0){
				money_change($id,$type,$money,'admin_change','管理员变更',0);
			}
			echo 1;
		}
	}
	
	/*
		积分变更
	*/
	public function jifen_change(){
		$db=M('wechat_user');
		$id=I('get.id');
		$info=$db->where(array('id'=>$id))->find();
		$info['jifen']=intval($info['jifen']);
		if($arr=$this->_post()){
			$arr['jifen']=intval($arr['jifen']);
			if($info['jifen']>=$arr['jifen']){
				$jifen=$info['jifen']-$arr['jifen'];
				$type=2;			//减少
			}elseif($info['jifen']<$arr['jifen']){
				$jifen=$arr['jifen']-$info['jifen'];
				$type=1;			//增加
			}
			//$user_id,$type,$amount,$way,$remark
			jifen_change($id,$type,$jifen,'admin_change','管理员变更');
			echo 1;
		}
	}
	
	public function index(){
		import("@.ORG.Page");
		$db=M('photo');
		if($wechatid=I('wechatid')){
			$map=array('wechatid'=>$wechatid);	
		}else{
			$map=array();
		}
		$count = $db->where($map)->count();
		$Page = new Page($count,20);		
		$list=$db->where($map)->order('id desc')->limit($Page->firstRow.','.$Page->listRows)->select();
		foreach($list as $key=>$val){
			$list[$key]['thumb']=get_thumb($val['photo']);
			$list[$key]['uname']=M('wechatuser')->where(array('wechatid'=>$val['wechatid']))->getField('uname');
		}
		$show = $Page->show();
		$this->assign('show',$show);
		$this->assign('list',$list);
		$this->display();    
	}
	public function del(){
		if($id=I('get.id')){
			$db=M('photo');
			$db->where(array('id'=>$id))->delete();
			$this->redirect('index');
		}	
	}
	public function upload(){
		import("@.ORG.Thumb");
		$date=date('Ymd',time());
        $folder="./Data/upload/slide/$date";
        if(!file_exists($folder)){
            mkdir($folder);
        }
        $targetFolder=$folder;
		if (!empty($_FILES)){
			$return=array('flag'=>false);
			if($_FILES['Filedata']['size']>2*1000*1000){
				$return['msg']='图片大小不能超过2M';
				echo json_encode($return);
				die();
			}
			$tempFile = $_FILES['Filedata']['tmp_name'];
			$targetPath = $targetFolder ;//$_SERVER['DOCUMENT_ROOT'] . $targetFolder;
			//重新构造图片名称
			$fileParts=pathinfo($_FILES['Filedata']['name']);
			$picname=rand(1111,9999).time().'.'.$fileParts['extension'];
			$targetFile=rtrim($targetPath,'/') . '/' . $picname;
			$thumbFile=rtrim($targetPath,'/') . '/thumb_' . $picname;
			//$targetFile=rtrim($targetPath,'/') . '/' . $_FILES['Filedata']['name'];
			
			// Validate the file type
			$fileTypes = array('jpg','jpeg','gif','png'); // File extensions
			$fileParts = pathinfo($_FILES['Filedata']['name']);
			
			if (in_array($fileParts['extension'],$fileTypes)) {
				move_uploaded_file($tempFile,$targetFile);
				//生成缩略图
				$thumb=new ResizeImage($targetFile, '120', '90', '0',$thumbFile);
				/*$source = imagecreatefromjpeg($targetFile);
				list($width, $height) = getimagesize($targetFile);
				list($newwidth,$newheight)=array(200,170);
				$thumb = imagecreatetruecolor($newwidth, $newheight);
				imagecopyresized($thumb, $source, 0, 0, 0, 0, $newwidth, $newheight, $width, $height);
				if($fileParts['extension']=='png'){
					imagepng($thumb,$thumbFile);
				}elseif($fileParts['extension']=='jpg'){
					imagejpeg($thumb,$thumbFile);
				}elseif($fileParts['extension']=='gif'){
					imagegif($thumb,$thumbFile);
				}*/
				$return['flag']=true;
				$return['url']=substr($targetFile,1);
				echo json_encode($return);
				die();
			}else{
				$return['msg']='图片格式不正确';
				echo json_encode($return);
				die();
			}
		}
	}
	//地区联动
	/*
		省份
	*/
	public function province(){
		$db=M('region');
		$map=array('region_type'=>1);
		$list=$db->where($map)->field('id,parent_id,region_name')->select();
		echo json_encode($list);die();
	}
	/*
		城市
	*/
	public function city(){
		$db=M('region');
		$map=array('region_type'=>2,'parent_id'=>I('post.parent_id'));
		$list=$db->where($map)->field('id,parent_id,region_name')->select();
		echo json_encode($list);die();
	}
	/*
		区县
	*/
	public function district(){
		$db=M('region');
		$map=array('region_type'=>3,'parent_id'=>I('post.parent_id'));
		$list=$db->where($map)->field('id,parent_id,region_name')->select();
		echo json_encode($list);die();
	}
}