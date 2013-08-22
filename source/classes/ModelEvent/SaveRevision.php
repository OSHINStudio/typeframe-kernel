<?php
/**
 * A model event for saving the current record in a revision table before updating it.
 */
class ModelEvent_SaveRevision implements Event_ObserverInterface {
	private $_revisionModelClassName;
	private $_dateModifiedField;
	/**
	 * Archive a copy of the current record if it already exists.
	 *
	 * @param string $revisionModelClassName The name of the model that stores
	 * revisions. If not specified, the class will attempt to use
	 * [ThisModelName]Revision. The revision model's fields should include the
	 * parent's primary keys, a datemodified field, and a data field.
	 *
	 * @param string $dateModifiedField The field in the original model that
	 * stores the modified time. If not specified, the class will attempt to
	 * use "datemodified".
	 */
	public function __construct($revisionModelClassName = '', $dateModifiedField = 'datemodified') {
		$this->_revisionModelClassName = $revisionModelClassName;
		$this->_dateModifiedField = $dateModifiedField;
	}
	public function update($record) {
		if ($record->exists()) {
			// Save revision
			$cls = $this->_revisionModelClassName;
			if (!$cls) $cls = get_class($record->model()) . 'Revision';
			if (class_exists($cls)) {
				$revMod = new $cls();
				$revRec = new Dbi_Record($revMod);
				$primary = $record->model()->primary();
				if (count($primary['fields']) != 1) {
					throw new Exception("Model must have exactly one field in primary key");
				}
				if ($record->model()->field($primary['fields'][0])) {
					// Foreign key in revision table matches source table's primary key
					$revKey = $primary['fields'][0];
				} else if ($record->model()->field($record->model()->name() . $primary['fields'][0])) {
					// Foreign key in revision table matches source table's name + "id"
					$revKey = $record->model()->name() . $primary['fields'][0];
				} else {
					throw new Exception('Could not identify foreign key in revision table');
				}
				$revRec[$revKey] = $record[$primary['fields'][0]];
				$init = $record->initArray();
				$revRec['data'] = $init;
				$revRec['datemodified'] = $init[$this->_dateModifiedField];
				$revRec->save();
			} else {
				throw new Exception("{$cls} is not a valid model class");
			}
		}
	}
}
