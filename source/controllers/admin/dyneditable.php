<?php 
// This is a helper file for the dyneditables javascript system.

/**
 * Simple function to throw a JSON-friendly error back to the browser.
 * 
 * @param string $text
 */
function returnerror($text, $code = 0){
	die(json_encode(array('status' => $code, 'error' => $text)));
}


// There need to be a "few" key variables set from the POST data...
if(!isset($_POST['basedir'])) returnerror('Please set the "basedir" for the upload.');
if(!isset($_FILES['___uploadfile'])) returnerror('No file uploaded.');
if($_FILES['___uploadfile']['error'] == UPLOAD_ERR_INI_SIZE) returnerror('The uploaded file exceeds the upload_max_filesize directive in php.ini.'); // 1
if($_FILES['___uploadfile']['error'] == UPLOAD_ERR_FORM_SIZE) returnerror('The uploaded file exceeds the MAX_FILE_SIZE directive that was specified in the HTML form. '); // 2
if($_FILES['___uploadfile']['error'] == UPLOAD_ERR_PARTIAL) returnerror('The uploaded file was only partially uploaded. '); // 3
if($_FILES['___uploadfile']['error'] == UPLOAD_ERR_NO_FILE) returnerror('No file was uploaded. '); // 4
if($_FILES['___uploadfile']['error'] == UPLOAD_ERR_NO_TMP_DIR) returnerror('Missing a temporary folder.'); // 6
if($_FILES['___uploadfile']['error'] == UPLOAD_ERR_CANT_WRITE) returnerror('Failed to write file to disk.'); // 7
if($_FILES['___uploadfile']['error'] == UPLOAD_ERR_EXTENSION) returnerror('A PHP extension stopped the file upload.'); // 8

// basedir needs to start with a '/'.
if($_POST['basedir']{0} != '/') $_POST['basedir'] = '/' . $_POST['basedir'];

// Guess I should do the save now that all checks have passed...
$image = @FileManager::MoveUpload($_FILES['___uploadfile']['tmp_name'], TYPEF_DIR . $_POST['basedir'] . '/' . $_FILES['___uploadfile']['name']);
if(!$image) returnerror('Unable to move uploaded file to ' . $_POST['basedir']);

// Must have went through.
die(json_encode(array('status' => 1, 'filename' => $image, 'basename' => basename($image))));