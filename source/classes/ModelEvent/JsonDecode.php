<?php
/**
 * A model event that decodes a field (or array of fields) from JSON format.
 * The field's value must be a string representing a JSON object, array, or
 * string. If the field contains an array or object, it will not be modified.
 */
class ModelEvent_JsonDecode implements Event_ObserverInterface {
	private $_fields;
	/**
	 * Decode field data from JSON format.
	 * @param string|array $field The fields to encode.
	 */
	public function __construct($fields) {
		$this->_fields = Bam_Functions::ListToArray($fields);
	}
	public function update($record) {
		foreach ($this->_fields as $field) {
			if ($record[$field]) {
				if (is_scalar($record[$field])) {
					$char = substr($record[$field], 0, 1);
					if ( ($char == '[') || ($char == '{') || ($char == '"') ) {
						$record[$field] = json_decode($record[$field], true);
					}
				}
			} else {
				$record[$field] = array();
			}
		}
	}
}
