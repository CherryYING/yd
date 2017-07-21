<?php
/*
	商城购物车操作
*/

/*
	读取购物车商品列表
*/
function cart_product_list(){
	$list=$_SESSION['shop_cart_info'];
	foreach($list as $key=>$val){
		if($val['goods_nums']<=0){
			unset($list[$key]);
		}
	}
	return $list;
}

/*
	添加购物车
	param:商品id,商品数量,商品价格,商品规格
*/
function addcart($goods_id,$goods_num,$goods_price,$goods_norm){
	$cur_cart_arr=$_SESSION['shop_cart_info'];
	if(empty($cur_cart_arr)){
		$cart_info[0]=array(
			'goods_id'=>$goods_id,
			'goods_nums'=>$goods_num,
			'goods_price'=>$goods_price,
			'goods_norm'=>$goods_norm
		);
		$_SESSION['shop_cart_info']=$cart_info;
	}elseif(!empty($cur_cart_arr)){
		//购物车中存在相同商品
        $is_exist=0;
		foreach($cur_cart_arr as $key=>$val){
			if($val['goods_id']==$goods_id&&$val['goods_norm']==$goods_norm){
				$cur_cart_arr[$key]['goods_nums']=$val['goods_nums']+$goods_num;
				$is_exist=1;
			}
		}
		//购物车中不存在相同商品
		if($is_exist==0){
			$cur_cart_arr[]=array(
			'goods_id'=>$goods_id,
			'goods_nums'=>$goods_num,
			'goods_price'=>$goods_price,
			'goods_norm'=>$goods_norm
			) ;
		}
		$_SESSION['shop_cart_info']=$cur_cart_arr;
	}	
	
}

/*
	删除购物车
*/
function delcart($cart_key){
	$cur_goods_arr=$_SESSION['shop_cart_info'];
	//删除该商品在数组中的位置
	unset($cur_goods_arr[$cart_key]);
	$_SESSION['shop_cart_info']=$cur_goods_arr;
}

/*
	修改购物车
	param: 商品id，增加？减少，商品规格
*/
function updatecart($cart_key,$action='add'){
	$cur_cart_arr=$_SESSION['shop_cart_info'];
	
	foreach($cur_cart_arr as $key=>$val){
		if($key==$cart_key){
			
			if($action=='add'){
				$cur_cart_arr[$key]['goods_nums']+=1;
			}else{
				$cur_cart_arr[$key]['goods_nums']-=1;
				if($cur_cart_arr[$key]['goods_nums']==0){
					unset($cur_cart_arr[$key]);
				}
			}
			
				
		}
	}
	
	$_SESSION['shop_cart_info']=$cur_cart_arr;
}

function _updatecart($goods_id,$action='add',$goods_norm){
	$cur_cart_arr=$_SESSION['shop_cart_info'];
	
	foreach($cur_cart_arr as $key=>$val){
		if($val['goods_id']==$goods_id&&$val['goods_norm']==$goods_norm){
			
			if($action=='add'){
				$cur_cart_arr[$key]['goods_nums']+=1;
			}else{
				$cur_cart_arr[$key]['goods_nums']-=1;
				if($cur_cart_arr[$key]['goods_nums']==0){
					unset($cur_cart_arr[$key]);
				}
			}
			
				
		}
	}
	
	$_SESSION['shop_cart_info']=$cur_cart_arr;
}
/*
	计算购物车商品总数
*/
function  cart_count(){
	$cart_count=0;
	$list=$_SESSION['shop_cart_info'];
	foreach($list as $val){
		$cart_count+=$val['goods_nums'];
	}
	return $cart_count;
	
}

?>