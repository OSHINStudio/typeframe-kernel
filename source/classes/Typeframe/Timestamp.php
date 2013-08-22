<?php
class Typeframe_Timestamp {
	protected $_action;
	protected $_time;
	public function __construct($action, $time = null) {
		$this->_action = $action;
		$this->_time = (is_null($time) ? microtime(true) : $time);
	}
	public function action() {
		return $this->_action;
	}
	public function time() {
		return $this->_time;
	}
}
