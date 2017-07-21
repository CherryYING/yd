<?php
class TagLibTree extends TagLib{
	protected $tags = array(
		'twotree'=>array('attr'=>'limit,order,sort','close'=>1),
		'friendlink'=>array('attr'=>'limit','close'=>1),
		'ttselect'=>array('attr'=>'table,where,order,limit,id,page,sql,field,key,mod,debug','level'=>3)
		);
	
	public function _twotree($attr,$content){
		$attr = $this->parseXmlAttr($attr);
		$limit = $attr['limit'];
		$order = $attr['order'];
		$sort = $attr['sort'];
		$str .= '<?php ';
		$str .= '$field=array("id","title","fname","descrip","posttime","spic","content","fid");';
		$str .= '$where=array("fid"=>'.$sort.');';
		$str .= '$_news=M("cms_article")->where($where)->field($field)->limit('.$limit.')->order("'.$order.'")->select();';
		$str .= 'foreach($_news as $_val):';
		$str .= 'extract($_val);';
		$str .= '$url=U("read",array("id"=>$id));?>';
		$str .= $content;
		$str .= '<?php endforeach ?>';
		return $str;
	}
	public function _friendlink($attr,$content){
		$attr = $this->parseXmlAttr($attr);
		$limit = $attr['limit'];
		$str .= '<?php ';
		$str .= '$field=array("linkname","linkurl","linklogo");';
		$str .= '$where=array("linktype"=>"1");';
		$str .= '$_links=M("cms_friendlink")->where($where)->field($field)->order("linklist DESC")->limit('.$limit.')->select();';
		$str .= 'foreach($_links as $_val):';
		$str .= 'extract($_val); ?>';
		$str .= $content;
		$str .= '<?php endforeach ?>';
		return $str;
	}
	public function _ttselect($attr,$content){
         $tag       = $this->parseXmlAttr($attr,'select');
         $table     =!empty($tag['table'])?$tag['table']:'';
         $order     =!empty($tag['order'])?$tag['order']:'';
         $limit     =!empty($tag['limit'])?intval($tag['limit']):'';
         $id        =!empty($tag['id'])?$tag['id']:'r';
         $where     =!empty($tag['where'])?$tag['where']:' 1 ';
         $key        =!empty($tag['key'])?$tag['key']:'i';
         $mod        =!empty($tag['mod'])?$tag['mod']:'2';
         $page      =!empty($tag['page'])?$tag['page']:false;
         $sql         =!empty($tag['sql'])?$tag['sql']:'';
         $field     =!empty($tag['field'])?$tag['field']:'';
         $debug     =!empty($tag['debug'])?$tag['debug']:false;
         $this->comparison['noteq']= '<>';
         $this->comparison['sqleq']= '=';
         $where     =$this->parseCondition($where);
         $sql         =$this->parseCondition($sql);
         $parsestr.='<?php $m=M("'.$table.'");';
         
         if($sql){
            if($page){
                $limit=$limit?$limit:10;//如果有page，没有输入limit则默认为10
                $parsestr.='import("@.ORG.Page");';
                $parsestr.='$count=count($m->query("'.$sql.'"));';
                $parsestr.='$p = new Page ( $count, '.$limit.' );';
                $parsestr.='$sql.="'.$sql.'";';

                $parsestr.='$sql.=" limit ".$p->firstRow.",".$p->listRows."";';
                $parsestr.='$ret=$m->query($sql);';
                $parsestr.='$pages=$p->show();';
                //$parsestr.='dump($count);dump($sql);';
            }else{
                $sql.=$limit?(' limit '.$limit):'';
                $parsestr.='$ret=$m->query("'.$sql.'");'; 
            }
         }else{
             if($page){
                 $limit=$limit?$limit:10;//如果有page，没有输入limit则默认为10
                 $parsestr.='import("@.ORG.Page");'; 
                 $parsestr.='$count=$m->where("'.$where.'")->count();';
                 $parsestr.='$p = new Page ( $count, '.$limit.' );';
                 $parsestr.='$ret=$m->field("'.$field.'")->where("'.$where.'")->limit($p->firstRow.",".$p->listRows)->order("'.$order.'")->select();';
                 $parsestr.='$pages=$p->show();';
             }else{
                 $parsestr.='$ret=$m->field("'.$field.'")->where("'.$where.'")->order("'.$order.'")->limit("'.$limit.'")->select();'; 
             }            
         }      
         if($debug!=false){
            $parsestr.='dump($ret);dump($m->getLastSql());';
         }
         $parsestr.= 'if ($ret): $'.$key.'=0;';
         $parsestr.= 'foreach($ret as $key=>$'.$id.'):';
         $parsestr.= '++$'.$key.';$mod = ($'.$key.' % '.$mod.' );?>';
         $parsestr.= $this->tpl->parse($content);      
         $parsestr.= '<?php endforeach;endif;?>';       
         return $parsestr;
         
    }
}