<?php
define('EXT', 'Yo Mama!');

require('class.cropcanvas.php');
//include_once('../../../extensions/ext.remote_asset_storage.php');
define('REL_DIR', '/images/mc_gallery/'); 

function relative_path($full_path)
{
	$pos = strpos($full_path, '/images/mc_gallery');
	$rel = substr($full_path, $pos);
	return $rel;
}

function download_image($target_url, $filepath)
{
	$contents = '';
	$fp = fopen($target_url, "r");
	if($fp)
	{
		while (!feof($fp))
		{
			$contents .= fread($fp, 4096);
		}
		fclose($fp);
	}
	else die('Could not load image from asset server');
	
	if(file_exists(dirname($filepath)) === FALSE)
		mkdir(dirname($filepath));
	
	$fp = fopen($filepath, "w");
	if($fp)
	{
		fwrite($fp, $contents);
		fclose($fp);
	}
	else die('Could not open target file for writing');
	
}


//---------------------------------------
//       Process cropped selection 
//---------------------------------------
if(isset($_GET['file'])) 
{
	$input = $_GET['file'];
	$dir = dirname($input);
	$file = basename($input, ".jpg");
	$basename = "$dir/$file";
	$cropname = $basename."_crop.jpg";
	
	$cc = new CropCanvas(); 
	//$RAS = new Remote_asset_storage();
	
	$cc->loadImage($input);
	$cc->cropToDimensions($_GET['sx'], $_GET['sy'], $_GET['ex'], $_GET['ey']);
	$cc->saveImage($cropname);
	$cc->flushImages();
	
	//$RAS->add_remote_asset($cropname, basename($cropname), relative_path($dir));	
	
	list($width, $height) = getimagesize($cropname);
	
	//Medium Size
	$newfile_med = $basename."_m.jpg";
	$new_width = 725;
	$new_height = 360;
	                                        
	$image_resized = imagecreatetruecolor($new_width, $new_height);
	$image = imagecreatefromjpeg($cropname);
	imagecopyresampled($image_resized, $image, 0, 0, 0, 0, $new_width, $new_height, $width, $height);
	if(imagejpeg($image_resized, $newfile_med))
		echo "Med JPG Created, ";
	else echo "Med JPG Failed. ";
	
	// Upload medium image
	//$RAS->add_remote_asset($newfile_med, basename($newfile_med), relative_path($dir));	
	//@unlink($newfile_med);
	
	imagedestroy($image_resized);
	imagedestroy($image);	
	
	//Small Size
	$newfile_sm = $basename."_s.jpg";
	$new_width = 92;
	$new_height = 52;
	                                        
	$image_resized = imagecreatetruecolor($new_width, $new_height);
	$image = imagecreatefromjpeg($cropname);
	imagecopyresampled($image_resized, $image, 0, 0, 0, 0, $new_width, $new_height, $width, $height);
	if(imagejpeg($image_resized, $newfile_sm))
		echo "Small JPG Created, ";
	else echo "Small JPG Failed. ";
	
	// Upload small image
	//$RAS->add_remote_asset($newfile_sm, basename($newfile_sm), relative_path($dir));	
	//@unlink($newfile_sm);
	//@unlink($cropname);
	//@unlink($input);

	echo '<a href="#" onclick="window.close()">close</a>'; 
	echo '<script type="text/javacscript">window.close();</script>';
	
	exit;

}

/*---------------------------------------------------------------------*/

require('class.cropinterface.php');
$ci =& new CropInterface(true);

?>

<html>
<body>

<?php

//-----------------------------------
//    Start image loading
//-----------------------------------

include('../../../config.php');
include('../../../includes/connect.php');

$image_dir = dirname(__FILE__) . "/../../../../images/mc_gallery";

//retrieve image data
if(isset($_GET['entry_id']) && isset($_GET['image_id']))
{
	$entry_id = $_GET['entry_id'];
	$image_id = $_GET['image_id'];
}
else die('No image passed');

//get image filename
$query = "SELECT filename FROM exp_mc_gallery_images WHERE entry_id=$entry_id AND image_id=$image_id LIMIT 1";
$result = mysql_query($query);

if(mysql_num_rows($result)  == 0) die('No image found in query');

$filename = mysql_result($result,0);

//----------------------------------------
//  Download the image from asset server
//----------------------------------------

//$target_url = $conf['remote_asset_url'] . '/images/mc_gallery/' . $entry_id . '/' . $filename;
$filepath = "$image_dir/$entry_id/$filename";

//download_image($target_url, $filepath);

//------------------------
//  Show crop interface
//------------------------

$ci->setCropAllowResize(true);
$ci->setCropTypeDefault(ccRESIZEANY);
$ci->setCropTypeAllowChange(true);
$ci->setCropSizeDefault('2/2');
$ci->setCropPositionDefault(ccCENTRE);
$ci->setCropMinSize(10, 10);
$ci->setExtraParameters(array('test' => '1', 'fake' => 'this_var'));
$ci->setCropSizeList(array(
        '200x200' => '200 x 200 pixels',
        '320x240' => '320 x 240 pixels',
        '3:5'     => '3x5 portrait',
        '5:3'     => '3x5 landscape',
        '8:10'    => '8x10 portrait',
        '10:8'    => '8x10 landscape',
        '4:3'     => 'TV screen',
        '16:9'    => 'Widescreen',
        '2/2'     => 'Half size',
        '4/2'     => 'Quater width and half height'
        ));
$ci->setMaxDisplaySize('300x300');
$ci->loadInterface($filepath);

?>

<?php $ci->loadJavascript(); ?>

</body>
</html>
