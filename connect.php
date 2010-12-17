<?php

if($db = mysql_connect($conf['db_hostname'], $conf['db_username'], $conf['db_password'])) {
	mysql_select_db($conf['db_name'], $db);
} else {
	die("Ajax Connection Failed!");
}

/*
if($db = mysql_connect('localhost', 'speed_engine', 'y0urm0m')) {
	mysql_select_db('speed_engine', $db);
} else {
	die("Ajax Connection Failed!");
}
*/
?>
