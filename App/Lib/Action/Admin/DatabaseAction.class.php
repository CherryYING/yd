<?php
/*
	数据备份
*/
class DatabaseAction extends PublicAction
{
  
public function index()
{
	$dbName=C('DB_NAME');
	$list=M()->query('SHOW TABLE STATUS FROM '.$dbName);
	$this->assign("list",$list);
	$this->display();
}
/*
	数据恢复
*/
public function recover(){
	$dir="./Data/Databack/";
	$list=scandir($dir);
	foreach($list as $key=>$val){
		$_list[$key]['name']=$val;	
		//文件名
		$filesize=filesize($dir.$val);
		$_list[$key]['size']=ceil($filesize/1024);			//文件大小[KB]
		$filetime=filemtime($dir.$val);
		$_list[$key]['time']=$filetime;		//文件修改时间
		if($val=="."||$val==".."){
			unset($_list[$key]);
		}
		unset($filesize);
		unset($filetime);
	}
	$this->assign('list',$_list);
	$this->display();
}
/*
	删除历史备份
*/
public function del(){
	$filename=I('get.filename');
	unlink('./Data/Databack/'.$filename);
	$this->redirect('recover');
}

/*
	数据备份
*/
public function back()
{
 
	 if(empty($_POST['tablearr']))
	 {
	$table=$this->getTable();
	 }else
	 {
	$table=explode(",",$_POST['tablearr']);
	 }
	 $struct=$this->bakStruct($table);
	 $record=$this->bakRecord($table);
	 $sqls=$struct.$record;
	 $dir="./Data/Databack/".date("Y-m-d_H-i-s").".sql";
	 file_put_contents($dir,$sqls);
	 if(file_exists($dir))
	 {
	$this->success("备份成功");
	 }else
	 {
	$this->error("备份失败");
	 }
}
 
protected function getTable()
{
	 $dbName=C('DB_NAME');
	 $result=M()->query('show tables from '.$dbName);
	 foreach ($result as $v){
	 $tbArray[]=$v['Tables_in_'.C('DB_NAME')];
	 }
	 return $tbArray;
}
/*
	备份表结构
*/ 
protected function bakStruct($array)
{
	 foreach ($array as $v){
	  $tbName=$v;
	  $result=M()->query('show columns from '.$tbName);
	
	  $sql.="--\r\n";
	  $sql.="-- 数据表结构: `$tbName`\r\n";
	  $sql.="--\r\n\r\n";
	  $sql.="DROP TABLE IF EXISTS `$tbName`;\r\n";
	  $sql.="create table `$tbName` (\r\n";
	  $rsCount=count($result);
	  foreach ($result as $k=>$v){
	  $field  =       $v['Field'];
	  $type   =       $v['Type'];
	  $default=       $v['Default'];
	  $extra  =       $v['Extra'];
	  $null   =       $v['Null'];
	if(!($default=='')){
	 $default='default '.$default;
	}      
	  if($null=='NO'){
	  $null='not null';
	  }else{
	  $null="null";
	  }          
	  if($v['Key']=='PRI'){
	  $key    =       'primary key';
	  }else{
	  $key    =       '';
	  }
	if($k<($rsCount-1)){
	 $sql.="`$field` $type $null $default $key $extra ,\r\n";
	}else{
	 //最后一条不需要","号
	 $sql.="`$field` $type $null $default $key $extra \r\n";
	}
	
	
	  }
	  $sql.=") ENGINE=MyISAM DEFAULT CHARSET=utf8;\r\n\r\n";
	 }
	 return str_replace(',)',')',$sql);
}
/*
	备份数据记录
*/  
protected function bakRecord($array)
{
 
	foreach ($array as $v){
	 
	  $tbName=$v;
	 
	 $rs=M()->query('select * from '.$tbName);
	 
	 if(count($rs)<=0){
	 continue;
	 }
	
	  $sql.="--\r\n";
	  $sql.="-- 数据表中的数据: `$tbName`\r\n";
	  $sql.="--\r\n\r\n";
	
	 foreach ($rs as $k=>$v){
	
	 $sql.="INSERT INTO `$tbName` VALUES (";
	  foreach ($v as $key=>$value){
	  if($value==''){
	  $value='null';
	  }
	  $type=gettype($value);
	  if($type=='string'){
	  $value="'".addslashes($value)."'";
	  }
	  $sql.="$value," ;
	  }
	  $sql.=");\r\n\r\n";
	}
	 }
	 return str_replace(',)',')',$sql);
}
/*
	优化修复表结构
*/
public function click()
{
		$do=I('get.do');
		$table=I('get.table');
		switch($do)
		{
		case optimize://优化
		$rs =M()->Query("OPTIMIZE TABLE `$table` ");
		if($rs)
		{
			$this->success("优化表：$table 成功！");
		}
		else
		{
			$this->error("优化表：$table 失败，失败原因".M()->GetError());
		}
		break;
		case repair://修复
		$rs = M()->Query("REPAIR TABLE `$table` ");
		if($rs)
		{
			$this->success("修复表：$table 成功！");
		}
		else
		{
			$this->error("修复表：$table 失败，失败原因".M()->GetError());
		}
		break;
		default://结构
		$dsql=M()->Query("SHOW CREATE TABLE ".$table);
		foreach($dsql as $k=>$v)
		{
		foreach($v as $k1=>$v1)
		{
		$rs=$v1;
		}
		}
		echo trim($rs);
		}
	}
}
?>