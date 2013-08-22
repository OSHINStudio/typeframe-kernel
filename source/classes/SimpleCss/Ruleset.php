<?php
class SimpleCss_Ruleset {
	private $_selectors;
	private $_rules;
	private $_declaration;
	/**
	 * @param string|array $selectors The CSS selector for this ruleset, e.g., "p, ul li" or array("p", "ul li").
	 * @param string $rules The CSS rules (e.g., "font-size: 10pt; color: black").
	 */
	public function __construct($selectors, $rules) {
		if (is_array($selectors)) {
			$this->_selectors = $selectors;
		} else {
			if (substr($selectors, 0, 1) != '@') {
				$this->_selectors = array();
				$array = explode(',', $selectors);
				foreach ($array as $selector) {
					$this->_selectors[] = trim($selector);
				}
			} else {
				$this->_selectors = array();
			}
			$this->_declaration = $selectors;
		}
		$this->_rules = $rules;
	}
	public function declaration() {
		return $this->_declaration;
	}
	/**
	 * Get an araay of the rule's selectors.
	 * @return array
	 */
	public function selectors() {
		return $this->_selectors;
	}
	/**
	 * Get the CSS rules.
	 * @return string
	 */
	public function rules() {
		return $this->_rules;
	}
}
