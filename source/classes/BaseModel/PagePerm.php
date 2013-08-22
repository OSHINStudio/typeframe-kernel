<?php
/**
 * Base model for the page_perm table
 * This class was automatically generated from the database. Instead of
 * modifying it directly, extend it to add new functionality.
 */
class BaseModel_PagePerm extends Dbi_Model {
	public function __construct() {
		parent::__construct();
		$this->name = 'page_perm';
		$this->prefix = DBI_PREFIX;
		$this->addField('pageid', new Dbi_Field('int', array('10', 'unsigned'), '0', false));
		$this->addField('usergroupid', new Dbi_Field('int', array('10', 'unsigned'), '0', false));
		$this->addIndex('primary', array(
			'pageid', 'usergroupid'
		), 'unique');
	}
}
