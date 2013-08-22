<?php
$dir = scandir(TYPEF_SOURCE_DIR . '/scripts/globals.d');
foreach ($dir as $file) {
	if (substr($file, 0, 1) !== '.') {
		include(TYPEF_SOURCE_DIR . '/scripts/globals.d/' . $file);
	}
}
