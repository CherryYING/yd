<?php
include ('./class.phpmailer.php');
$config = array(
        'host'=>'smtp.163.com',
        'port'=>'25',
        'user'=>'shaaoboo@163.com',
        'passwd'=>'19900314',
        'from'=>'shaaoboo@163.com',
        'fromname'=>'少博',
        
        );
$subject = 'this is a test mail';
$body = '<table style="background:#dfdfdf"><tr><td>测试内容</td></tr><tr><td>这是内容</td></tr></table>';
$address='763300134@qq.com';
$username='本人';

$mail = new PHPMailer();
$mail->CharSet = 'gb2312';// 设置邮件的字符编码
$mail->IsSMTP();// 使用SMTP方式发送
$mail->Host = $config['host'];// 您的企业邮局域名
$mail->Port = $config['port'];

$mail->From = $config['from'];
$mail->FromName = $config['fromname'];
$mail->SMTPAuth = true;// 启用SMTP验证功能

$mail->Username = $config['user'];
$mail->Password = $config['passwd'];
//$mail->AddAttachment("2012.jpg"); // 添加附件
$mail->Subject=$subject;//邮件标题
$mail->AltBody="text/html";//附加信息，可以省略
//$mail->IsHTML(true); // set email format to HTML //是否使用HTML格式
$mail->MsgHTML($body);


$mail->AddAddress($address,$username);

if(!$mail->Send())
{
    echo "Mail Error :".$mail->ErrorInfo;
}else
{
    echo "恭喜发送成功！";
}
