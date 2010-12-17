<?php

/**
 * $Id: test.cropinterface.php 49 2006-11-29 14:35:46Z Andrew $
 *
 * [Description]
 *
 * Example file for class.cropinterface.php.
 *
 * [Author]
 *
 * Andrew Collington <php@amnuts.com> <http://php.amnuts.com/>
 */

require('class.cropinterface.php');
$ci =& new CropInterface(true);

if (isset($_GET['file'])) {
    $ci->loadImage($_GET['file']);
	$ci->cropToDimensions($_GET['sx'], $_GET['sy'], $_GET['ex'], $_GET['ey']);
	$ci->showImage('png', 100);
	exit;
}

?>

<html>

<body>

<div style="margin:5em;">

<?php

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
$ci->loadInterface('mypicture.jpg');

?>

</div>

<?php $ci->loadJavascript(); ?>

</body>
</html>