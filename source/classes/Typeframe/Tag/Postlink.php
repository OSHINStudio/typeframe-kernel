<?php
class Typeframe_Tag_Postlink extends Pagemill_Tag {
	public function __construct($name, array $attributes = array(), \Pagemill_Tag $parent = null, \Pagemill_Doctype $doctype = null) {
		parent::__construct($name, $attributes, $parent, $doctype);
		$this->name = 'a';
		$this->setAttribute('data-confirm', $this->getAttribute('confirm'));
		$this->removeAttribute('confirm');
		$this->attachPreprocess(new Typeframe_TagPreprocessor_Method('post'));
		Typeframe_Tag_Scriptonce::Generate(TYPEF_WEB_DIR . '/files/static/jquery/jquery.js', 'text/javascript', $this);
		Typeframe_Tag_Scriptonce::Generate(TYPEF_WEB_DIR . '/files/static/jquery/jquery.linkmethod.js', 'text/javascript', $this);
	}
}
