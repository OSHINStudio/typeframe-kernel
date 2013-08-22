<?php
class Typeframe_Application_Plugin {
	private $_name;
	private $_className;
	private $_application;
	public function __construct($name, $class, $application) {
		$this->_name = $name;
		$this->_className = $class;
		$this->_application = $application;
	}
	public function name() {
		return $this->_name;
	}
	public function className() {
		return $this->_className;
	}
	public function application() {
		return $this->_application;
	}
}
