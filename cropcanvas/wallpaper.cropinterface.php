<?php
define('EXT', 'Yo Mama!');

include('../../../config.php');
include('../../../includes/connect.php');
//include_once('../../../extensions/ext.remote_asset_storage.php');



function relative_path($full_path)
{
	$pos = strpos($full_path, '/images/mc_gallery');
	$rel = substr($full_path, $pos);
	return $rel;
}

function relative_dir($full_path, $filename)
{
	$pos = strpos($full_path, $filename);
	$rel = substr($full_path, 0, $pos);
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

function create_wallpaper(/*$remote_obj,*/ $watermark_image, $input, $new_width, $new_height)
{
	
	$webroot = $_POST['webroot'];

	$target_quality = 90;
	
	// Check if the source image is big enough
	list($source_width, $source_height) = getimagesize($input);
	if(($new_width > $source_width) || ($new_height > $source_height))
	{
		echo "Image size cannot support $new_width x $new_height <br />";
		return false;
	}
	
	// Get some info from the file
	$dir = dirname($input);
	$file = basename($input, ".jpg");
	$basename = "$dir/$file";
	$suffix = "$width_$height.jpg";
	$target_file = $basename."_".$new_width."x".$new_height.".jpg";
	
	// Resample Image
	$target_image = imagecreatetruecolor($new_width, $new_height);
	$image = imagecreatefromjpeg($input);
	if (!imagecopyresampled($target_image, $image, 0, 0, 0, 0, $new_width, $new_height, $source_width, $source_height))
		return false;
	
	imagealphablending($target_image, TRUE);
	
	// Add watermark
	if(file_exists($watermark))
	{
		$watermark = imagecreatefrompng($watermark_image);
		if(!$watermark)	return false;
			
		$watermark_width = imagesx($watermark);  
		$watermark_height = imagesy($watermark);
		
		$dest_x = $new_width - $watermark_width;
		$dest_y = $new_height - $watermark_height;
		if(!imagecopy($target_image, $watermark, $dest_x, $dest_y, 0, 0, $watermark_width, $watermark_height))
			return false;
		
		imagealphablending($target_image, FALSE);
		imagesavealpha($target_image, TRUE);
	}
	
	// Save Image
	if (!imagejpeg($target_image,$target_file,$target_quality))
		return false;
		
	// Upload image
	//$remote_obj->add_remote_asset($target_file, basename($target_file), relative_dir(relative_path($target_file), basename($target_file)));	
	//@unlink($target_file);
	
	return true;	
}

// Create wallpapers after selecting from interface
if(isset($_POST['file']) && isset($_POST['entry_id']) && isset($_POST['image_id'])) 
{
	//$RAS = new Remote_asset_storage();
	//if(!$RAS) die('Could not create RAS object');
	
	$watermark_image = $webroot."images/mc_gallery/watermark_image.png";
	
	$input = $_POST['file'];
	$entry_id = $_POST['entry_id'];
	$image_id = $_POST['image_id'];

	//$target_url = $conf['remote_asset_url'] . '/images/mc_gallery/' . $entry_id .'/'. basename($input);
	//download_image($target_url, $input);
	
	$sizes = array( "800" => "600",
					"1024" => "768",
					"1280" => "1024",
					"1600" => "1280",
					"2650" => "1600");
	
	foreach($sizes as $key => $val)
	{
		if(create_wallpaper(/*$RAS,*/ $watermark_image, $input, $key, $val))
		{
			// insert into db
			$sql = "UPDATE exp_mc_gallery_images SET size_$key".'x'."$val = 1 WHERE entry_id = $entry_id AND image_id = $image_id";
			
			if(!mysql_query($sql))
				echo "Failed to update desktop wallpaper database.".mysql_error()."<br />";
			
			echo "Created $key x $val <br />";
		}
		else echo "Failed to create $key x $val size <br />";
		
	}

	echo '<a href="#" onclick="window.close()">close</a>'; 
	
	exit;

}

/*---------------------------------------------------------------------*/

?>

<html>
<body>

<?php

$webroot=dirname(__FILE__) . "/../../../../";
$image_dir = $webroot."images/mc_gallery";

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

$filepath = "$image_dir/$entry_id/$filename";

?>

<form name="n" action="/system/modules/mc_gallery/cropcanvas/wallpaper.cropinterface.php" method="post">
	<input type="hidden" name="webroot" value="<?php echo $webroot; ?>">
	<input type="hidden" name="file" value="<?php echo $filepath; ?>" />
	<input type="hidden" name="entry_id" value="<?php echo $entry_id; ?>" />
	<input type="hidden" name="image_id" value="<?php echo $image_id; ?>" />
	<img height="100" src="<?php /*echo $conf['remote_asset_url'];*/ ?>/images/mc_gallery/<?php echo "$entry_id/$filename"; ?>" />
	<br />
	<input type="submit" value="Create Wallpaper" />
</form>

</body>
</html>
