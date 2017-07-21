<?php
/*
	快递公司管理
*/
class ExpressAction extends PublicAction{	
	/*
		快递公司列表
	*/
	public function index(){
		import("@.ORG.Page");
		$db=M('express');
		$count = $db->count();
		$Page = new Page($count,10);
		$list = $db->order('id asc')->limit($Page->firstRow.','.$Page->listRows)->select();
		$show = $Page->show();
		$this->assign('show',$show);
		$this->assign('list',$list);
		$this->display();
	}
	/*
		新增
	*/
	public function add(){
		$db=M('express');
		if($arr=$this->_post()){
			$db->add($arr);
			$this->redirect('index');
		}
		$this->display();
	}
	/*
		编辑
	*/
	public function edit(){
		$id=I('get.id');
		$db=M('express');
		$info=$db->find($id);
		$this->assign('info',$info);
		if($arr=$this->_post()){
			$db->where(array('id'=>$id))->save($arr);
			$this->redirect('index');
		}
		$this->display();
	}
	/*
		地区管理
	*/
	public function area(){
		$map=array('region_type'=>1);
		$list=M('region')->where($map)->select();
		$list=order($list);
		$this->assign('list',$list);
		$this->display();
	}
	/*
		导入excel
	*/
	public function import_excel(){
		header('content-type:text/html;charset=utf-8');
		import('@.ORG.PHPExcel');
		import('@.ORG.PHPExcel.IOFactory');
		if($this->_post()){
			$uploadfile="./Data/upload/file/20150731/20150731200716_58477.xlsx";//I('post.excel');		
			//如果上传文件成功，就执行导入 excel操作  
			if($uploadfile){ 
				   // $objReader = PHPExcel_IOFactory::createReader('Excel5');//use excel2003   
				   $objReader =PHPExcel_IOFactory::createReader('Excel2007');//use excel2003 和  2007 format   
				   // $objPHPExcel = $objReader->load($uploadfile); //这个容易造成httpd崩溃   
				   $objPHPExcel =PHPExcel_IOFactory::load($uploadfile);//改成这个写法就好了   
			  
				   $sheet = $objPHPExcel->getSheet(0);    
				   $highestRow = $sheet->getHighestRow(); // 取得总行数    
				   $highestColumn = $sheet->getHighestColumn(); // 取得总列数   
				   
					//循环读取excel文件,读取一条,插入一条   
					for($j=1;$j<=$highestRow;$j++)   
					{    
						for($k='A';$k<=$highestColumn;$k++)   
						 {    
							 //$str .= iconv('gbk','utf-8',$objPHPExcel->getActiveSheet()->getCell("$k$j")->getValue()).'\\';		
							 $str .= $objPHPExcel->getActiveSheet()->getCell("$k$j")->getValue().'\\';		
							 //读 取单元格  
						}   
						//explode:函 数把字符串分割为数组。  
						$strs[]=explode("\\",$str);  
						  
						
						//$sql ="INSERT INTO z_test_importexcel(duty_date,name_am,name_pm) VALUES ('".$strs[0]."','".$strs[1]."','".$strs[2]."')";       
						//echo $ sql;  
						//mysql_query ("set names GBK");//这就是指定数据库字 符集，一般放在连接数据库后面就系了   
						//if(! mysql_query($sql)){  
						  //return false;  
						//}  
						$str ="";  
				   }   
				   var_dump ($strs);  
				   die();  
				   //unlink ($uploadfile); //删除上传的excel文件  
				   $msg = "导入成 功！";  
				}else{  
				   $msg = "导入失 败！";   
				}   
				return $msg;   
				
				//导入数据完成
		}
		$this->display();
	}
}