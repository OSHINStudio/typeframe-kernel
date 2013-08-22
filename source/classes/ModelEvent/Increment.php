<?php
/**
 * A model event that increments a value.
 */
class ModelEvent_Increment implements Event_ObserverInterface {
	private $_field;
	private $_keys;
	/**
	 * Increment the specified field.
	 * @param string $field The field to increment.
	 * @param array $keys An array of keys to filter by the current record's values.
	 */
	public function __construct($field, $keys = array()) {
		$this->_field = $field;
		$this->_keys = $keys;
	}
	public function update($record) {
		/*if ( ($this->_overwrite) || (!$record[$this->_field]) ) {
			$record[$this->_field] = Typeframe::Now();
		}*/
		if (!isset($record[$this->_field])) {
			$cls = get_class($record->model());
			$mod = new $cls();
			$mod->order($this->_field . ' DESC');
			$mod->limit(0, 1);
			foreach ($this->_keys as $key) {
				$mod->where("{$key} = ?", $record[$key]);
			}
			$rec = $mod->getFirst();
			$num = $rec[$this->_field] + 1;
			$record[$this->_field] = $num;
		}
	}
}
