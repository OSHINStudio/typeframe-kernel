<?php
class Dbi_Recordset_MySql extends Dbi_Recordset {
	private $_model;
	private $_result;
	private $_key = false;
	private $_current;
	private $_cache = array();
	public function __construct(Dbi_Model $model, $result) {
		$this->_model = $model;
		$this->_result = $result;
		$this->_key = false;
		$this->_current = null;
	}
	public function count() {
		return mysql_num_rows($this->_result);
	}
	public function rewind() {
		if (mysql_num_rows($this->_result) > 0) {
			mysql_data_seek($this->_result, 0);
			$this->_key = -1;
			$this->next();
		}
	}
	public function current() {
		return $this->_current;
	}
	public function key() {
		return $this->_key;
	}
	private function _recurseRecordKeys(&$sub, $key, $value) {
		$sub[$key] = $value;
		$parts = explode('.', $key, 2);
		if (count($parts) > 1) {
			if (!isset($sub[$parts[0]])) {
				$sub[$parts[0]] = array();
			}
			$this->_recurseRecordKeys($sub[$parts[0]], $parts[1], $value);
			unset($sub[$key]);
		}
	}
	public function next() {
		if (isset($this->_cache[$this->_key + 1])) {
			$this->_current = $this->_cache[$this->_key + 1];
			$this->_key++;
		} else {
			if ($row = mysql_fetch_assoc($this->_result)) {
				$joined = array();
				foreach ($row as $key => $value) {
					if (is_null($value)) {
						unset($row[$key]);
					} else {
						$parts = explode('.', $key, 2);
						if (count($parts) > 1) {
							if (substr($parts[0], 0, 3) != '___') {
								if (!isset($joined[$parts[0]])) {
									$joined[$parts[0]] = array();
								}
								//$joined[$parts[0]][$parts[1]] = $value;
								$this->_recurseRecordKeys($joined[$parts[0]], $parts[1], $value);
							} else {
								$this->_recurseRecordKeys($joined, $parts[1], $value);
							}
							unset($row[$key]);
						}
					}
				}
				$row = array_merge($row, $joined);
				$record = new Dbi_Record($this->_model, $row);
				$components = $this->_model->components();
				$subqueries = $components->subqueries;
				foreach ($subqueries as $subquery) {
					$cls = $subquery['model'];
					$m = new $cls();
					$tokens = Dbi_Sql_Tokenizer::Tokenize($subquery['statement']);
					// TODO: This token replacement is apparently limited in that it
					// will only work for two levels of subqueries. I say "apparently"
					// because I'm not completely sure how or why it works.
					foreach ($tokens as &$t) {
						$t = str_replace("{$subquery['name']}.", $m->name(). ".", $t);
						$words = explode('.', $t);
						if ( (count($words) == 2) && ($words[0] == $this->_model->name()) && ($this->_model->name() != $m->name()) ) {
							array_shift($words);
							$t = '?';
							$subquery['args'][] = $record[$words[0]];
						} else if ( (count($words) == 1) && ($this->_model->field($words[0])) ) {
							$t = '?';
							$subquery['args'][] = $record[$words[0]];
						}
					}
					$statement = implode(' ', $tokens);
					$args = array_merge(array($statement), $subquery['args']);
					call_user_func_array(array($m, 'where'), $args);
					$record[$subquery['name']] = $m;
				}
				$this->_current = $record;
				$this->_key++;
				$this->_cache[$this->_key] = $this->_current;
			} else {
				$this->_current = null;
				$this->_key = false;
			}
		}
	}
	public function valid() {
		return ($this->_key !== false);
	}
	public function offsetExists($offset) {
		$index = (int)$offset;
		return ($index >= 0 && $index < mysql_num_rows($this->_result));
	}
	public function offsetGet($offset) {
		$index = (int)$offset;
		if ($this->_key !== false && $this->_key == $index) return $this->_current;
		$currentKey = $this->_key;
		mysql_data_seek($this->_result, $index);
		$this->next();
		$record = $this->_current;
		$this->_key = $currentKey;
		if ($this->_key !== false) mysql_data_seek($this->_result, $this->_key);
		return $record;
	}
}
