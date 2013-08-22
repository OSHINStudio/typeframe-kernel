<?php
class Typeframe_Application_Config {
	private $_name;
	private $_items;
	private $_application;
	private $_redirect;
	/**
	 * 
	 * @param string $name The name of the config set as it will appear in control panels
	 * @param Typeframe_Application_Config_Item[] $items Configuration items
	 * @param string $applicatgion The application name
	 */
	public function __construct($name, array $items, $application, $redirect) {
		$this->_name = $name;
		$this->_items = $items;
		$this->_application = $application;
		$this->_redirect = $redirect;
	}
	public function name() {
		return $this->_name;
	}
	public function items() {
		return $this->_items;
	}
	public function application() {
		return $this->_application;
	}
	public function redirect() {
		return $this->_redirect;
	}
}
