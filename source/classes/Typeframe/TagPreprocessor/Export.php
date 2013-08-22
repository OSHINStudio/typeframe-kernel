<?php
class Typeframe_TagPreprocessor_Export extends Pagemill_TagPreprocessor {
	private $_name;
	private $_tag;
	private static $_exports = array();
	public function __construct($name, Pagemill_Tag $tag) {
		$this->_name = $name;
		$this->_tag = $tag;
	}
	public function process(Pagemill_Tag $tag, Pagemill_Data $data, Pagemill_Stream $stream) {
		if (!isset(self::$_exports[$this->_name])) self::$_exports[$this->_name] = array();
		self::$_exports[$this->_name][] = $this->_tag;
	}
	public static function Peek($name) {
		return (isset(self::$_exports[$name]) ? self::$_exports[$name][count(self::$_exports[$name]) - 1] : null);
	}
	public static function Pop($name) {
		return self::Peek($name);
		return (isset(self::$_exports[$name]) ? array_pop(self::$_exports[$name]) : null);
	}
}
