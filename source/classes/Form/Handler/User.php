<?php
class Form_Handler_User extends Form_Handler {
	public function __construct($withPassword = true) {
		$this->addField('email', true, 'Email', new Form_Field_Email());
		if ($withPassword) {
			$this->addField('password', true, 'Password', new Form_Field_ConfirmPassword('password2'));
		}
	}
}
