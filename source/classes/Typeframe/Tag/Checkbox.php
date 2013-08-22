<?php
class Typeframe_Tag_Checkbox extends Pagemill_Tag {
	public function __construct($name, array $attributes = array(), \Pagemill_Tag $parent = null, \Pagemill_Doctype $doctype = null) {
		parent::__construct($name, $attributes, $parent, $doctype);
		$this->name = 'input';
		$this->attributes['type'] = 'checkbox';
		$this->attachPreprocess(new Pagemill_TagPreprocessor_CheckValue($this->attributes['checked']));
	}	
}
