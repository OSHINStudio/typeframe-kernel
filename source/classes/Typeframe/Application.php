<?php
class Typeframe_Application {
	private $_name;
	private $_base;
	private $_title;
	private $_icon;
	private $_handlerName;
	private $_package;
	private $_category;
	private $_admin;
	public function __construct($name, $base = '', $title = '', $icon = '', $handlerName = '', $extenders = array(), $package = '', $category = '', $admin = '') {
		$this->_name = $name;
		$this->_base = $base;
		$this->_title = $title ? $title : $this->_name;
		$this->_icon = $icon;
		if ($handlerName == '') $handlerName= 'Typeframe_Application_Handler';
		$this->_handlerName = $handlerName;
		$this->_extenders = $extenders;
		$this->_package = $package;
		$this->_category = $category;
		$this->_admin = $admin;
	}
	public function name() {
		return $this->_name;
	}
	public function base() {
		return $this->_base;
	}
	public function title() {
		return $this->_title;
	}
	public function icon() {
		return $this->_icon;
	}
	public function handlerName() {
		return $this->_handlerName;
	}
	public function package() {
		return $this->_package;
	}
	public function extenders() {
		return $this->_extenders;
	}
	public function category() {
		return $this->_category;
	}
	public function admin() {
		return $this->_admin;
	}
}
