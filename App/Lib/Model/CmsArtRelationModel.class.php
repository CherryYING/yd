<?php 
class CmsArtRelationModel extends RelationModel{
	protected $tableName='cms_article';
	protected $_link=array(
		'cms_reply'=>array(
			'mapping_type'=>HAS_MANY,
			'class_name'=>'cms_reply',
			'foreign_key'=>'aid',
			'mapping_name'=>'cms_reply',
			'mapping_fields'=>'id,uname,aid,reply'
		)
	
	);
}