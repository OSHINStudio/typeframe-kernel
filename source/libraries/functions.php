<?php
function randomID($len = 32) {
	// Generate a random alphanumeric string
	$seed = "abcdefghijklmnopqrtsuvwxyz0123456789";
	$result = "";
	for ($t = 0; $t < $len; $t++) {
		$x = rand(0, 35);
		$result .= substr($seed, $x, 1);
	}
	return $result;
}

function uniqueFilename($filename, $path = '') {
	if ($path != '') {
		if ( (substr($path, strlen($path) - 1, 1) != "/") && (substr($filename, 0, 1) != "/") ) {
			$path = $path . "/";
		}
	}
	$num = 0;
	$uniquename = $filename;
	$dot = strrpos($uniquename, ".");
	if ($dot === false) {
		$base = $uniquename;
		$ext = "";
	} else {
		$base = substr($uniquename, 0, $dot);
		$ext = substr($uniquename, $dot);
	}
	while (file_exists("{$path}{$uniquename}")) {
		$num++;
		$uniquename = "{$base}({$num}){$ext}";
	}
	return $uniquename;
}

function makeFriendlyUrlText($string, $query = null) {
	// DO NOT pass an entire URL into this function.  It'll just get mangled.
	// Pass text that needs to be made friendly for insertion into a path, such as the
	// title of an article.
	$result = strtolower($string);
	$result = str_replace("'", '', $result);
	$result = preg_replace('/[^a-z0-9]+/', '-', $result);
	$result = preg_replace('/[\-]+/', '-', $result);
	// Maximum length for each unit (assumed here to be used as a single directory name)
	// is 60 characters, plus a numeric ID in the case of duplicated titles
	if (strlen($result) > 60) {
		$result = substr($result, 0, 100);
		if (substr($result, 59) == '-') {
			$result = substr($result, 0, 99);
		}
	}
	if ($result{0} == '-') $result = substr($result, 1);
	if (substr($result, strlen($result) - 1) == '-') $result = substr($result, 0, strlen($result) - 1);
	if (!is_null($query)) {
		// Make sure the text does not already exist in the specified query
		// The query should include a parameter for the text (e.g.: SELECT * FROM table WHERE url = ?)
		$rs = Typeframe::Database()->prepare($query);
		$checked = $result;
		$num = 0;
		$rs->execute($checked);
		while ($rs->recordcount() > 0) {
			$num++;
			$checked = $result . '-' . $num;
			$rs->execute($checked);
		}
		$result = $checked;
	}
	return $result;
}

function requestIsAjax() {
	return (isset($_SERVER['HTTP_X_REQUESTED_WITH']) && $_SERVER['HTTP_X_REQUESTED_WITH'] == 'XMLHttpRequest');
}

function simplexml_merge (SimpleXMLElement &$xml1, SimpleXMLElement $xml2) {
	$dom1 = new DomDocument();
	$dom2 = new DomDocument();
	$dom1->loadXML($xml1->asXML());
	$dom2->loadXML($xml2->asXML());

	$xpath = new domXPath($dom2);
	$xpathQuery = $xpath->query('/*/*');
	for ($i = 0; $i < $xpathQuery->length; $i++) {
		$dom1->documentElement->appendChild(
		$dom1->importNode($xpathQuery->item($i), true));
	}
	$xml1 = simplexml_import_dom($dom1);
}

