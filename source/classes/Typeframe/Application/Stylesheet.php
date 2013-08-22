<?php

class Typeframe_Application_Stylesheet {
	private $_path;
	private $_stylesheet;
	public function __construct($path, $sheet) {
		$this->_path = $path;
		$this->_stylesheet = $sheet;
	}
	public function path() {
		return $this->_path;
	}
	public function stylesheet() {
		return $this->_stylesheet;
	}
}
