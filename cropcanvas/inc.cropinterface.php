<?php

/**
 * $Id: inc.cropinterface.php 49 2006-11-29 14:35:46Z Andrew $
 *
 * [Description]
 *
 * Required file for class.cropinterface.php.
 *
 * [Author]
 *
 * Andrew Collington <php@amnuts.com> <http://php.amnuts.com/>
 */

list($w, $h) = $this->calculateCropDimensions($this->crop['default']);
list($x, $y) = $this->calculateCropPosition($w, $h);

?>

<!--
    Styles for the interface.
    Don't change any of the php code segments or #theCrop, unless you know
    what you are doing.  Feel free to change the rest if you so desire.
-->

<style type="text/css">
    #cropInterface {
        border: 1px solid black;
        padding: 0;
        margin: 0;
        text-align: center;
        background-color: #fff;
        color: #000;
		font-family: Tahoma, Verdana, Arial, Helvetica, sans-serif;
		font-size: 10px;
        width: <?php echo $this->getImageWidth(); ?>px;
    }
    #cropDetails {
        margin: 5px;
        padding: 0;
    }
    #cropResize, #cropResize p {
        margin: 5px;
        padding: 0;
        font-size: 11px;
        display: <?php echo ($this->crop['change'] && $this->crop['resize']) ? 'inherit' : 'none'; ?>;
    }
    #cropSizes {
        margin: 5px;
        padding: 0;
        font-size: 11px;
        display: <?php echo (!empty($this->crop['sizes']) && $this->crop['resize']) ? 'inherit' : 'none'; ?>;
    }
    #cropImage {
        border-top: 1px solid black;
        border-bottom: 1px solid black;
        margin: 0;
        padding: 0;
    }
    #cropSubmitButton {
        font-size: 10px;
        font-family: "MS Sans Serif", Geneva, sans-serif;
        background-color: #D4D0C8;
        border: 0;
        margin: 0;
        padding: 5px;
        width: 100%;
    }
    #theCrop {
        position: absolute;
        background-color: transparent;
        border: 1px solid yellow;
        background-image: url(<?php echo $this->getImageSource(); ?>);
        background-repeat: no-repeat;
        padding: 0;
        margin: 0;
    }
    #cropImage, #theImage {
        width: <?php echo $this->getImageWidth(); ?>px;
        height: <?php echo $this->getImageHeight(); ?>px;
    }

    /* box model hack stuff */

    #theCrop {
        width: <?php echo $w; ?>px;
        font-family: "\"}\"";
        font-family:inherit;
        width:<?php echo ($w - 2); ?>px;
    }
    #theCrop {
        height: <?php echo $h; ?>px;
        font-family: "\"}\"";
        font-family:inherit;
        height:<?php echo ($h - 2); ?>px;
    }
    html>body #theCrop {
        width:<?php echo ($w - 2); ?>px;
        height:<?php echo ($h - 2); ?>px;
    }
</style>

<!--
    The main interface.
    You must not rename the ids because things may break!
-->

<div id="theCrop"></div>
<div id="cropInterface">
    <div id="cropDetails">
        <strong><?php echo basename($this->file); ?> (<?php echo $this->img['sizes'][0]; ?> x <?php echo $this->img['sizes'][1]; ?>)</strong>
        <div id="cropDimensions">&nbsp;</div>
    </div>
    <div id="cropImage"><img src="<?php echo $this->getImageSource(); ?>" alt="image" title="crop this image" name="theImage" id="theImage" /></div>
    <div id="cropResize">
        <p>Hold down 'shift' or 'control' while dragging to resize cropping area</p>
        <input type="radio" id="cropResizeAny" name="resize" onClick="cc_SetResizingType(0);"<?php if ($this->crop['type'] == ccRESIZEANY) { echo ' checked="checked"'; } ?> /> <label for="cropResizeAny">Any Dimensions</label> &nbsp; <input type="radio" name="resize" id="cropResizeProp" onClick="cc_SetResizingType(1);"<?php if ($this->crop['type'] == ccRESIZEPROP) { echo ' checked="checked"'; } ?> /> <label for="cropResizeProp">Proportional</label>
    </div>
    <div id="cropSizes">
        <select id="setSize" name="setSize" onchange="cc_setSize();">
            <option value="-1">Select a cropping size</option>
            <?php
                if (!empty($this->crop['sizes'])) {
                    foreach ($this->crop['sizes'] as $size => $desc) {
                        echo '<option value="', $size, '">', $desc, '</option>';
                    }
                }
            ?>
        </select>
    </div>
    <div id="cropSubmit">
        <input type="submit" value="crop the image" id="cropSubmitButton" onClick="cc_Submit();" />
    </div>
