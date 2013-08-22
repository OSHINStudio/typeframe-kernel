<?php
/**
 * Base model for the log table
 * This class was automatically generated from the database. Instead of
 * modifying it directly, extend it to add new functionality.
 */
class BaseModel_Log extends Dbi_Model {
	public function __construct() {
		parent::__construct();
		$this->name = 'log';
		$this->prefix = DBI_PREFIX;
		$this->addField('logid', new Dbi_Field('int', array('10', 'unsigned', 'auto_increment'), '', false));
		$this->addField('userid', new Dbi_Field('int', array('10', 'unsigned'), '0', false));
		$this->addField('ipaddress', new Dbi_Field('varchar', array('24'), '', false));
		$this->addField('package', new Dbi_Field('varchar', array('32'), '', false));
		$this->addField('application', new Dbi_Field('varchar', array('32'), '', false));
		$this->addField('action', new Dbi_Field('varchar', array('255'), '', false));
		$this->addField('logdate', new Dbi_Field('datetime', array(), '0000-00-00 00:00:00', false));
		$this->addField('full_desc', new Dbi_Field('text', array(), '', false));
		$this->addIndex('primary', array(
			'logid'
		), 'unique');
		$this->addIndex('userid', array(
			'userid', 'logdate'
		), '');
		$this->addIndex('ipaddress', array(
			'ipaddress'
		), '');
	}
}
