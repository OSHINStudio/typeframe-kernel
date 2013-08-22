<?php
/**
 * The Typeframe/Pagemill head tag (e.g., pm:head) that enables skins.
 */
class Typeframe_Tag_Head extends Pagemill_Tag {
	public function __construct($name, array $attributes = array(), Pagemill_Tag $parent = null, Pagemill_Doctype $doctype = null) {
		parent::__construct($name, $attributes, $parent, $doctype);
		$this->parent()->attachPreprocess(new Typeframe_TagPreprocessor_Export('head', $this));
	}
	public function output(Pagemill_Data $data, Pagemill_Stream $stream) {
		foreach ($this->children() as $child) {
			$child->process($data, $stream);
		}
		//parent::output($data, $stream);
	}
}
