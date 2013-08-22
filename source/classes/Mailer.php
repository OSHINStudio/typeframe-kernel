<?php
require_once(TYPEF_SOURCE_DIR . '/libraries/phpmailer/class.phpmailer.php');

/**
 * A subclass of PHPMailer that loads the site's default SMTP configuration.
 */
class Mailer extends PHPMailer {
	/**
	 * Load the default SMTP configuration.  Return void.
	 */
	public function Configure() {
		if (TYPEF_MAILER_SENDER == '') {
			if ($_SERVER['SERVER_ADMIN']) {
				$this->From = $_SERVER['SERVER_ADMIN'];
				$this->FromName = $_SERVER['SERVER_ADMIN'];
			} else {
				$host = $_SERVER['HTTP_HOST'];
				if (substr($host, 0, 4) == "www.") {
					$host = substr($host, 4);
				}
				$this->From = "webmaster@{$host}";
				$this->FromName = "webmaster@{$host}";
			}
		} else {
			$this->From = TYPEF_MAILER_SENDER;
			if (TYPEF_MAILER_NAME) {
				$this->FromName = TYPEF_MAILER_NAME;
			} else {
				$this->FromName = TYPEF_MAILER_SENDER;
			}
		}
		if (TYPEF_MAILER_METHOD == 'SMTP') {
			$this->Mailer = 'smtp';
			$this->Host = TYPEF_MAILER_HOST;
			if (TYPEF_MAILER_AUTH == 1) {
				$this->SMTPAuth = true;
				$this->Username = TYPEF_MAILER_USERNAME;
				$this->Password = TYPEF_MAILER_PASSWORD;
				if (TYPEF_MAILER_SECURE) {
					$this->SMTPSecure = TYPEF_MAILER_SECURE;
				}
			}
		} else {
			$this->Mailer = 'mail';
		}
	}
}
