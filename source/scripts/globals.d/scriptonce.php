<?php
if (!isset($_SESSION['scriptonce'])) {
	$_SESSION['scriptonce'] = array();
}
if (!requestIsAjax()) {
	$url = (!empty($_SERVER['HTTPS'])) ? 'https://' : 'http://';
	$url .= $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'];
	$_SESSION['scriptonce'][$url] = array();
} else {
	if ( (!empty($_SERVER['HTTP_REFERER'])) && isset($_SESSION['scriptonce'][$_SERVER['HTTP_REFERER']]) ) {
		Typeframe_Tag_Scriptonce::AlreadyLoaded($_SESSION['scriptonce'][$_SERVER['HTTP_REFERER']]);
	} else {
		trigger_error('Request appears to be Ajax but does not have a referer');
	}
}
