<?php
/**
 * Validate email address fields in forms.
 */
class Form_Field_Email extends Form_Field {
	public function process() {
		if ((string)$this->value !== '') {
			if (!preg_match('/^[A-Z0-9._%+-]+@[A-Z0-9.-]+\.[A-Z]{2,4}$/i', $this->value)) {
				$this->error = ucfirst($this->label . ' does not appear to be a valid email address.');
			}
		}
	}
}
