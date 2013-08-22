<?php
/**
 * Base model for the page table
 * This class was automatically generated from the database. Instead of
 * modifying it directly, extend it to add new functionality.
 */
class BaseModel_Page extends Dbi_Model {
	public function __construct() {
		parent::__construct();
		$this->name = 'page';
		$this->prefix = DBI_PREFIX;
		$this->addField('pageid', new Dbi_Field('int', array('10', 'unsigned', 'auto_increment'), '', false));
		$this->addField('siteid', new Dbi_Field('int', array('10', 'unsigned'), '', false));
		$this->addField('uri', new Dbi_Field('varchar', array('128'), '', false));
		$this->addField('nickname', new Dbi_Field('varchar', array('64'), '', false));
		$this->addField('application', new Dbi_Field('varchar', array('64'), '', false));
		$this->addField('skin', new Dbi_Field('varchar', array('64'), '', false));
		$this->addField('settings', new Dbi_Field('text', array(), '', false));
		$this->addField('datecreated', new Dbi_Field('datetime', array(), '0000-00-00 00:00:00', false));
		$this->addField('datemodified', new Dbi_Field('datetime', array(), '0000-00-00 00:00:00', false));
		$this->addField('driver', new Dbi_Field('varchar', array('128'), '', false));
		$this->addField('rules', new Dbi_Field('text', array(), '', false));
		$this->addIndex('primary', array(
			'pageid'
		), 'unique');
		$this->addIndex('uri', array(
			'uri'
		), 'unique');
	}
}