</div>

<!--
    Main javascript routines.
    Changing things here may break functionality, so don't tweak unless you
    know what you are doing.
-->

<script type="text/javascript" src="wz_dragdrop.js"></script>
<script type="text/javascript">
    function my_DragFunc()
    {
        dd.elements.theCrop.maxoffr = dd.elements.theImage.w - dd.elements.theCrop.w;
        dd.elements.theCrop.maxoffb = dd.elements.theImage.h - dd.elements.theCrop.h;
        dd.elements.theCrop.maxw    = <?php echo $this->getImageWidth(); ?>;
        dd.elements.theCrop.maxh    = <?php echo $this->getImageHeight(); ?>;
        cc_showCropSize();
		cc_reposBackground();
    }

    function my_ResizeFunc()
    {
        dd.elements.theCrop.maxw = (dd.elements.theImage.w + dd.elements.theImage.x) - dd.elements.theCrop.x;
        dd.elements.theCrop.maxh = (dd.elements.theImage.h + dd.elements.theImage.y) - dd.elements.theCrop.y;
        cc_showCropSize();
		cc_reposBackground();
    }

    function cc_Submit()
    {
        self.location.href = '<?php echo $_SERVER['PHP_SELF']; ?>?file=<?php echo $this->file; ?>&sx=' +
                Math.round((dd.elements.theCrop.x - dd.elements.theImage.x)<?php echo ($this->getRatio()) ? ' * ' . $this->getRatio() : ''; ?>) + '&sy=' +
                Math.round((dd.elements.theCrop.y - dd.elements.theImage.y)<?php echo ($this->getRatio()) ? ' * ' . $this->getRatio() : ''; ?>) + '&ex=' +
                Math.round(((dd.elements.theCrop.x - dd.elements.theImage.x) + dd.elements.theCrop.w)<?php echo ($this->getRatio()) ? ' * ' . $this->getRatio() : ''; ?>) + '&ey=' +
                Math.round(((dd.elements.theCrop.y - dd.elements.theImage.y) + dd.elements.theCrop.h)<?php echo ($this->getRatio()) ? ' * ' . $this->getRatio() : ''; ?>) +
                '<?php echo $this->params['str']; ?>';
    }

    function cc_SetResizingType(proportional)
    {
        if (proportional) {
            dd.elements.theCrop.defw = dd.elements.theCrop.w;
            dd.elements.theCrop.defh = dd.elements.theCrop.h;
            dd.elements.theCrop.scalable  = 1;
            dd.elements.theCrop.resizable = 0;
        } else {
            dd.elements.theCrop.scalable  = 0;
            dd.elements.theCrop.resizable = 1;
        }
    }

    function cc_reposBackground()
    {
        xPos = (dd.elements.theCrop.x - dd.elements.theImage.x + 1);
        yPos = (dd.elements.theCrop.y - dd.elements.theImage.y + 1);

        if (document.getElementById) {
            document.getElementById('theCrop').style.backgroundPosition = '-' + xPos + 'px -' + yPos + 'px';
        } else if (document.all) {
            document.all['theCrop'].style.backgroundPosition = '-' + xPos + 'px -' + yPos + 'px';
        } else {
            document.layers['theCrop'].backgroundPosition = '-' + xPos + 'px -' + yPos + 'px';
        }
    }

    function cc_showCropSize()
    {
        dd.elements.cropDimensions.write('Crop size: ' + dd.elements.theCrop.w + ' / ' + dd.elements.theCrop.h);
    }

    function cc_setSize()
    {
        element = document.getElementById('setSize');
        switch(element.value) {
        <?php
            $str = "case '%s':
                        cc_setCropDimensions(%d, %d);
                        dd.elements.theCrop.moveTo(dd.elements.theImage.x + %d, dd.elements.theImage.y + %d);
                        cc_reposBackground();
                        break\n";
            if ($this->crop['sizes']) {
                foreach ($this->crop['sizes'] as $s => $d) {
                    list($w, $h) = $this->calculateCropDimensions($s);
                    list($x, $y) = $this->calculateCropPosition($w, $h);
                    printf($str, $s, $w, $h, $x, $y);
                }
            }
        ?>
        }
        cc_showCropSize();
    }

    function cc_setCropDimensions(w, h)
    {
        dd.elements.theCrop.moveTo(dd.elements.theImage.x, dd.elements.theImage.y);
        dd.elements.theCrop.resizeTo(w, h);
        dd.elements.theCrop.defw = w;
        dd.elements.theCrop.defh = h;
        cc_reposBackground();
    }
</script>