<?php
date_default_timezone_set('America/New_York');
if ( (defined('TYPEF_ENT')) && (TYPEF_ENT) ) {
	require_once(TYPEF_SOURCE_DIR . '/kernel/typeframe-enterprise.class.php');
} else {
	define('TYPEF_ENT', '');
}

if (file_exists(TYPEF_SOURCE_DIR . '/classes/Zend')) {
	set_include_path(get_include_path() . PATH_SEPARATOR . TYPEF_SOURCE_DIR . '/classes');
}

function typeframe_autoloader($classname) {
	$path = TYPEF_SOURCE_DIR . '/classes/' . str_replace('_', '/', $classname) . '.php';
	if ( (file_exists($path)) && (is_file($path)) ) {
		require_once($path);
		return true;
	}
	// This is the legacy method of loading classes from the registry. Deprecate ASAP.
	// TODO: Some classes are hardcoded for simplicity.  See if a more robust solution is possible,
	// but keep in mind that some classes should work without requiring the registry to be loaded.
	switch ($classname) {
		case 'DBI':
			throw new Exception('Attempt to load legacy DBI class');
			//require_once(TYPEF_SOURCE_DIR . '/libraries/dbi.class.php');
			//return true;
			break;
		default:
			/*if ( (defined('TYPEF_DEBUG_LEGACY_CLASS_ERRORS')) && (TYPEF_DEBUG_LEGACY_CLASS_ERRORS) ) {
				trigger_error("Legacy access to class '{$classname}'");
			}
			if (Typeframe::RegistryLoaded()) {
				throw new Exception('Legacy load ' . $classname);
				$file = Typeframe::Registry()->getClassFile($classname);
				if ($file) {
					if (file_exists(TYPEF_SOURCE_DIR . '/libraries/' . $file)) {
						require_once(TYPEF_SOURCE_DIR . '/libraries/' . $file);
						return true;
					}
				}
			}*/
			return false;
	}
}
spl_autoload_register("typeframe_autoloader");
