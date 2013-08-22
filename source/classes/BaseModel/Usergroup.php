<?php
/**
 * Base model for the usergroup table
 * This class was automatically generated from the database. Instead of
 * modifying it directly, extend it to add new functionality.
 */
class BaseModel_Usergroup extends Dbi_Model {
	public function __construct() {
		parent::__construct();
		$this->name = 'usergroup';
		$this->prefix = DBI_PREFIX;
		$this->addField('usergroupid', new Dbi_Field('int', array('10', 'unsigned', 'auto_increment'), '', false));
		$this->addField('usergroupname', new Dbi_Field('varchar', array('64'), '', false));
		$this->addIndex('primary', array(
			'usergroupid'
		), 'unique');
		$this->addIndex('usergroupname', array(
			'usergroupname'
		), 'unique');
	}
}
