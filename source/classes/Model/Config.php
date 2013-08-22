<?php
class Model_Config extends BaseModel_Config {
	/**
	 * A shortcut for creating or updating a config value.
	 * @param string $name The config name.
	 * @param string $value The config value.
	 * @return Dbi_Record The resulting record.
	*/
	public static function Set($name, $value) {
		$config = Model_Config::Get($name);
		if (!$config->exists()) {
			$config = Model_Config::Create();
			$config['configname'] = $name;
		}
		$config['configvalue'] = $value;
		$config->save();
		return $config;
	}
}
