<?php
/**
 * A model event that sets a friendly URL based on another field (eg., an article's title).
 */
class ModelEvent_FriendlyUrl implements Event_ObserverInterface {
	private $_sourceField;
	private $_urlField;
	private $_overwrite;
	private $_unique;
	/**
	 * @param string $sourceField The field containing the original text.
	 * @param string $urlField The field to set with the friendly URL text.
	 * @param boolean $overwrite If false, existing values will not be overwritten.
	 * @param boolean $unique If true, make sure that no other record in the model is using the generated URL.
	 */
	public function __construct($sourceField, $urlField, $overwrite = false, $unique = true) {
		$this->_sourceField = $sourceField;
		$this->_urlField = $urlField;
		$this->_overwrite = $overwrite;
		$this->_unique = $unique;

	}
	public function update($record) {
		if ( ($this->_overwrite) || (!$record[$this->_urlField]) ) {
			if ($record[$this->_sourceField]) {
				$baseUrl = makeFriendlyUrlText($record[$this->_sourceField]);
				$url = $baseUrl;
				if ($this->_unique) {
					$cls = get_class($record->model());
					$mod = new $cls();
					$num = 1;
					$mod->where($this->_urlField . ' = ?', $url);
					while ($mod->count()) {
						$num++;
						$url = $baseUrl . '-' . $num;
						$mod = new $cls();
						$mod->where($this->_urlField . ' = ?', $url);
					}
				}
				$record[$this->_urlField] = $url;
			}
		}
	}
}
