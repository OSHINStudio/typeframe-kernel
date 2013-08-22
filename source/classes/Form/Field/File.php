<?php
class Form_Field_File extends Form_Field {
	private $_name;
	private $_directory;
	private $_extensions;
	/**
	 * @param string $name The name of the form field that contains the uploaded file.
	 * @param type $directory The directory where the file will be saved.
	 * @param type $extensions Valid file extensions.
	 */
	public function __construct($name, $directory, $extensions = '') {
		$this->_name = $name;
		$this->_directory = $directory;
		$this->_extensions = $extensions;
	}
	public function process() {
		if (isset($_SERVER['REQUEST_METHOD']) && $_SERVER['REQUEST_METHOD'] == 'POST') {
			$result = FileManager::GetPostedOrUploadedFile($this->_name, $this->_directory, $this->_extensions);
			if (!FileManager::Error()) {
				$this->value = basename($result);
			} else {
				$this->error = 'Unable to upload ' . $this->label . ': ' . FileManager::Error();
			}
		} else {
			// TODO: Should there be a validation method for uploads that don't come from a POST?
		}
	}
}