function skin_path($path) {
	$path = (string) $path;
	$uri = Typeframe::CurrentPage()->uri();
	if (substr($path, 0, 1) != '/') $path = '/' . $path;
	if (file_exists(TYPEF_DIR . '/skins/' . Typeframe_Skin::Current() . $path)) {
		return TYPEF_ROOT_WEB_DIR . '/skins/' . Typeframe_Skin::Current() . $path;
	/*} else if (file_exists(TYPEF_DIR . '/skins/default' . $path)) {
		return TYPEF_ROOT_WEB_DIR . '/skins/default' . $path;
	} else if (file_exists(TYPEF_DIR . '/files/static' . $path)) {
		return TYPEF_ROOT_WEB_DIR . '/files/static' . $path;
	} else {
		// TODO: This might not be the best default.  Should it trigger an error?
		return TYPEF_ROOT_WEB_DIR . $path;
	}*/
	} else if ( (Typeframe_Skin::Current() != TYPEF_SITE_SKIN) && ($uri != TYPEF_WEB_DIR . '/admin') && (substr($uri, 0, strlen(TYPEF_WEB_DIR . '/admin/')) != TYPEF_WEB_DIR . '/admin/') ) {
		if (file_exists(TYPEF_DIR . '/skins/' . TYPEF_SITE_SKIN . $path)) {
			return TYPEF_ROOT_WEB_DIR . '/skins/' . TYPEF_SITE_SKIN . $path;
		}
	} else {
		return TYPEF_ROOT_WEB_DIR . '/skins/default' . $path;
	}
}

Pagemill_Data::RegisterExprFunc('skin_path', 'skin_path');
//$skin = Pagemill_Tag_Html::Skin();
//Typeframe::SetTemplatePath(TYPEF_DIR . '/skins/' . $skin . '/templates;' . ($skin != 'default' ? TYPEF_DIR . '/skins/default/templates' . ';' : '') . TYPEF_SOURCE_DIR . '/templates');

if (!function_exists('http_response_code')) {
	function http_response_code($code = NULL) {
		static $status = 200;
		if ($code !== NULL) {
			switch ($code) {
				case 100: $text = 'Continue'; break;
				case 101: $text = 'Switching Protocols'; break;
				case 200: $text = 'OK'; break;
				case 201: $text = 'Created'; break;
				case 202: $text = 'Accepted'; break;
				case 203: $text = 'Non-Authoritative Information'; break;
				case 204: $text = 'No Content'; break;
				case 205: $text = 'Reset Content'; break;
				case 206: $text = 'Partial Content'; break;
				case 300: $text = 'Multiple Choices'; break;
				case 301: $text = 'Moved Permanently'; break;
				case 302: $text = 'Moved Temporarily'; break;
				case 303: $text = 'See Other'; break;
				case 304: $text = 'Not Modified'; break;
				case 305: $text = 'Use Proxy'; break;
				case 400: $text = 'Bad Request'; break;
				case 401: $text = 'Unauthorized'; break;
				case 402: $text = 'Payment Required'; break;
				case 403: $text = 'Forbidden'; break;
				case 404: $text = 'Not Found'; break;
				case 405: $text = 'Method Not Allowed'; break;
				case 406: $text = 'Not Acceptable'; break;
				case 407: $text = 'Proxy Authentication Required'; break;
				case 408: $text = 'Request Time-out'; break;
				case 409: $text = 'Conflict'; break;
				case 410: $text = 'Gone'; break;
				case 411: $text = 'Length Required'; break;
				case 412: $text = 'Precondition Failed'; break;
				case 413: $text = 'Request Entity Too Large'; break;
				case 414: $text = 'Request-URI Too Large'; break;
				case 415: $text = 'Unsupported Media Type'; break;
				case 500: $text = 'Internal Server Error'; break;
				case 501: $text = 'Not Implemented'; break;
				case 502: $text = 'Bad Gateway'; break;
				case 503: $text = 'Service Unavailable'; break;
				case 504: $text = 'Gateway Time-out'; break;
				case 505: $text = 'HTTP Version not supported'; break;
				default:
					throw new Exception('Unknown http status code "' . htmlentities($code) . '"');
					break;
			}
			$protocol = (isset($_SERVER['SERVER_PROTOCOL']) ? $_SERVER['SERVER_PROTOCOL'] : 'HTTP/1.0');
			header($protocol . ' ' . $code . ' ' . $text);
			$status = $code;
		}
		return $status;
	}
}