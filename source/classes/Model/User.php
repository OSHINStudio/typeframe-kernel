<?php
class Model_User extends BaseModel_User {
	public function __construct() {
		parent::__construct();
		$this->innerJoin('usergroup', 'Model_Usergroup', 'usergroupid = usergroup.usergroupid');
		// Update the password hash data when a password has been set.
		$this->attach(Dbi_Model::EVENT_BEFORESAVE, new ModelEvent_HashPassword());
		/*$this->attach(Dbi_Model::EVENT_BEFORESAVE, new ModelEvent_Callback(function($record) {
			if ($record['password']) {
				$salt = Bam_Functions::RandomId();
				$type = 'sha1';
				$hash = sha1("{$record['password']}{$salt}");
				$record['passhash'] = $hash;
				$record['salt'] = $salt;
				$record['hashtype'] = $type;
			}
		}));*/
	}
}
