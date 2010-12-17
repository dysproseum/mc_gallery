<?php

/**
 * $Id: test.cropcanvas.php 44 2006-06-26 10:05:41Z Andrew $
 * 
 * [Description]
 * 
 * Example file for class.cropcanvas.php.
 *
 * [Author]
 * 
 * Andrew Collington <php@amnuts.com> <http://php.amnuts.com/>
 */

require('class.cropcanvas.php');
$cc =& new CropCanvas();

if ($cc->loadImage(dirname(__FILE__). '/original.png')) {
    $cc->cropBySize('100', '100', ccBOTTOMRIGHT);
    $cc->saveImage(dirname(__FILE__). '/final1.jpg');
    $cc->flushImages();
}

if ($cc->loadImage(dirname(__FILE__). '/original2.png')) {
    $cc->cropByPercent(15, 50, ccCENTER);
    $cc->saveImage(dirname(__FILE__). '/final2.jpg', 90);
    $cc->flushImages();
}

if ($cc->loadImage(dirname(__FILE__). '/original3.png')) {
    $cc->cropToDimensions(67, 37, 420, 255);
    $cc->showImage('png');
}

?>