<?php
class DelcacheAction extends PublicAction{
	public function index(){
		header("Content-type: text/html; charset=utf-8");
		$dirs = array(RUNTIME_PATH.'Cache/');
		@mkdir(RUNTIME_PATH,0777,true);
		foreach($dirs as $value) {
		   $this->rmdirr($value);
		}
		echo "系统缓存清除成功！";
		//$this->success('系统缓存清除成功！');
	}

	public function rmdirr($dirname) {
		if (!file_exists($dirname)) {
			return false;
		}
		if (is_file($dirname) || is_link($dirname)) {
			return unlink($dirname);
		}
		$dir = dir($dirname);
		if($dir){
			while (false !== $entry = $dir->read()) {
				if ($entry == '.' || $entry == '..') {
					continue;
				}
				//递归
				$this->rmdirr($dirname . DIRECTORY_SEPARATOR . $entry);
			}
		}
			$dir->close();
			return rmdir($dirname);
	}
	/*
		清除数据
	*/
	public function del_data(){
		$model = new Model();
		$rs=$model->query('truncate twotree_wechatuser');
		$model->query('truncate twotree_user_relation');
		$model->query('truncate twotree_user_address');
		$model->query('truncate twotree_order_info');
		$model->query('truncate twotree_order_goods');
		$model->query('truncate twotree_apply_money');
		$model->query('truncate twotree_score_log');
		$model->query('truncate twotree_coupon');
		$dirs= array('./Data/upload/qrcode/','./Data/upload/headimg/');
		foreach($dirs as $value) {
		   	$file_list=scandir($value);
			foreach($file_list as $val){
				if(!is_dir($val)){
					unlink($value.$val);
				}
			}
			unset($file_list);
		}
		echo '数据清除成功！';
	}
	private  function get_url() {
		$sys_protocal = isset($_SERVER['SERVER_PORT']) && $_SERVER['SERVER_PORT'] == '443' ? 'https://' : 'http://';
		$php_self = $_SERVER['PHP_SELF'] ? $_SERVER['PHP_SELF'] : $_SERVER['SCRIPT_NAME'];
		$path_info = isset($_SERVER['PATH_INFO']) ? $_SERVER['PATH_INFO'] : '';
		$relate_url = isset($_SERVER['REQUEST_URI']) ? $_SERVER['REQUEST_URI'] : $php_self.(isset($_SERVER['QUERY_STRING']) ? '?'.$_SERVER['QUERY_STRING'] : $path_info);
		return $sys_protocal.(isset($_SERVER['HTTP_HOST']) ? $_SERVER['HTTP_HOST'] : '').$relate_url;
	 }

}