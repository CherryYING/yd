<?php 
class ArthouseModel extends RelationModel{
	protected $tableName='cms_article';
	protected $_link=array(
		'cms_form_house'=>array(
			'mapping_type'=>HAS_ONE,
			'class_name'=>'cms_form_house',
			'foreign_key'=>'aid',
			'mapping_name'=>'cms_form_house'
			//'as_fields'=>'id:hid'
		),
		'cms_form_house_reply'=>array(
			'mapping_type'=>HAS_MANY,
			'class_name'=>'cms_form_house_reply',
			'foreign_key'=>'aid',
			'mapping_name'=>'cms_form_house_reply'
			//'as_fields'=>'id:hid'
		)
	
	);
}