<?php
$start = microtime(true);
//session_start();
date_default_timezone_set('America/New_York');

if ( (!file_exists('typeframe.config.php')) && (file_exists('install')) ) {
	header('Location: install/');
	exit;
}

require_once('typeframe.config.php');
require_once(TYPEF_SOURCE_DIR . '/import.php');

Typeframe::CurrentPage()->execute();
$end = microtime(true);
//echo "\n<!-- Total execution time: " . ($end - $start) . '-->';
