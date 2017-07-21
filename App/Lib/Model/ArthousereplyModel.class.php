<?php 
class ArthousereplyModel extends RelationModel{
	protected $tableName='cms_form_house';
	protected $_link=array(
		'cms_form_house_reply'=>array(
			'mapping_type'=>HAS_MANY,
			'class_name'=>'cms_form_house_reply',
			'foreign_key'=>'hid',
			'mapping_name'=>'cms_form_house_reply'
			//'as_fields'=>'id:hid'
		)
	
	);
}