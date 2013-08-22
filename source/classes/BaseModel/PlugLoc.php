<?php
/**
 * Base model for the plug_loc table
 * This class was automatically generated from the database. Instead of
 * modifying it directly, extend it to add new functionality.
 */
class BaseModel_PlugLoc extends Dbi_Model {
	public function __construct() {
		parent::__construct();
		$this->name = 'plug_loc';
		$this->prefix = DBI_PREFIX;
		$this->addField('locid', new Dbi_Field('int', array('10', 'unsigned', 'auto_increment'), '', false));
		$this->addField('plugid', new Dbi_Field('int', array('10', 'unsigned'), '', false));
		$this->addField('skin', new Dbi_Field('varchar', array('32'), '', false));
		$this->addField('socket', new Dbi_Field('varchar', array('32'), '', false));
		$this->addField('sortnum', new Dbi_Field('int', array('11'), '', false));
		$this->addField('rules', new Dbi_Field('varchar', array('255'), '', false));
		$this->addIndex('primary', array(
			'locid'
		), 'unique');
	}
}
