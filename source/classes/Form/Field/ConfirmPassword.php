<?php
class Form_Field_ConfirmPassword extends Form_Field {
	protected function process($confirmationField = 'password2') {
		if ($this->value != @$this->form[$confirmationField]) {
			$this->error = 'Passwords do not match.';
		}
	}
}
