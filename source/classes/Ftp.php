<?php
class Ftp {
	private $_host;
	private $_port;
	private $_user;
	private $_pass;
	private $_ftpStream;
	public function __construct() {
		$this->_host = "";
		$this->_port = "";
		$this->_user = "";
		$this->_pass = "";
		$this->_ftpStream = NULL;
	}
	public function connect($host, $port = 21) {
		if (!($this->_ftpStream = ftp_connect($host, $port))) return false;
		$this->_host = $host;
		$this->_port = $port;
		return true;
	}
	public function close() {
		if ($this->_ftpStream) {
			ftp_close($this->_ftpStream);
			$this->_ftpStream = null;
			$this->_user = '';
			$this->_pass = '';
		}
	}
	public function login($user, $pass) {
		if ($this->_ftpStream) {
			if (@ftp_login($this->_ftpStream, $user, $pass)) {
				$this->_user = $user;
				$this->_pass = $pass;
				return true;
			} else {
				return false;
			}
		} else {
			die("ERROR in phrameFTP: Attempted to log in without connection.");
		}
	}
	public function mkdir($dir) {
		if ($this->_ftpStream) {
			if (@ftp_mkdir($this->_ftpStream, $dir)) return true;
		}
		return false;
	}
	public function chmod($mode, $file) {
		if ($this->_ftpStream) {
			if (function_exists('ftp_chmod')) {
				return ftp_chmod($this->_ftpStream, $mode, $file);
			} else {
				//echo "<h1>$file</h1>";
				return ftp_site($this->_ftpStream, "chmod $mode $file");
				//return ftp_exec($this->ftpStream, "chmod $mode $file");
			}
		}
		return false;
	}
	public function put($remote, $local, $mode = FTP_BINARY) {
		if ($this->_ftpStream) {
			return ftp_put($this->_ftpStream, $remote, $local, $mode);
		}
		return false;
	}
	public function fput($remote, $handle, $mode = FTP_BINARY) {
		if ($this->_ftpStream) {
			if(!ftp_fput($this->_ftpStream, $remote, $handle, $mode)) {
				echo "Error in fput writing to $remote";
				return false;
			}
			return true;
		}
		return false;
	}
	public function delete($filename) {
		if ($this->_ftpStream) {
			return ftp_delete($this->_ftpStream, $filename);
		}
		return false;
	}
	public function pwd() {
		return ftp_pwd($this->_ftpStream);
	}
	public function nlist($dir) {
		return ftp_nlist($this->_ftpStream, $dir);
	}
	public function rawlist($dir) {
		return ftp_rawlist($this->_ftpStream, $dir);
	}
}
