<?php
/**
 * A model event that updates a specified timestamp field.
 */
class ModelEvent_Timestamp implements Event_ObserverInterface {
	private $_field;
	private $_overwrite;
	/**
	 * Set the timestamp on the specified field.
	 * @param string $field The field to set with the current timestamp.
	 * @param boolean $overwrite If false, existing values will not be overwritten.
	 */
	public function __construct($field, $overwrite = true) {
		$this->_field = $field;
		$this->_overwrite = $overwrite;
	}
	public function update($record) {
		if ( ($this->_overwrite) || (!$record[$this->_field]) ) {
			$record[$this->_field] = Typeframe::Now();
		}
	}
}
