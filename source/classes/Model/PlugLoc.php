<?php
class Model_PlugLoc extends BaseModel_PlugLoc
{
	public function __construct() {
		parent::__construct();
		$this->innerJoin('plugin', 'Model_Plug', 'plugin.plugid = plugid');
	}
	public static function Append($plugid, $skin, $socket, $rules)
	{
		$plugloc = Model_PlugLoc::Create();
		$plugloc['plugid'] = $plugid;
		$plugloc['skin'] = $skin;
		$plugloc['socket'] = $socket;
		$plugloc['rules'] = $rules;
		$plugloc->save();
		return $plugloc['id'];
	}
}
