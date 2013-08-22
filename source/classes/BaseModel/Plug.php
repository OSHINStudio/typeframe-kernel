<?php
/**
 * Base model for the plug table
 * This class was automatically generated from the database. Instead of
 * modifying it directly, extend it to add new functionality.
 */
class BaseModel_Plug extends Dbi_Model {
	public function __construct() {
		parent::__construct();
		$this->name = 'plug';
		$this->prefix = DBI_PREFIX;
		$this->addField('plugid', new Dbi_Field('int', array('10', 'unsigned', 'auto_increment'), '', false));
		$this->addField('name', new Dbi_Field('varchar', array('64'), '', false));
		$this->addField('plug', new Dbi_Field('varchar', array('64'), '', false));
		$this->addField('settings', new Dbi_Field('text', array(), '', false));
		$this->addIndex('primary', array(
			'plugid'
		), 'unique');
	}
}
