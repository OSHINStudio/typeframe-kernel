<?php

class Typeframe_Tag_Timg extends Pagemill_Tag {
	public function __construct($name, array $attributes = array(), \Pagemill_Tag $parent = null, \Pagemill_Doctype $doctype = null) {
		parent::__construct($name, $attributes, $parent, $doctype);
		$this->name = 'img';
		$ratio = $this->hasAttribute('ratio');
		$this->attachPreprocess(new Typeframe_TagPreprocessor_Thumb($ratio));
	}
}
