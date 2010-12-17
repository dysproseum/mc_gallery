<?php
define('EXT', 'Yo Mama!');

include('../../../config.php');
include('../../../../includes/connect.php');

//$file_path=$webroot . '/images/easy_gallery/';

function download($location, $name)
{
	/*
	$ohnoes="../";

	if(strstr($location, $ohnoes) != FALSE)
	{
	        die();
	}

	if(FALSE === file_exists($location))
	{
			echo "Not found.";
	        die();
	}

	// Size of the file
	$file_size = filesize($location);
	*/
	
	// Download headers
	//header ("Content-Type: application/octet-stream");
	//header ("Content-Length: $file_size");
	header ("Content-Disposition: attachment; filename=$name");

	// Read the file
	$fn=fopen($location,"r");
	fpassthru($fn);
	fclose($fn);
}

// Get file path
if(isset($_GET['file']) && isset($_GET['entry_id']) && isset($_GET['size'])) 
{
	//download file
	$server = $conf['remote_asset_url'];
    $file_name=$_GET['file'];
	$entry_id = $_GET['entry_id'];
	$size = $_GET['size'];
	
	list($name, $ext) = explode('.', $file_name); 
	$file = $server.'/images/easy_gallery/'.$entry_id.'/'.$name.'_'.$size.'.'.$ext;

	download($file, $name.'_'.$size.'.'.$ext);
	
	exit;
}
else die();

?>