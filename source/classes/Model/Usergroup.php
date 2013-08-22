<?php
class Model_Usergroup extends BaseModel_Usergroup {
	public function __construct() {
		parent::__construct();
		$this->order('usergroupname');
	}
}
