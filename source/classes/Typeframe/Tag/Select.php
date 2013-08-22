<?php

class Typeframe_Tag_Select extends Pagemill_Tag_Html_Select {
	public function __construct($name, array $attributes = array(), \Pagemill_Tag $parent = null, \Pagemill_Doctype $doctype = null) {
		parent::__construct($name, $attributes, $parent, $doctype);
		$this->name = 'select';
		$selected = $this->getAttribute('selected');
		$this->removeAttribute('selected');
		// TODO: The namespace shouldn't be hardcoded here.
		$this->setAttribute('pm:selected', $selected);
	}
}
