<?php
/**
 * Base model for the sessions table
 * This class was automatically generated from the database. Instead of
 * modifying it directly, extend it to add new functionality.
 */
class BaseModel_Sessions extends Dbi_Model {
	public function __construct() {
		parent::__construct();
		$this->name = 'sessions';
		$this->prefix = DBI_PREFIX;
		$this->addField('sid', new Dbi_Field('varchar', array('32'), '', false));
		$this->addField('ip_addr', new Dbi_Field('varchar', array('39'), '', false));
		$this->addField('uid', new Dbi_Field('int', array('11'), '', false));
		$this->addField('expires', new Dbi_Field('int', array('11', 'unsigned'), '0', false));
		$this->addField('session_data', new Dbi_Field('longtext', array(), '', false));
		$this->addIndex('primary', array(
			'sid', 'ip_addr'
		), 'unique');
		$this->addIndex('uid', array(
			'uid'
		), '');
	}
}
