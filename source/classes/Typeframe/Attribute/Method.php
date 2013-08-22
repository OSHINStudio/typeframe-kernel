<?php

class Typeframe_Attribute_Method extends Pagemill_Attribute_Hidden {
	public function __construct($name, $value, Pagemill_Tag $tag) {
		parent::__construct($name, $value, $tag);
		$tag->attachPreprocess(new Typeframe_TagPreprocessor_Method($value));
		Typeframe_Tag_Scriptonce::Generate(TYPEF_WEB_DIR . '/files/static/jquery/jquery.js', 'text/javascript', $tag);
		Typeframe_Tag_Scriptonce::Generate(TYPEF_WEB_DIR . '/files/static/jquery/jquery.linkmethod.js', 'text/javascript', $tag);
	}
}
