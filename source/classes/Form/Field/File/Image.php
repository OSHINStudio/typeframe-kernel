<?php

class Form_Field_File_Image extends Form_Field_File {
	/**
	 * @param string $name The name of the form field that contains the uploaded image.
	 * @param type $directory The directory where the image will be saved.
	 */
	public function __construct($name, $directory) {
		parent::__construct($name, $directory, 'jpg,jpeg,gif,png');
	}
}
