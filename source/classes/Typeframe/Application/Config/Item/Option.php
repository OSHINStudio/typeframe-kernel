<?php
class Typeframe_Application_Config_Item_Option {
	private $_value;
	private $_caption;
	public function __construct($value, $caption) {
		$this->_value = $value;
		$this->_caption = $caption;
	}
	public function value() {
		return $this->_value;
	}
	public function caption() {
		return $this->_caption;
	}
}
