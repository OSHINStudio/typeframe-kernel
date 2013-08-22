<?php
class Typeframe_User {

	public function __construct() {
		if ($this->loggedIn()) {
			$user = Model_User::Get($_SESSION['typef_user']['userid']);
		} else {
			if (isset($_COOKIE['typef_userid']) && isset($_COOKIE['typef_passhash'])) {
				$user = Model_User::Get($_COOKIE['typef_userid']);
				if ($user->exists()) {
					if ($user['passhash'] == $_COOKIE['typef_passhash']) {
						//if ($row = $rs->fetch_array()) {
							// Log in user and update cookie
							$row = $user->getArray(false);
							unset($row['salt']);
							unset($row['hashtype']);
							$_SESSION['typef_user'] = $row;
							setcookie('typef_username', $row['username'], time() + (60 * 60 * 24 * 30), '/');
							setcookie('typef_passhash', $row['passhash'], time() + (60 * 60 * 24 * 30), '/');
							Typeframe::Log("{$row['username']} logged in via cookie");
						//}
					}
				}
			}
		}
		if ($this->loggedIn()) {
			$user['lastrequest'] = Typeframe::Now();
			$user->save();
		}
	}

	/**
	 * Boolean indicating whether the current user is logged in.
	 * @return bool
	 */
	public function loggedIn() {
		// Return true or false
		return (isset($_SESSION['typef_user']));
	}

	/**
	 * Log in the current user with the provided credentials.
	 * @param string $usernameOrEmail User name or email of account
	 * @param string $password
	 * @param bool $cookie Use a cookie to store the login
	 * @param string $use The field being used to identify the user (username, email, or either)
	 * @return bool False if login failed
	 */
	public function login($usernameOrEmail, $password, $cookie = false, $use = 'either') {
		switch ($use) {
			case 'username':
				$field = 'username';
				break;
			case 'email':
				$field = 'email';
				break;
			default:
				$field = 'username';
				if (preg_match('/^[A-Z0-9._%+-]+@[A-Z0-9.-]+\.[A-Z]{2,4}$/i', $usernameOrEmail)) {
					$field = 'email';
				}
				break;
		}
		$users = new Model_User();
		$users->where("{$field} = ?", $userNameOrEmail);
		if ($users->count() == 0) {
			Typeframe::Log("WARNING: {$usernameOrEmail} matches more than one {$field} in the user table.");
			//return false;
		}
		
		$row = $users->getFirst();

		// Did this even find a record?
		if(!$row){
			Typeframe::Log("Login failed for {$usernameOrEmail} due to: no $field found");
			return false;
		}

		// Does the password not match?
		if (!self::CheckPassword($row, $password)) {
			Typeframe::Log("Login failed for {$usernameOrEmail} due to: incorrect password");
			return false;
		}

		//check to see if account is suspended.
		if($row['confirmed']==0){
		   Typeframe::Log("Login failed for {$usernameOrEmail} due to: suspended account");
		   return false;
		}

		// Whee, all the error checks must have passed!
		unset($row['salt']);
		unset($row['hashtype']);
		$_SESSION['typef_user'] = $row;
		if ($cookie) {
			// Store cookie
			// TODO: It might make more sense to store the user ID instead of the name.
			setcookie('typef_username', $row['username'], time() + (60 * 60 * 24 * 30), '/');
			setcookie('typef_passhash', $row['passhash'], time() + (60 * 60 * 24 * 30), '/');
		}
		Typeframe::Log("{$usernameOrEmail} logged in");
		return true;
	}

	/**
	 * Log out the current user.
	 */
	public function logout() {
		Typeframe::Log('User logged out');
		unset($_SESSION['typef_user']);
		if(defined('SESSION_DB') && SESSION_DB) Session::SetUID(0);
		setcookie('typef_username', false, time() - 3600, '/');
		setcookie('typef_passhash', false, time() - 3600, '/');
	}

	/**
	 * Get a user value by key.
	 * @param string $name
	 * @return string
	 */
	public function get($name) {
		if (isset($_SESSION['typef_user'][$name])) {
			return $_SESSION['typef_user'][$name];
		}
		// TODO: This is a quick and dirty hack to ensure that queries referencing userid receive 0 instead of null.
		if ($name == 'userid') return 0;
		return null;
	}

	/**
	 * Set a user value by key.
	 * @param string $name
	 * @param string $value
	 */
	public function set($name, $value) {
		if (is_null($value)) {
			unset($_SESSION['typef_user'][$name]);
		} else {
			$_SESSION['typef_user'][$name] = $value;
		}
	}

	/**
	 * Repopulate the SESSION data with what is in the database.
	 */
	public function refresh() {
		if (Typeframe::User()->loggedIn()) {
			$user = Model_User::Get($_SESSION['typef_user']['userid']);
			if ($user->exists()) {
				$row = $user->getArray();
				unset($row['salt']);
				unset($row['hashtype']);
				$_SESSION['typef_user'] = $row;
			}
		}
	}

	/**
	 * Get an associative array of all user values.
	 * @return assoc
	 */
	public function values() {
		if (isset($_SESSION['typef_user'])) {
			return $_SESSION['typef_user'];
		}
		return null;
	}

	/**
	 * Simple function to check the validity of a password for a user.
	 * @param int|array $userid
	 * @param string $password
	 * @return boolean
	 */
	public static function CheckPassword($userid, $password){
		// Allow $userid to be an array too.
		// This is because if the function is called from within the user object,
		// the necessary data is already poled.
		if(is_array($userid)){
			$row = $userid;
		}
		else{
			$user = Model_User::Get($userid);
			if (!$user->exists()) return false;
			$row = $user->getArray();
		}

		// Different hash types will have different logic.
		switch ($row['hashtype']) {
			case 'md5':
				if ($row['passhash'] == md5("{$password}{$row['salt']}")) {
					return true;
				}
				break;
			case 'sha1':
				if ($row['passhash'] == sha1("{$password}{$row['salt']}")) {
					return true;
				}
				break;
			default:
				return false;
		}
		return false;
	}
}
