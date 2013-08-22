<?php

class Typeframe_Attribute_Thumb extends Pagemill_Attribute_Hidden {
	public function __construct($name, $value, Pagemill_Tag $tag) {
		parent::__construct($name, $value, $tag);
		if (strtolower($this->value) != 'fixed' && strtolower($this->value) != 'ratio') {
			throw new Exception("Value of {$name} must be 'fixed' or 'ratio'");
		}
		$ratio = (strtolower($this->value) == 'ratio');
		$tag->attachPreprocess(new Typeframe_TagPreprocessor_Thumb($ratio));
	}
}
