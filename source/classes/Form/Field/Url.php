<?php
/**
 * Validate URL fields in forms.
 */
class Form_Field_Url extends Form_Field {
	private $_defaultScheme;
	public function __construct($defaultScheme = '') {
		$this->_defaultScheme = $defaultScheme;
	}
	public function process() {
		if ((string)$this->value !== '') {
			if ($this->_defaultScheme) {
				$scheme = parse_url($this->value, PHP_URL_SCHEME);
				if (!$scheme) {
					$this->value = $this->_defaultScheme . '://' . $this->value;
				}
			}
			// Check with filter
			$result = filter_var($this->value, FILTER_VALIDATE_URL);
			if ($result === false) {
				$this->error = ucfirst("{$this->label} does not appear to be a valid URL.");
				return;
			}
			// Check with regular expression
			// Code modified from http://phpcentral.com/208-url-validation-in-php.html

			// SCHEME
			$urlregex = "^(https?|ftp)\:\/\/";

			// USER AND PASS (optional)
			$urlregex .= "([a-z0-9+!*(),;?&=\$_.-]+(\:[a-z0-9+!*(),;?&=\$_.-]+)?@)?";

			// HOSTNAME OR IP
			//$urlregex .= "[a-z0-9+\$_-]+(\.[a-z0-9+\$_-]+)*"; // http://x = allowed (ex. http://localhost, http://routerlogin)
			$urlregex .= "[a-z0-9+\$_-]+(\.[a-z0-9+\$_-]+)+"; // http://x.x = minimum
			//$urlregex .= "([a-z0-9+\$_-]+\.)*[a-z0-9+\$_-]{2,3}"; // http://x.xx(x) = minimum
			//use only one of the above

			// PORT (optional)
			$urlregex .= "(\:[0-9]{2,5})?";
			// PATH (optional)
			$urlregex .= "(\/([a-z0-9+\$_-]\.?)+)*\/?";
			// GET Query (optional)
			$urlregex .= "(\?[a-z+&\$_.-][a-z0-9;:@/&%=+\$_\.\-]*)?";
			// ANCHOR (optional)
			$urlregex .= "(#[!a-z_\.\-!][a-z0-9+\$_\.\-\/]*)?\$^";

			// check
			if (!preg_match($urlregex, $this->value)) {
				$this->error = ucfirst("{$this->label} does not appear to be a valid URL.");
			}
		}
	}
}
