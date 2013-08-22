<?php

class Typeframe_Extender {
	private $_path;
	private $_preg;
	private $_redirect;
	public function __construct($path, $preg, $redirect) {
		$this->_path = $path;
		$this->_preg = $preg;
		$this->_redirect = $redirect;
	}
	public function path() {
		return $this->_path;
	}
	public function preg() {
		return $this->_preg;
	}
	public function redirect() {
		return $this->_redirect;
	}
}
