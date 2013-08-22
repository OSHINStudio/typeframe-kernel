<?php
/**
 * A collection of static functions for handling file uploads and manipulation.
 */
class FileManager {
	private static $_lastError = '';
	/**
	 * Move an uploaded file using move_uploaded_file() but without overwriting existing copies.
	 * If a different file with the same name exists, a copy number will be appended to the new filename, e.g., file(1).txt.
	 * @param string $src The source file
	 * @param string $dst The destination file
	 * @param bool $overwrite If false, filename will be changed instead of overwriting existing files
	 * @param string|array $extensions A comma-delimited list or an array of permitted file extensions
	 * @return string The resulting filename (including the path from $dst) or false on failure
	 */
	public static function MoveUpload($src, $dst, $overwrite = false, $extensions = '') {
		FileManager::$_lastError = '';
		if ( ($extensions) && (!self::HasExtension($dst, $extensions)) ) {
			FileManager::$_lastError = 'File has invalid extension (permitted extensions: ' . (is_array($extensions) ? join(', ', $extensions) : $extensions) . ')';
			return false;
		}
		$pathinfo = pathinfo($dst);
		$finalDst = $dst;
		$copyNum = 1;
		while ( (!$overwrite) && (file_exists($finalDst)) ) {
			//$result = exec('diff -q ' . "\"{$src}\" \"{$finalDst}\"", $array, $return);
			if (FileManager::FilesIdentical($src, $finalDst)) {
				// Existing file is identical
				return $finalDst;
			}
			$finalDst = $pathinfo['dirname'] . '/' . $pathinfo['filename'] . '_' . $copyNum . (!empty($pathinfo['extension']) ? '.' . $pathinfo['extension'] : '');
			$copyNum++;
		}
		$result = move_uploaded_file($src, $finalDst);
		if ($result) {
			return $finalDst;
		}
		FileManager::$_lastError = 'Upload failed.';
		return false;
	}

	private static function MoveOrCopyFile($action, $src, $dst, $overwrite = false, $extensions = ''){
		FileManager::$_lastError = '';
		if ( ($extensions) && (!self::HasExtension($dst, $extensions)) ) {
			FileManager::$_lastError = 'File has invalid extension (permitted extensions: ' . (is_array($extensions) ? join(', ', $extensions) : $extensions) . ')';
			return false;
		}
		$pathinfo = pathinfo($dst);
		$finalDst = $dst;
		$copyNum = 1;
		// File already exists and is the same file, don't do anything.
		if(file_exists($finalDst) && FileManager::FilesIdentical($src, $finalDst)) return $finalDst;

		while (!$overwrite && file_exists($finalDst)) {
			if (FileManager::FilesIdentical($src, $finalDst)) {
				// Existing file is identical
				return $finalDst;
			}
			$finalDst = $pathinfo['dirname'] . '/' . $pathinfo['filename'] . '_' . $copyNum . (!empty($pathinfo['extension']) ? '.' . $pathinfo['extension'] : '');
			$copyNum++;
		}

		switch ($action)
		{
			case 'move':
				$result = rename($src, $finalDst);
			break;
			case 'copy':
				$result = copy($src, $finalDst);
			break;
			default:
				$result = null;
			break;
		}

		if($result) {
			return $finalDst;
		}

		FileManager::$_lastError = 'File rename failed.';
		return false;
	}

	public static function MoveFile($src, $dst, $overwrite = false, $extensions = ''){
		return FileManager::MoveOrCopyFile('move', $src, $dst, $overwrite, $extensions);
	}

	public static function CopyFile($src, $dst, $overwrite = false, $extensions = ''){
		return FileManager::MoveOrCopyFile('copy', $src, $dst, $overwrite, $extensions);
	}

