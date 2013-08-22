<?php

date_default_timezone_set('America/New_York');
$mtime = microtime(true);
define('TYPEF_START_TIME', $mtime);

require_once(TYPEF_SOURCE_DIR . '/autoload.php');
require_once(TYPEF_SOURCE_DIR . '/libraries/functions.php');

Pagemill_Doctype::SetTemplateDoctypeClass('Typeframe_Doctype');

// TODO: This constant is a candidate for deprecation. It's a legacy from the
// kernel-enterprise package.
if(!defined('TYPEF_ROOT_WEB_DIR')) define('TYPEF_ROOT_WEB_DIR', TYPEF_WEB_DIR);

// TODO: Set up a way to make the framework database-independent.
$source = new Dbi_Source_MySql(TYPEF_DB_HOST, TYPEF_DB_USER, TYPEF_DB_PASS, TYPEF_DB_NAME);
Dbi_Source::SetGlobalSource($source);

// TODO: Find logical places to register the class handlers and expressions.
function useModel($model) {
	return $model->select();
}
function useFormHandler($form) {
	return array('fields' => $form->fields(), 'errors' => $form->errors());
}
function useFormField($field) {
	return $field->data();
}
Pagemill_Data::ClassHandler('Dbi_Model', 'useModel');
Pagemill_Data::ClassHandler('Form_Handler', 'useFormHandler');
Pagemill_Data::ClassHandler('Form_Field', 'useFormField');
Pagemill_Data::RegisterExprFunc('default_date', 'Typeframe_ExprFunc::default_date');
Pagemill_Data::RegisterExprFunc('default_date_time', 'Typeframe_ExprFunc::default_date_time');
Pagemill_Data::RegisterExprFunc('default_date_time_w_seconds', 'Typeframe_ExprFunc::default_date_time_w_seconds');
Pagemill_Data::RegisterExprFunc('skin_path', 'Typeframe_Skin::SkinPath');
Pagemill_Data::RegisterExprFunc('allow', 'Typeframe::Allow');
Pagemill_Data::RegisterExprFunc('shorten', 'Bam_Functions::GetIntro');
Pagemill_Data::RegisterExprFunc('count_plugins_for', 'Typeframe_Tag_Socket::CountPluginsFor');
Pagemill_Data::RegisterExprFunc('resize_image', 'Typeframe_ExprFunc::resize_image');

// TODO: Experimenting with session management in Typeframe_Response->execute().
if (isset($_SERVER['HTTP_HOST'])) {
	if (TYPEF_WEB_DIR != '') session_set_cookie_params(ini_get('session.cookie_lifetime'), TYPEF_WEB_DIR);
	session_start();
}

$dir = scandir(TYPEF_SOURCE_DIR . '/scripts/define.d');
foreach ($dir as $file) {
	if (substr($file, 0, 1) != '.' && is_file(TYPEF_SOURCE_DIR . '/scripts/define.d/' . $file)) {
		require_once(TYPEF_SOURCE_DIR . '/scripts/define.d/' . $file);
	}
}
