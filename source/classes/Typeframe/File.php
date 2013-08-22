<?php
// TODO: This class should be able to detect FTP credentials in the session
// environment; e.g., if the config does not provide the user name and password,
// the user could have provided credentials that are being stored in $_SESSION.

// TODO: Sanitize destination paths, e.g., disallow "../../" or any other
// syntax that could grant access to paths outside of the Typeframe root.

/**
 * A filesystem interface for managing files, directories, and permissions.
 * It will either execute commands directly on the filesystem if the current
 * user has write permissions (e.g., if the process is being executed from a
 * CLI) or use the FTP credentials from typeframe.config.php. The paths to
 * Typeframe files are always relative to the Typeframe root (TYPEF_DIR).
 */
class Typeframe_File {
	private $_ftp = null;
	public function __construct() {
		// This class assumes that the user has adequate permissions if the
		// Typeframe root directory is writeable.
		if (!is_writeable(TYPEF_DIR)) {
			$this->_ftp = new Ftp();
			if (!$this->_ftp->connect(TYPEF_FTP_HOST)) {
				throw new Exception("Unable to connect to FTP host");
			}
			if (!$this->_ftp->login(TYPEF_FTP_USER, TYPEF_FTP_PASS)) {
				throw new Exception("Unable to log in to FTP account");
			}
		}
	}
	public function __destruct() {
		if ($this->_ftp) {
			$this->_ftp->close();
		}
	}
	public function close() {
		// TODO: No op?
	}
	/**
	 * Copy a file. The destination is relative to the Typeframe root directory (TYPEF_DIR).
	 * @param string $src The source file.
	 * @param string $dst The destination file (relative to TYPEF_DIR).
	 */
	public function copy($src, $dst) {
		if ($this->_ftp) {
			$this->_ftp->put(TYPEF_FTP_ROOT . '/' . $dst, $src);
		} else {
			copy($src, TYPEF_DIR . '/' . $dst);
		}
	}
	/**
	 * Create a directory. The path is relative to the Typeframe root (TYPEF_DIR).
	 * @param string $dir The directory name (relative to TYPEF_DIR).
	 */
	public function mkdir($dir) {
		if ($this->_ftp) {
			$this->_ftp->mkdir(TYPEF_FTP_ROOT . '/' . $dir);
		} else {
			mkdir(TYPEF_DIR . '/' . $dir);
		}
	}
	/**
	 * Set permissions on a file. The file's path is relative to the Typeframe
	 * root directory (TYPEF_DIR).
	 * @param int $mode The permissions given as an octal value.
	 * @param string $file The file to change (relative to TYPEF_DIR).
	 */
	public function chmod($mode, $file) {
		if ($this->_ftp) {
			@$this->_ftp->chmod($mode, TYPEF_FTP_ROOT . '/' . $file);
		} else {
			@chmod(TYPEF_DIR . '/'. $file, $mode);
		}
	}
}
