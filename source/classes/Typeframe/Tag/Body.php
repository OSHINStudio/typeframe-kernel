<?php
/**
 * The Typeframe/Pagemill body tag (e.g., pm:body) that enables skins.
 */
class Typeframe_Tag_Body extends Pagemill_Tag {
	public function __construct($name, array $attributes = array(), Pagemill_Tag $parent = null, Pagemill_Doctype $doctype = null) {
		parent::__construct($name, $attributes, $parent, $doctype);
		$this->parent()->attachPreprocess(new Typeframe_TagPreprocessor_Export('body', $this));
	}
	public function output(Pagemill_Data $data, Pagemill_Stream $stream) {
		$this->name = 'body';
		//parent::output($data, $stream);
		foreach ($this->children() as $child) {
			$child->process($data, $stream);
		}
	}
}
