<?php
/**
 * Base model for the config table
 * This class was automatically generated from the database. Instead of
 * modifying it directly, extend it to add new functionality.
 */
class BaseModel_Config extends Dbi_Model {
	public function __construct() {
		parent::__construct();
		$this->name = 'config';
		$this->prefix = DBI_PREFIX;
		$this->addField('configname', new Dbi_Field('varchar', array('64'), '', false));
		$this->addField('configvalue', new Dbi_Field('text', array(), '', false));
		$this->addIndex('primary', array(
			'configname'
		), 'unique');
	}
}
