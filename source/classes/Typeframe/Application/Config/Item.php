<?php
class Typeframe_Application_Config_Item {
	private $_name;
	private $_caption;
	private $_type;
	private $_defaultValue;
	private $_options;
	public function __construct($name, $caption, $type, $defaultValue, array $options) {
		$this->_name = $name;
		$this->_caption = $caption;
		$this->_type = $type;
		$this->_defaultValue = $defaultValue;
		$this->_options = $options;
	}
	public function name() {
		return $this->_name;
	}
	public function caption() {
		return $this->_caption;
	}
	public function type() {
		return $this->_type;
	}
	public function defaultValue() {
		return $this->_defaultValue;
	}
	public function options() {
		return $this->_options;
	}
}
