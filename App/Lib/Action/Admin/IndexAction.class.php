<?php
/*
	这是后台首页控制器！
*/
class IndexAction extends PublicAction{
	public function index(){
		//当月订单统计
		$ym=date('Ym',time());
		$order_total_fee=M('order_info')->where(array('ym'=>$ym,'pay_status'=>1))->sum('total_fee');
		$order_pay_count=M('order_info')->where(array('ym'=>$ym,'pay_status'=>1))->count();
		$order_no_pay_count=M('order_info')->where(array('ym'=>$ym,'pay_status'=>0))->count();
		
		//没有提现的商铺
		$wei=M('wechat_user');
		$yue=$wei->where(array('money_p'=>array('gt',0)))->sum('money_p');
		$num=$wei->where(array('money_p'=>array('gt',0)))->count();
		

		$count_info['yue']=$yue;
		$count_info['num']=$num;


		$count_info['order_total_fee']=$order_total_fee;
		$count_info['order_pay_count']=$order_pay_count;
		$count_info['order_no_pay_count']=$order_no_pay_count;
		
		$db=M('wechat_user');
		//统计供货商总数
		$service_nums=M('service')->where(array('lock'=>0))->count();
		//统计店铺总数
		$user_count=$db->where(array('role_id'=>1))->count();
		$agent_city_count=$db->where(array('role_id'=>2))->count();
		$agent_province_count=$db->where(array('role_id'=>3))->count();
		$agent_country_count=$db->where(array('role_id'=>4))->count();
		//订单统计
		$today=strtotime(date('Y-m-d',time()));
		
		$map=array('order_time'=>array('egt',$today));
		//订单总数
		$order_total=M('order_info')->where($map)->count();
		//已付款订单
		$map['pay_status']=1;
		$pay_order_total=M('order_info')->where($map)->count();
		$map['pay_status']=0;
		$unpay_order_total=M('order_info')->where($map)->count();
		//商品统计
		//上架商品
		$sale_goods_total=M('goods')->where(array('is_sale'=>1))->count();
		//下架商品
		$unsale_goods_total=M('goods')->where(array('is_sale'=>2))->count();
		//最新商品
		$new_time=strtotime(date('Y-m-d',time()));
		$new_goods_total=M('goods')->where(array('is_sale'=>0,'posttime'=>array('egt',$new_time)))->count();
		//官方自营商品
		$self_goods_total=M('goods')->where(array('sid'=>0))->count();
			
		$count_info['service_nums']=$service_nums;
		$count_info['user_count']=$user_count;
		$count_info['agent_city_count']=$agent_city_count;
		$count_info['agent_province_count']=$agent_province_count;
		$count_info['agent_country_count']=$agent_country_count;
		
		$count_info['order_total']=$order_total;
		$count_info['pay_order_total']=$pay_order_total;
		$count_info['unpay_order_total']=$unpay_order_total;
		
		$count_info['sale_goods_total']=$sale_goods_total;
		$count_info['unsale_goods_total']=$unsale_goods_total;
		$count_info['new_goods_total']=$new_goods_total;
		$count_info['self_goods_total']=$self_goods_total;
		
		$this->assign('count_info',$count_info);
		$this->display();
	}
	
	
	/*
		销售报表
	*/
	public function charts(){
		$db=M('order_info');
		$order=$db->where()->select();
		foreach($order as $key=>$val){
			//M('order_info')->where(array('id'=>$val['id']))->save(array('year'=>substr($val['ymd'],0,4),'month'=>substr($val['ymd'],4,2)));
		}
		
		//已付款金额
		$count['pay_total_fee']=$db->where(array('pay_status'=>1))->sum('total_fee');
		//代付款金额
		$count['no_total_fee']=$db->where(array('pay_status'=>0))->sum('total_fee');
		//已退款金额
		$count['refund_total_fee']=$db->where(array('pay_status'=>2))->sum('total_fee');
		
		$count['trade_total_fee']=array(0,0,0,0,0,0,0,0,0,0,0,0);
		$year=date('Y',time());
		$month=date('m',time());
		$order_list=$db->where(array('year'=>$year,'pay_status'=>1))->select();
		foreach($order_list as $val){

			switch($val['month']){
				case 1:
					$count['trade_total_fee'][0]+=$val['total_fee'];
				break;
				case 2:
					
					$count['trade_total_fee'][1]+=$val['total_fee'];
				
				break;
				case 3:
				
					$count['trade_total_fee'][2]+=$val['total_fee'];
					
				break;
				case 4:
				
					$count['trade_total_fee'][3]+=$val['total_fee'];
					
				break;
				case 5:
					
					$count['trade_total_fee'][4]+=$val['total_fee'];
				
				break;
				case 6:
					
					$count['trade_total_fee'][5]+=$val['total_fee'];
				
				break;
				case 7:
				
					$count['trade_total_fee'][6]+=$val['total_fee'];
					
				break;
				case 8:
				
					$count['trade_total_fee'][7]+=$val['total_fee'];
					
				break;
				case 9:
					
					$count['trade_total_fee'][8]+=$val['total_fee'];
					
				break;
				case 10:
				
					$count['trade_total_fee'][9]+=$val['total_fee'];
					
				break;
				case 11:
				
					$count['trade_total_fee'][10]+=$val['total_fee'];
					
				break;
				case 12:
					
					$count['trade_total_fee'][11]+=$val['total_fee'];
				
				break;
			}	
		}
		$count['trade_total_fee']=json_encode($count['trade_total_fee']);
		$this->assign('count',$count);
		$this->display();
	}
	
	//基本信息编辑
	public function edit(){
		$user=M('user_data');
		$data=$user->where(array('id'=>1))->find();
		$this->assign('data',$data);
		if(array_filter($_FILES['spic']['name'])){
			$_data['icon']=$this->getpic('spic');
		}
		if(IS_POST){
			$_data['id']=1;
			$_data['role_id']=1;
			$_data['username']='admin';
			$_data['truename']=I('post.truename');
			$_data['address']=I('post.address');
			$_data['special']=I('post.special');
			$_data['jobtitle']=I('post.jobtitle');
			$_data['posttime']=time();
			//$res=$user->add($_data);
			$res=$user->where(array('id'=>1))->save($_data);
			if($res){
				$this->redirect(U('Index/edit'));	
			}
		}
		$this->display();
	}
	private function getpic($input){	
		$savePath = "./Data/upload/icon";
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
     return "/Data/upload/icon/".$pic[0]["saveName"];
	}	
	
	/*
		图片预览
	*/
	public function show_img(){
		$picurl=I('get.picurl','','base64_decode');
		echo "<img src='$picurl' style='width:500px'/>";
	}
	
	/*
		模板演示
	*/
	public function demo(){
		$this->display();
	}
}