<?php
class Typeframe_Tag_Scriptonce extends Pagemill_Tag_Html_Script {
	private static $_scripts = array();
	private function _findHead() {
		$parent = $this->parent();
		if (is_null($parent)) return null;
		$top = null;
		while (!is_null($parent)) {
			$top = $parent;
			if ($top->name() == 'html' || $top->name() == 'pm:html') break;
			if ($parent->name() == 'head' || $parent->name() == 'pm:head') {
				return $parent;
			}
			$parent = $parent->parent();
		}
		foreach ($top->children() as $child) {
			if (is_a($child, 'Pagemill_Tag')) {
				if ($child->name() == 'head' || $child->name() == 'pm:head') {
					return $child;
				}
			}
		}
		return null;
	}
	public function __construct($name, array $attributes = array(), \Pagemill_Tag $parent = null, \Pagemill_Doctype $doctype = null) {
		parent::__construct($name, $attributes, $parent, $doctype);
		$head = $this->_findHead();
		if (!is_null($head)) {
			$this->detach();
			$head->appendChild($this);
		}
	}
	public function output(\Pagemill_Data $data, \Pagemill_Stream $stream) {
		$src = $data->parseVariables($this->getAttribute('src'));
		if (!in_array($src, self::$_scripts)) {
			self::$_scripts[] = $src;
			$this->name = 'script';
			parent::output($data, $stream);
		}
	}
	public static function AlreadyLoaded($array) {
		self::$_scripts = array_merge(self::$_scripts, $array);
	}
	public static function Generate($src, $type, Pagemill_Tag $parent) {
		$tag = new Typeframe_Tag_Scriptonce('scriptonce', array('src' => $src, 'type' => $type), $parent);
		return $tag;
	}
}
