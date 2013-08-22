<?php
$referrer = @$_SERVER['HTTP_REFERER'];
if ($referrer) {
	$parsed = parse_url($referrer);
	if (strpos($parsed['host'], $_SERVER['HTTP_HOST']) === false) {
		$_SESSION['external_referrer'] = $referrer;
	}
}
