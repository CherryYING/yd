<?php
class EmptyAction extends Action{
	public function _empty(){
		$this->redirect('Index/index');
	}
}