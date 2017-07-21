<?php
class ReplyAction extends PublicAction{
	public $db;
	public function __construct() {
		parent::__construct();
		$this->db=M("cms_question");
	}
		
    //问题回复
	public function edit(){
            if($this->isPOST()){
               $id=$_POST['id'];
			   $data['state']=1;
			   $data['reply']=$_POST['reply'];
               $this->db->where("id=$id")->save($data); 
               redirect(U('Reply/lists'));
            }
            $id=$this->_get('id');
            $data=$this->db->where("id=$id")->find();
            $this->assign('data',$data);
            $this->display();
	}
	 //问题回复
	public function del(){
             if(IS_GET){               
                $id=$this->_get('id');
                $res=$this->db->where("id=$id")->delete(); 
                if($res){
					$this->success("删除成功！");
                }
            }
	}
	//问题列表
	public function lists(){
            import("@.ORG.Page");
			$count =$this->db->count();
			$Page = new Page($count,10);	
			$pagestr = $Page->show();
			$this->assign('pagestr',$pagestr);
            $list=$this->db->order('posttime desc')->limit($Page->firstRow.','.$Page->listRows)->select(); 
            $this->assign('list',$list);
            $this->display();
	}
}