<?php
$db = Typeframe::Database();
$pm = Typeframe::Pagemill();

$width = 160;
$height = 60;
//$characters = 5;
$characters = rand(4,5);

// Generate the captcha code
$poss = '23456789bcdefghjkmnpqrstvwxyz'; // Limit code to unambiguous characters
$code = '';
for ($i = 0; $i < $characters; $i++) {
	$code .= substr($poss, rand(0, strlen($poss) - 1), 1);
}

$image = imagecreate($width, $height) or die('Image creation failed.');
$background_color = imagecolorallocate($image, 255, 255, 255);
$text_color = imagecolorallocate($image, 20, 40, 100);
//$noise_color = imagecolorallocate($image, 100, 120, 180);
$noise_color = imagecolorallocate($image, 20, 40, 100);

// Add random lines to image
for ($i=0; $i < ($width*$height) / 1000; $i++) {
	imageline($image, rand(0, $width), rand(0, $height), rand(0, $width), rand(0, $height), $noise_color);
}

// Load font and write security code to image
$font = imageloadfont(TYPEF_SOURCE_DIR . '/fonts/anonymous.gdf');
$fontwidth = imagefontwidth($font);
$left = floor($fontwidth / 2) * -1;
for ($i = 0; $i < strlen($code); $i++) {
	$char = substr($code, $i, 1);
	$left += $fontwidth + rand(0, 8);
	imagestring($image, $font, $left, 2 + rand(0, 12), $char, $text_color);
}

// Add random dots to image
for( $i=0; $i<($width*$height)/5; $i++ ) {
	imagefilledellipse($image, mt_rand(0,$width), mt_rand(0,$height), 1, 1, $noise_color);
}

// Output image
header('Content-Type: image/jpeg');
imagejpeg($image);
imagedestroy($image);

$_SESSION['captcha'] = $code;

exit;
?>