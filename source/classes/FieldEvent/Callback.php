<?php
class FieldEvent_Callback implements Event_ObserverInterface {
	private $_function;
	public function __construct($function) {
		$this->_function = $function;
	}
	public function update($record) {
		$function = $this->_function;
		$function($record);
	}
}
