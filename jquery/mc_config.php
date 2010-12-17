<?php
include($_SERVER['DOCUMENT_ROOT'] . "/system/config.php");

// START EDITABLE VARIABLES
$db_host = $conf['db_hostname'];
$db_database = $conf['db_name'];
$db_username = $conf['db_username'];
$db_password = $conf['db_password'];

$easy_gallery_path = $_SERVER["DOCUMENT_ROOT"].'/images/mc_gallery/';
$easy_gallery_url = '/images/mc_gallery/';
//$asset_server = '';
// END EDITABLE VARIABLES

// Session Id
$php_session_id = isset($_POST["S"]) ? $_POST["S"] : false;
session_id($php_session_id);
session_start();


?>
