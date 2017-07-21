<?php
error_reporting(0);
//引入thinkphp配置文件
$config=require(dirname(__FILE__).'/../../App/Conf/config.php');
class Connection{
     //主机
     private $host;
     //数据库的username
     private $user;
     //数据库的password
     private $password;
     //数据库名称
     private $dbname;
     //编码形式
     private $charset="utf8"; 
     //构造函数
     function __construct(){
		global $config;		//声明全局变量
        date_default_timezone_set('PRC');
        $this->host=$config['DB_HOST']; 
		$this->dbname=$config['DB_NAME'];
        $this->user=$config['DB_USER'];
        $this->password=$config['DB_PWD'];
        self::link();
     }
     //数据库的链接
     function link(){
         $link=mysql_connect($this->host,$this->user,$this->password) or die ('数据库链接失败:'.$this->error());
         mysql_select_db($this->dbname,$link) or die("没该数据库：".$this->dbname);
         mysql_query("SET NAMES '$this->charset'");
     }
 
     function query($sql, $type = '') {
         if(!($query = mysql_query($sql))) die($sql);
		 return $query;
     }
 	 //返回一条结果
	 function get_one($query) {
         return mysql_fetch_assoc($query);
     }
	 //返回所有结果
	 function fetch_assoc($query) {
		while($row=mysql_fetch_assoc($query)) {
			$list[]=$row;
		}
        return $list;
     }
	 //操作所影响的记录行数 
     function affected_rows() {
         return mysql_affected_rows();
     }
 	 //取得结果集中的数据
     function result($query, $row) {
         return mysql_result($query, $row);
     }
 	 //取得结果集中行的数目 
     function num_rows($query) {
         return @mysql_num_rows($query);
     }
 
     function num_fields($query) {
         return mysql_num_fields($query);
     }
 
     function free_result($query) {
         return mysql_free_result($query);
     }
 
     function insert_id() {
         return mysql_insert_id();
     }
	 
	 function error() {
         return mysql_error();
     }
 
     function version() {
         return mysql_get_server_info();
     }
 
     function close() {
         return mysql_close();
     }
}
?>