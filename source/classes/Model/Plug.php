<?php
class Model_Plug extends BaseModel_Plug {
	public function __construct() {
		parent::__construct();
		$this->subquery('locs', 'Model_PlugLoc', 'plug.plugid = locs.plugid');
		$this->attach(Dbi_Model::EVENT_AFTERSELECT, new ModelEvent_JsonDecode('settings'));
	}
}
