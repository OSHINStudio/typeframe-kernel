<?php
/**
 * Base model for the user table
 * This class was automatically generated from the database. Instead of
 * modifying it directly, extend it to add new functionality.
 */
class BaseModel_User extends Dbi_Model {
	public function __construct() {
		parent::__construct();
		$this->name = 'user';
		$this->prefix = DBI_PREFIX;
		$this->addField('userid', new Dbi_Field('int', array('10', 'unsigned', 'auto_increment'), '', false));
		$this->addField('username', new Dbi_Field('varchar', array('64'), '', false));
		$this->addField('auth', new Dbi_Field('varchar', array('16'), '', false));
		$this->addField('passhash', new Dbi_Field('varchar', array('64'), '', false));
		$this->addField('salt', new Dbi_Field('varchar', array('32'), '', false));
		$this->addField('hashtype', new Dbi_Field('varchar', array('16'), 'md5', false));
		$this->addField('email', new Dbi_Field('varchar', array('64'), '', false));
		$this->addField('firstname', new Dbi_Field('varchar', array('64'), '', false));
		$this->addField('lastname', new Dbi_Field('varchar', array('64'), '', false));
		$this->addField('usergroupid', new Dbi_Field('int', array('10', 'unsigned'), '0', false));
		$this->addField('confirmed', new Dbi_Field('tinyint', array('4'), '0', false));
		$this->addField('regdate', new Dbi_Field('datetime', array(), '0000-00-00 00:00:00', false));
		$this->addField('lastrequest', new Dbi_Field('datetime', array(), '0000-00-00 00:00:00', false));
		$this->addIndex('primary', array(
			'userid'
		), 'unique');
		$this->addIndex('username', array(
			'username'
		), 'unique');
		$this->addIndex('email', array(
			'email'
		), '');
	}
}