	/**
	 * Simple function that runs the `diff` application to compare two files.
	 *
	 * Please ensure that the file names provided are fully resolved and absolute!
	 *
	 * @param string $filea
	 * @param string $fileb
	 */
	public static function FilesIdentical($filea, $fileb){
		$result = exec('diff -q ' . "\"{$filea}\" \"{$fileb}\"", $array, $return);
		return ($return == 0);
	}
	public static function FormatFilesize($filesize, $round = 1){
		if(!is_numeric($filesize)){
			// Maybe it's formatted something like: 8M or 128G
			if(preg_match('/[0-9]*G/i', $filesize)) $filesize = $filesize * (1024 * 1024 * 1024);
			elseif(preg_match('/[0-9]*M/i', $filesize)) $filesize = $filesize * (1024 * 1024);
			elseif(preg_match('/[0-9]*k/i', $filesize)) $filesize = $filesize * (1024);
		}

		if(is_numeric($filesize)){
			$decr = 1024; $step = 0;
			$prefix = array(' Bytes',' KB',' MB',' GB',' TB',' PB');

			while(($filesize / $decr) > 0.9){
				$filesize = $filesize / $decr;
				$step++;
			}

			return round($filesize, $round) . $prefix[$step];
		} else {
			return 'NaN';
		}
	}
	/**
	 * Get the error message from the last FileManager operation, if any.
	 * @return string The error message, or blank if an error did not occur.
	 */
	public static function Error() {
		return FileManager::$_lastError;
	}
	/**
	 * Determine whether a user uploaded a new file or requested a reference
	 * to one that already exists. This function is designed for interoperability
	 * with the Typeframe imageupload and fileupload tags.
	 * @param string $field The key to check in the $_FILES and $_POST arrays.
	 * @param string $directory The directory where the file should be saved.
	 * @param string|array $extensions Permitted filename extensions (blank for no restrictions).
	 * @return string The complete path of the saved file (or an empty string).
	 */
	public static function GetPostedOrUploadedFile($field, $directory, $extensions = '') {
		self::$_lastError = '';
		// If *_fileuploadoption is not set, check first for an uploaded file,
		// and then for a posted file name
		if (!isset($_POST["{$field}_fileuploadoption"])) {
			if (!empty($_FILES[$field]['name'])) {
				$path = self::MoveUpload($_FILES[$field]['tmp_name'], $directory . '/' . $name, false, $extensions);
				return $path;
			}
			if (!empty($_POST[$field])) {
				if (file_exists($directory . '/' . $_POST[$field])) {
					if ($extensions && !self::HasExtension($_POST[$field], $extensions)) {
						return '';
					}
					return $directory . '/' . $_POST[$field];
				}
			}
		}
		// Proceed using *_fileuploadoption. This is the safer method because it
		// can intelligently resolve conflicts between $_POST and $_FILES values.
		if ( (!empty($_FILES[$field]['name'])) && (!empty($_POST[$field . '_fileuploadoption'])) && ($_POST[$field . '_fileuploadoption'] == '_upload')) {
			$name = $_FILES[$field]['name'];
			if ($name == '_upload') $name = 'upload';
			$path = self::MoveUpload($_FILES[$field]['tmp_name'], $directory . '/' . $name, false, $extensions);
			return $path;
		} else if (!empty($_POST[$field . '_fileuploadoption']) && $_POST[$field . '_fileuploadoption'] != '_upload') {
			if ( (!file_exists($directory . '/' . $_POST[$field . '_fileuploadoption'])) || (!is_file($directory . '/' . $_POST[$field . '_fileuploadoption'])) ) {
				self::$_lastError = "File '{$_POST[$field . '_fileuploadoption']}' does not exist on the server.";
				return '';
			}
			if ( ($extensions) && (!self::HasExtension($_POST[$field . '_fileuploadoption'], $extensions)) ) {
				self::$_lastError = 'File has invalid extension (permitted extensions: ' . (is_array($extensions) ? join(', ', $extensions) : $extensions) . ')';
				return '';
			}
			return $directory . '/' . $_POST[$field . '_fileuploadoption'];
		}
		return '';
	}
	/**
	 * Validate the filename against a list of permitted extensions.
	 * @param string $filename The filename.
	 * @param string|array $extensions Permitted extensions.
	 * @param boolean $caseSensitive If true, the extension test is case-sensitive.
	 * @return boolean
	 */
	public static function HasExtension($filename, $extensions, $caseSensitive = false) {
		if (!$extensions) return true;
		$parts = pathinfo($filename);
		if (!is_array($extensions)) {
			$extensions = preg_split('/[\s,]+/', trim($extensions));
		}
		if (!$caseSensitive) {
			$extensions = array_map('strtolower', $extensions);
			$parts['extension'] = strtolower($parts['extension']);
		}
		if (array_search($parts['extension'], $extensions) !== false) {
			return true;
		}
		return false;
	}
}
