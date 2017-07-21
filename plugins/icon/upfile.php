<?php
$file_src = "../../Data/upload/icon/src.png"; 
$filename162="../../Data/upload/icon/1.png"; 
$filename48= "../../Data/upload/icon/2.png"; 
$filename20="../../Data/upload/icon/3.png";   
/*$file_src = "/Data/upload/icon/src.png"; 
$filename162 = "/Data/upload/icon/".$userInfo['uid']."-m-icon.jpg"; 
$filename48 = "/Data/upload/icon/".$userInfo['uid']."-c-icon.jpg"; 
$filename20 = "/Data/upload/icon/".$userInfo['uid']."-s-icon.jpg";   */

$src=base64_decode($_POST['pic']);
$pic1=base64_decode($_POST['pic1']);   
$pic2=base64_decode($_POST['pic2']);  
$pic3=base64_decode($_POST['pic3']);  

if($src) {
	file_put_contents($file_src,$src);
}

file_put_contents($filename162,$pic1);
file_put_contents($filename48,$pic2);
file_put_contents($filename20,$pic3);

$rs['status'] = 1;

print json_encode($rs);

?>
