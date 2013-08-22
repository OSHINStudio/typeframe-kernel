<?php
class SimpleCss_Document {
	private $_rulesets;
	public function __construct() {
		$this->_rulesets = array();
	}
	/**
	 * Add a ruleset to the document.
	 */
	public function addRuleset(SimpleCss_Ruleset $ruleset) {
		$this->_rulesets[] = $ruleset;
	}
	/**
	 * Get an array of the document's rulesets.
	 * @return SimpleCss_Ruleset[]
	 */
	public function rulesets() {
		return $this->_rulesets;
	}
	/**
	 * Get an array of style rulesets (without at rules).
	 * @return SimpleCss_Ruleset[]
	 */
	public function styleRulesets() {
		$array = array();
		foreach ($this->_rulesets as $ruleset) {
			if (substr($ruleset->declaration(), 0, 1) !== '@') {
				$array[] = $ruleset;
			}
		}
		return $array;
	}
	/**
	 * Get the document as a string.
	 * @return string The generated CSS code.
	 */
	public function toString() {
		$output = '';
		foreach ($this->_rulesets as $ruleset) {
			//$output .= implode(', ', $ruleset->selectors()) . " { " . $ruleset->rules() . " }\n";
			$output .= $ruleset->declaration();
			if ($ruleset->rules()) {
				$output .= " { " . $ruleset->rules() . " }\n";
			}
		}
		return $output;
	}
}
