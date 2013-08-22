<?php
class Typeframe_Tag_Html_Body extends Pagemill_Tag {
	public function __construct($name, array $attributes = array(), \Pagemill_Tag $parent = null, \Pagemill_Doctype $doctype = null) {
		parent::__construct($name, $attributes, $parent, $doctype);
		$this->attachPreprocess(new Typeframe_TagPreprocessor_DebugInBody());
		$this->attachPreprocess(new Typeframe_TagPreprocessor_BodyAttributes());
	}
}
