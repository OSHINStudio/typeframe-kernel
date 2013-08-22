<?php
$globals = array('get' => $_GET, 'post' => $_POST, 'session' => $_SESSION, 'cookie' => $_COOKIE, 'request' => $_REQUEST, 'request_is_ajax' => requestIsAjax());
$globals['http_referer'] = isset($_SERVER['HTTP_REFERER']) ? $_SERVER['HTTP_REFERER'] : '';
$pm->setVariable('globals', $globals);

if (isset($_SESSION['typef_redirect_message'])) {
	unset($_SESSION['typef_redirect_message']);
}

// Add current URI and other general info to Pagemill
$pm->setVariable('typef_page_uri', Typeframe::CurrentPage()->uri());
$pm->setVariable('typef_pageid', Typeframe::CurrentPage()->pageid());
$pm->setVariable('typef_siteid', Typeframe::CurrentPage()->siteid());

$pm->setVariable('typef_app_uri', Typeframe::CurrentPage()->page()->uri());
$pm->setVariable('typef_app_dir', Typeframe::CurrentPage()->applicationUri());
$pm->setVariable('typef_app_base', Typeframe::CurrentPage()->applicationUri() . '/');

$pm->setVariable('typef_web_dir', TYPEF_WEB_DIR);
$pm->setVariable('typef_web_base', TYPEF_WEB_DIR . '/');
$pm->setVariable('typef_root_web_dir', TYPEF_ROOT_WEB_DIR);

$pm->setVariable('typef_title', TYPEF_TITLE);

//$pm->setVariable('typef_host', (defined('TYPEF_HOST') ? $_SERVER['HTTP_HOST'] : TYPEF_HOST));
$pm->setVariable('typef_host', Typeframe::CurrentPage()->siteid() == 0 && defined('TYPEF_HOST') ? TYPEF_HOST : $_SERVER['HTTP_HOST']);

$parts = parse_url($_SERVER['REQUEST_URI']);
$pm->setVariable('typef_request_uri', $_SERVER['REQUEST_URI']);
$pm->setVariable('typef_request_path', Typeframe::CurrentPage()->uri());

$pm->setVariable('typef_app_name', Typeframe::CurrentPage()->application()->name());
