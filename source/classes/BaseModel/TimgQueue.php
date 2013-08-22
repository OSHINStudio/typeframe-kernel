<?php
/**
 * Base model for the timg_queue table
 * This class was automatically generated from the database. Instead of
 * modifying it directly, extend it to add new functionality.
 */
class BaseModel_TimgQueue extends Dbi_Model {
	public function __construct() {
		parent::__construct();
		$this->name = 'timg_queue';
		$this->prefix = DBI_PREFIX;
		$this->addField('src', new Dbi_Field('varchar', array('100'), '', false));
		$this->addField('dst', new Dbi_Field('varchar', array('100'), '', false));
		$this->addField('width', new Dbi_Field('int', array('11'), '', false));
		$this->addField('height', new Dbi_Field('int', array('11'), '', false));
		$this->addField('ratio', new Dbi_Field('tinyint', array('4'), '', false));
		$this->addIndex('primary', array(
			'src', 'dst', 'width', 'height', 'ratio'
		), 'unique');
	}
}
