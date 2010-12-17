<?php

/*
=====================================================
 ExpressionEngine - MindComet Gallery Module
-----------------------------------------------------
 http://www.mindcomet.com/
=====================================================
 File: mcp.mc_gallery.php
-----------------------------------------------------
 Purpose: MindComet Gallery Module - CP
=====================================================
*/

if ( ! defined('EXT'))
{
    exit('Invalid file request');
}



class Mc_gallery_CP {

    var $version			= '.1';
    
    var $base_url			= '';    
    var $base_path			= '';    

    var $prefs				= array();
    var $permmissions		= array();

	var $title_prefix = 'MindComet Gallery: ';

	var $settings			= array();
	var $settings_gallery	= array();

	// These let us translate the base member groups
    var $english = array('Guests', 'Banned', 'Members', 'Pending', 'Super Admins');


    /** -------------------------------
    /**  Constructor
    /** -------------------------------*/
    
    function Mc_gallery_CP()
    {
        global $MOD, $IN, $DB, $DSP, $LANG, $PREFS, $LOC;

		error_reporting(E_ERROR | E_WARNING | E_PARSE);

		# Is the module installed?
		if ($IN->GBL('M') == 'INST')
		{
			return;
		}
        $query = $DB->query("SELECT module_version FROM exp_modules WHERE module_name = 'Mc_gallery'");
        if ($query->num_rows == 0)
        {
        	return;
        }

		# Fetch the mc gallery settings
		$query = $DB->query("SELECT * FROM exp_mc_gallery_settings");
		foreach($query->result as $row) {
			
			// Sean: This should allow us to override settings in conf.php
			if ($PREFS->ini('mc_gallery_'.$row['name']) != '') {
				$row['value'] = $PREFS->ini('mc_gallery_'.$row['name']);
			}
			
			$this->settings[$row['name']] = $row['value'];
		}

		$this->settings['theme_path']			= PATH_THEMES.'mc_gallery_themes/';
		$this->settings['theme_url'] 			= $PREFS->ini('theme_folder_url', 1).'mc_gallery_themes/';

		# Set the module base path for convenience
        $this->base_url = BASE.'&C=modules&M=mc_gallery';
		$this->base_path = PATH.'modules/mc_gallery/';

		# Extension Gallery Navigation
		$this->navbar_gallery =	array(
/*
						'navbar_gallery_upload_html'	=> array(
														'name'			=> 'ext_gallery_upload_html',						# Name of the Easy Shop module method
														'link'			=> 'javascript:ext_gallery_upload_html();',		# Used to create the URL $this->base_url.AMP.'P=home' (array)
														'page_children'	=> array('ext_gallery_upload_html')								# List of children pages $_GET['P'] (array)
													),
*/
						'navbar_gallery_images'		=> array(													# Name of the link used for $LANG->line('nav_hom'); (string)
														'name'			=> 'ext_gallery_images',						# Name of the Easy Shop module method
														'link'			=> 'javascript:ext_gallery_images('.$IN->GBL('entry_id').');',		# Used to create the URL $this->base_url.AMP.'P=home' (array)
														'row_properties'=> array(),									# Properties that define a row (TR) 
														'page_children'	=> array('ext_gallery_images')								# List of children pages $_GET['P'] (array)
													),
/*
													'navbar_gallery_order'		=> array(													# Name of the link used for $LANG->line('nav_hom'); (string)
														'name'			=> 'ext_gallery_order',						# Name of the Easy Shop module method
														'link'			=> 'javascript:ext_gallery_order();',		# Used to create the URL $this->base_url.AMP.'P=home' (array)
														'row_properties'=> array(),									# Properties that define a row (TR) 
														'page_children'	=> array('ext_gallery_order')								# List of children pages $_GET['P'] (array)
													),
*/
						'navbar_gallery_upload_flash'	=> array(
														'name'			=> 'ext_gallery_upload_flash',						# Name of the Easy Shop module method
														'link'			=> 'javascript:ext_gallery_upload_flash();',		# Used to create the URL $this->base_url.AMP.'P=home' (array)
														'page_children'	=> array('ext_gallery_upload_flash')								# List of children pages $_GET['P'] (array)
													),
						'navbar_gallery_settings'	=> array(
														'name'			=> 'ext_gallery_settings',						# Name of the Easy Shop module method
														'link'			=> 'javascript:ext_gallery_settings();',		# Used to create the URL $this->base_url.AMP.'P=home' (array)
														'page_children'	=> array('ext_gallery_settings')								# List of children pages $_GET['P'] (array)
													)
					);

		# Main Gallery Navigation
		$this->navbar_main =	array(
						'navbar_main_dashboard'	=> array(
														'name'			=> 'dashboard',						# Name of the Easy Shop module method
														'link'			=> $this->base_url.'&P=dashboard',		# Used to create the URL $this->base_url.AMP.'P=home' (array)
														'page_children'	=> array('dashboard')								# List of children pages $_GET['P'] (array)
													),
						'navbar_main_settings'		=> array(													# Name of the link used for $LANG->line('nav_hom'); (string)
														'name'			=> 'settings',						# Name of the Easy Shop module method
														'link'			=> $this->base_url.'&P=settings',		# Used to create the URL $this->base_url.AMP.'P=home' (array)
														'row_properties'=> array(),									# Properties that define a row (TR) 
														'page_children'	=> array('settings')								# List of children pages $_GET['P'] (array)
													)
					);


		# Fetch the current gallery settings
		$entry_id = $IN->GBL('entry_id');
		if (!empty($entry_id))
		{
			$sql_query = 'SELECT * FROM exp_mc_gallery WHERE entry_id="'.$IN->GBL('entry_id').'"';
			$query = $DB->query($sql_query);
			if ($query->num_rows > 0) {

				foreach($query->result[0] as $key => $val) {
					if (!empty($val)) {
						$this->settings[$key] = $val;
					}
				}
				$this->settings['exists'] = true;
			} else if ($IN->GBL('P') != 'ext_gallery_settings_action') {
				$_GET['P'] = 'ext_gallery_settings';
				$this->ext_gallery_settings();
				return;
			}
		}

		# Was the forum just installed?
		# If so, we'll force the preference page to be shown just to make sure they update their prefs
        if ($IN->GBL('P') != 'settings_update' AND $this->settings['general_install_date'] < 1)
        {
			$_GET['P'] = 'settings';
        	$this->settings();
        	return;
        }
		
		# Default Page to show
        $request = ($IN->GBL('P') != FALSE) ? $IN->GBL('P') : 'dashboard';
       
		# Show method
        if (method_exists($this, $request))
		{
			$this->$request();
		}
    }
	/* END */


	#########################
    #  mc_gallery  IMAGES
	#########################

	function ext_gallery_images_action() 
	{
		global $IN, $DB, $SESS;

		# Images Path
		$files_path = $this->settings['files_path'].$IN->GBL('entry_id').'/';
		$files_url = $this->settings['files_url'].$IN->GBL('entry_id').'/';

		# Single Entry Modification
		switch($IN->GBL('A')) {
			case 'image_cover': 
				# Reset all the covers to NO
				$sql_query = 'UPDATE exp_mc_gallery_images SET cover="No" WHERE entry_id="'.$IN->GBL('entry_id').'"';
				$DB->query($sql_query);

				# Set selected Image to be the cover
				$sql_query = 'UPDATE exp_mc_gallery_images SET cover="Yes" WHERE image_id="'.$IN->GBL('image_id').'"';
				$DB->query($sql_query);

				exit;
				break;
			case 'image_caption': 
				# Set selected Image to be the cover
				$sql_query = 'UPDATE exp_mc_gallery_images SET caption="'.$IN->GBL('caption').'" WHERE image_id="'.$IN->GBL('image_id').'"';
				$DB->query($sql_query);

				//echo 'alert("Set Caption to '.$IN->GBL('caption').' for image_id '.$IN->GBL('image_id').'");';
				exit;
				break;
			case 'image_description': 
				# Set selected Image to be the cover
				$sql_query = 'UPDATE exp_mc_gallery_images SET description="'.$IN->GBL('description').'" WHERE image_id="'.$IN->GBL('image_id').'"';
				$DB->query($sql_query);

				//echo 'alert("Set Description to '.$IN->GBL('description').' for image_id '.$IN->GBL('image_id').'");';
				exit;
				break;
			case 'image_order': 
				# Set selected Image to be the cover
				$sql_query = 'UPDATE exp_mc_gallery_images SET order_id="'.$IN->GBL('order_id').'" WHERE image_id="'.$IN->GBL('image_id').'"';
				$DB->query($sql_query);
				exit;
				break;
			case 'image_delete': 

				# Set selected Image to be the cover
				$sql_query = 'SELECT filename FROM exp_mc_gallery_images WHERE image_id="'.$IN->GBL('image_id').'"';
				$results = $DB->query($sql_query);

//				list($filename,$file_extension) = explode('.',$results->row['filename']);
				list($filename,$file_extension) = $this->_get_file_extension($results->row['filename']);

				$files['original'] = $filename.'.'.$file_extension;
				$files['small'] = $filename.'_s.'.$file_extension;
				$files['medium'] = $filename.'_m.'.$file_extension;
				$files['large'] = $filename.'_l.'.$file_extension;
				$files['thumbnail'] = $filename.'_thumb.'.$file_extension;

				# Delete Small, Medium, Large, Thumbnail Images
				foreach ($files as $file) {
					if (file_exists($files_path.$file)) {
						unlink($files_path.$file);
#					} else {
#						echo 'alert("Image '.$files_path.$file.' does not exist.");';
					}
				}

				$sql_query = 'DELETE FROM exp_mc_gallery_images WHERE image_id="'.$IN->GBL('image_id').'"';
				$DB->query($sql_query);

				echo 'alert("Successfully Deleted Image");';
				exit;
				break;
		}

		# Multiple Entries Multiplication
		$sql_query = 'SELECT filename FROM exp_mc_gallery_images WHERE entry_id = "'.$IN->GBL('entry_id').'"';
		$results = $DB->query($sql_query);
		foreach($results->result as $fields) {

			# list($filename,$file_extension) = explode('.',$fields['filename']);
			list($filename,$file_extension) = $this->_get_file_extension($fields['filename']);
			$source_path = $files_path.$filename.'.'.$file_extension;
			if ($IN->GBL('which') == 'small') {
				$target_path = $files_path.$filename.'_s.'.$file_extension;
			} else if ($IN->GBL('which') == 'medium') {
				$target_path = $files_path.$filename.'_m.'.$file_extension;
			} else if ($IN->GBL('which') == 'large') {
				$target_path = $files_path.$filename.'_l.'.$file_extension;
			} else {
				echo 'alert("Unable to determine target_path > WHICH '.$IN->GBL('which').' ");';
				exit;
			}

			switch($IN->GBL('A')) {
				case 'entry_recreate': 
//					echo 'alert("RECREATE SOURCE: '.$source_path.' > TARGET: '.$target_path.' > Which: \"'.$IN->GBL('which').'\" Image: \"'.$IN->GBL('image_width').'\" by \"'.$IN->GBL('image_height').'\" pixels");';

					# Recreate Image
					$this->_recreate_image($source_path,$target_path,$IN->GBL('image_width'),$IN->GBL('image_height'),$IN->GBL('image_quality'),$IN->GBL('image_scale'),$file_extension);

					$message = 'alert("Succesfully recreated resized images.")';
					break;
				case 'entry_delete': 

					# Delete Target Images
					if (file_exists($target_path)) {
						unlink($target_path);
					}

					$message = 'alert("Succesfully deleted images.")';
					break;
			}
		}

		switch($IN->GBL('A')) {
			case 'entry_recreate': 
				# $small_height,$small_width,$small_quality,$small_scale,$medium_height,$medium_width,$medium_quality,$medium_scale,$large_height,$large_width,$large_quality,$large_scale

				if ($IN->GBL('which') == 'small') {
					$this->settings['small_width'] = $IN->GBL('image_width');
					$this->settings['small_height'] = $IN->GBL('image_height');
					$this->settings['small_quality'] = $IN->GBL('image_quality');
					$this->settings['small_scale'] = $IN->GBL('image_scale');
				} else if ($IN->GBL('which') == 'medium') {
					$this->settings['medium_width'] = $IN->GBL('image_width');
					$this->settings['medium_height'] = $IN->GBL('image_height');
					$this->settings['medium_quality'] = $IN->GBL('image_quality');
					$this->settings['medium_scale'] = $IN->GBL('image_scale');
				} else if ($IN->GBL('which') == 'large') {
					$this->settings['large_width'] = $IN->GBL('image_width');
					$this->settings['large_height'] = $IN->GBL('image_height');
					$this->settings['large_quality'] = $IN->GBL('image_quality');
					$this->settings['large_scale'] = $IN->GBL('image_scale');
				}

				$data = $this->settings['small_width'].'||'.$this->settings['small_height'].'||'.$this->settings['small_quality'].'||'.$this->settings['small_scale'].'||'.$this->settings['medium_width'].'||'.$this->settings['medium_height'].'||'.$this->settings['medium_quality'].'||'.$this->settings['medium_scale'].'||'.$this->settings['large_width'].'||'.$this->settings['large_height'].'||'.$this->settings['large_quality'].'||'.$this->settings['large_scale'];

//				echo 'alert("data: '.addslashes($data).'");';

				$fp = fopen($files_path.'data.txt','w');
				if(!$fp) {
					echo 'alert("Error: Cannot open file.");';
					exit;
				}

				fwrite($fp,$data);
				fclose($fp);

				break;
		}


		echo $message;
		exit;
	}


	function ext_gallery_images() 
	{
		global $IN, $DB, $SESS, $TMPL, $DSP, $PREFS;

		$s = '';
		$r = '';
		
		# Navigations
		$r = $this->_navbar_horizontal($this->navbar_gallery);

		# Images Path
		$entry_id = $IN->GBL('entry_id');

		# Images Path
		$files_path = $this->settings['files_path'].$entry_id.'/';
		$files_url = $this->settings['files_url'].$entry_id.'/';

		# Other Settings
		$session_id = $SESS->userdata['session_id'];
		$base_url = $this->settings['system_url'].'index.php?S='.$session_id.'&C=modules&M=mc_gallery';

		# Small Images Operations
		if ($this->settings['small_scale'] == 'proportion') {
			$small_scale_proportion = 'checked="checked" ';
			$small_scale_crop = '';
		} else {
			$small_scale_proportion = '';
			$small_scale_crop = 'checked="checked" ';
		}
		$small_image =	$DSP->qdiv('itemWrapper', 
						'MAX Width x Height: '.BR.
						'<input type="text" name="small_width" style="font-family:Arial, Helvetica, sans-serif; font-size:10px; width:30px; height:10px;" value="'.$this->settings['small_width'].'"> x'.
						'<input type="text" name="small_height" style="font-family:Arial, Helvetica, sans-serif; font-size:10px; width:30px; height:10px;" value="'.$this->settings['small_height'].'">'.BR.BR.
						'<input type="radio" name="small_scale" style="height:10px;" value="proportion" '.$small_scale_proportion.'> Scale by Proportion'.BR.
						'<input type="radio" name="small_scale" style="height:10px;" value="crop" '.$small_scale_crop.'> Scale by Cropping'.BR.BR.
						'<input type="text" name="small_quality" style=" font-family:Arial, Helvetica, sans-serif; font-size:10px; width:30px; height:10px;" value="'.$this->settings['small_quality'].'" > % Image Quality'.BR.BR.
						'<a href="javascript: mod_gallery_entry_recreate(\'small\',\''.$entry_id.'\');">Recreate small Images</a>'.BR.
						'<a href="javascript: mod_gallery_entry_delete(\'small\',\''.$entry_id.'\');">Delete small Images</a>'.BR
		);

		# Medium Images Operations
		if ($this->settings['medium_scale'] == 'proportion') {
			$medium_scale_proportion = 'checked="checked" ';
			$medium_scale_crop = '';
		} else {
			$medium_scale_proportion = '';
			$medium_scale_crop = 'checked="checked" ';
		}
		$medium_image =	$DSP->qdiv('itemWrapper', 
						'MAX Width x Height: '.BR.
						'<input type="text" name="medium_width" style="font-family:Arial, Helvetica, sans-serif; font-size:10px; width:30px; height:10px;" value="'.$this->settings['medium_width'].'"> x'.
						'<input type="text" name="medium_height" style="font-family:Arial, Helvetica, sans-serif; font-size:10px; width:30px; height:10px;" value="'.$this->settings['medium_height'].'">'.BR.BR.
						'<input type="radio" name="medium_scale" style="height:10px;" value="proportion" '.$medium_scale_proportion.'> Scale by Proportion'.BR.
						'<input type="radio" name="medium_scale" style="height:10px;" value="crop" '.$medium_scale_crop.'> Scale by Cropping'.BR.BR.
						'<input type="text" name="medium_quality" style=" font-family:Arial, Helvetica, sans-serif; font-size:10px; width:30px; height:10px;" value="'.$this->settings['medium_quality'].'" > % Image Quality'.BR.BR.
						'<a href="javascript: mod_gallery_entry_recreate(\'medium\',\''.$entry_id.'\');">Recreate medium Images</a>'.BR.
						'<a href="javascript: mod_gallery_entry_delete(\'medium\',\''.$entry_id.'\');">Delete medium Images</a>'.BR
		);

		# Large Images Operations
		if ($this->settings['large_scale'] == 'proportion') {
			$large_scale_proportion = 'checked="checked" ';
			$large_scale_crop = '';
		} else {
			$large_scale_proportion = '';
			$large_scale_crop = 'checked="checked" ';
		}
		$large_image =	$DSP->qdiv('itemWrapper', 
						'MAX Width x Height: '.BR.
						'<input type="text" name="large_width" style="font-family:Arial, Helvetica, sans-serif; font-size:10px; width:30px; height:10px;" value="'.$this->settings['large_width'].'"> x'.
						'<input type="text" name="large_height" style="font-family:Arial, Helvetica, sans-serif; font-size:10px; width:30px; height:10px;" value="'.$this->settings['large_height'].'">'.BR.BR.
						'<input type="radio" name="large_scale" style="height:10px;" value="proportion" '.$large_scale_proportion.'> Scale by Proportion'.BR.
						'<input type="radio" name="large_scale" style="height:10px;" value="crop" '.$large_scale_crop.'> Scale by Cropping'.BR.BR.
						'<input type="text" name="large_quality" style=" font-family:Arial, Helvetica, sans-serif; font-size:10px; width:30px; height:10px;" value="'.$this->settings['large_quality'].'" > % Image Quality'.BR.BR.
						'<a href="javascript: mod_gallery_entry_recreate(\'large\',\''.$entry_id.'\');">Recreate Large Images</a>'.BR.
						'<a href="javascript: mod_gallery_entry_delete(\'large\',\''.$entry_id.'\');">Delete Large Images</a>'.BR
		);

		$r .= NL."<table class='clusterBox' border='0' cellpadding='0' cellspacing='0' style='margin:5px; width:475px; height:205px; float:left;'><tr>";	
		$r .= '<tr>'.NL;
		$r .= '<td colspan="3" style="padding: 10px 10px 0px 10px; text-align:center;" class="publishItemWrapper" nowrap="nowrap">'.NL;
		$r .= '<strong>SELECTED GALLERY:</strong> '.NL;

		# LIST GALLERIES
		$r .= '<select name="select" style="width:300px;" onchange="ext_gallery_images(this.value);">'.NL;
		$r .= '  <option value="'.$entry_id.'">Choose a Gallery</option>'.NL;
		$sql_query = 'SELECT * FROM exp_mc_gallery';
		$results = $DB->query($sql_query);
		foreach($results->result as $fields) {
			$sql_query = 'SELECT * FROM exp_weblog_titles WHERE entry_id="'.$fields['entry_id'].'"';
			$rs = $DB->query($sql_query);

			if ($fields['entry_id'] == $entry_id) $SELECTED = 'selected="selected"';
				else $SELECTED = '';

			$r .= '  <option value="'.$fields['entry_id'].'" '.$SELECTED.'>'.$rs->row['title'].'</option>'.NL;
		}
		$r .= '</select>'.NL;

		$r .= '</td>'.NL;
		$r .= '</tr>'.NL;
		
		if ($small_image != '')
		{
			$r .= NL.'<td class="publishItemWrapper" valign="top">'.BR;
			$r .= $DSP->div('clusterLineR');
			$r .= $DSP->heading(NBS.'Small Images Size', 5);
			$r .= $small_image;
			$r .= $DSP->div_c();
			$r .= '</td>';
		}

		if ($medium_image != '')
		{
			$r .= NL.'<td class="publishItemWrapper" valign="top">'.BR;
			$r .= $DSP->div('clusterLineR');
			$r .= $DSP->heading(NBS.'Medium Images Size', 5);
			$r .= $medium_image;
			$r .= $DSP->div_c();
			$r .= '</td>';
		}
		
		if ($large_image != '')
		{
			$r .= NL.'<td class="publishItemWrapper" valign="top">'.BR;
			$r .= $DSP->heading(NBS.'Large Images Size', 5);
			$r .= $large_image;
			$r .= '</td>';
		}
		
		$r .= "</tr></table>";
		
		# Hidden Action Div
		echo '<div id="extension_gallery_action"></div>';

		$r .= '<div id="items">';
		$results = $DB->query('SELECT * FROM exp_mc_gallery_images WHERE entry_id="'.$entry_id.'" ORDER BY order_id ASC');
		foreach($results->result as $fields) {

			# list($filename,$file_extension) = explode('.',$fields['filename']);
			list($filename,$file_extension) = $this->_get_file_extension($fields['filename']);
			$filename_small = $filename.'_s.'.$file_extension;
			$filename_medium = $filename.'_m.'.$file_extension;
			$filename_large = $filename.'_l.'.$file_extension;

			if (!empty($fields['description'])) {
				$description = $fields['description'].'<br />'.NL;
			}

			if ($fields['cover'] == 'Yes') {
				$background_color = 'background-color:#CCFFFF;';
			} else {
				$background_color = '';
			}
			
			$r .= '<div id="image_'.$fields['image_id'].'" class="sortable_item" style="'.$background_color.' text-align:center; margin:5px; padding-top: 10px; padding-bottom: 10px; font-family: Arial, Helvetica, sans-serif; font-size: 10px; border: 1px dotted #666666; width: 150px; height:225px;">'.NL.
			'<img src="'.$PREFS->ini('remote_asset_url').'/images/mc_gallery/'.$entry_id.'/'.$filename.'_thumb.'.$file_extension.'" border="0" width="100px" height="100px" alt="'.addslashes($fields['description']).'"><br /> '.NL.
			'<a href="javascript: mod_gallery_image_cover(\''.$fields['entry_id'].'\',\''.$fields['image_id'].'\');">Select Cover</a> | '.NL.
			'<a href="javascript: mod_gallery_image_delete(\''.$fields['entry_id'].'\',\''.$fields['image_id'].'\');">Delete Image</a>'.NL;

			# Format Full Filename
			if (strlen($filename) > 20)
			{
				$full_filename = substr($filename,0,15).'...'.$file_extension;
			}
			else 
			{
				$full_filename = $filename.'.'.$file_extension;
			}
			$r .= '<br /> <strong>'.$full_filename.' </strong><br /> '.BR.NL;

/*
			$r .= 'ORDER ID: <input type="text" name="order_id" value="'.$fields['order_id'].'" onblur="mod_gallery_image_order(\''.$fields['entry_id'].'\',\''.$fields['image_id'].'\',this.value)" style="width:50px; height:12px; font-family:Arial, Helvetica, sans-serif; font-size:10px;"> <br /> '.NL;
*/
			
			$r .= 'Caption: <input type="text" name="order_id" value="'.$fields['caption'].'" onblur="mod_gallery_image_caption(\''.$fields['entry_id'].'\',\''.$fields['image_id'].'\',this.value)" style="width:90px; height:12px; font-family:Arial, Helvetica, sans-serif; font-size:10px;"> <br /> '.NL;

			$r .= 'Description: <br><textarea rows=10 cols=10 name="order_id" onblur="mod_gallery_image_description(\''.$fields['entry_id'].'\',\''.$fields['image_id'].'\',this.value)" style="width:120px; height:36px; font-family:Arial, Helvetica, sans-serif; font-size:10px;">'.$fields['description'].'</textarea>  <br /> '.NL;

			// Show popup to Edit Image
			$r .= "<a style='cursor:pointer' onclick=\" javascript:window.open('modules/mc_gallery/cropcanvas/speed.cropinterface.php?entry_id=".$fields['entry_id']."&image_id=".$fields['image_id']."','Crop Images','width=500,height=500,scrollbars=1')\">Crop Image</a> <br />";

			// Create Desktop Wallpapers
			$r .= "<a style='cursor:pointer' onclick=\" javascript:window.open('modules/mc_gallery/cropcanvas/wallpaper.cropinterface.php?entry_id=".$fields['entry_id']."&image_id=".$fields['image_id']."','Desktop Wallpaper','width=500,height=500,scrollbars=1')\">Create Wallpaper</a>";

			/*


			if (file_exists($files_path.$filename_small)) {
				$r .= 'SMALL: <input type="text" name="filename_small" value="'.$files_url.$filename_small.'" onClick="javascript:this.focus();this.select();" style="width:80px; height:12px; font-family:Arial, Helvetica, sans-serif; font-size:10px;"> <br /> '.NL;
			} else {
				$r .= '<a href="javascript:mod_gallery_entry_recreate(\'small\',\''.$fields['entry_id'].'\');">Recreate SMALL Images</a> <br /> '.NL;
			}
			if (file_exists($files_path.$filename_medium)) {
				$r .= 'MEDIUM: <input type="text" name="filename_medium" value="'.$files_url.$filename_medium.'" onClick="javascript:this.focus();this.select();" style="width:80px; height:12px; font-family:Arial, Helvetica, sans-serif; font-size:10px;"><br /> '.NL;
			} else {
				$r .= '<a href="javascript:mod_gallery_entry_recreate(\'medium\',\''.$fields['entry_id'].'\');">Recreate MEDIUM Images</a> <br /> '.NL;
			}
			if (file_exists($files_path.$filename_large)) {
				$r .= 'LARGE: <input type="text" name="filename_large" value="'.$files_url.$filename_large.'" onClick="javascript:this.focus();this.select();" style="width:80px; height:12px; font-family:Arial, Helvetica, sans-serif; font-size:10px;"><br />'.NL;
			} else {
				$r .= '<a href="javascript:mod_gallery_entry_recreate(\'large\',\''.$fields['entry_id'].'\');">Recreate LARGE Images</a> <br /> '.NL;
			}
			
			*/
			
			$r .= '</div>'.NL;

		}

		$r .= '</div>';

		# Hidden Action Div
		echo '<div id="extension_gallery_action"></div>';

		echo $r;
		echo $s;
		exit;
	}


	#########################
    #  mc_gallery UPLOADING
	#########################
	
	function ext_gallery_upload_flash() 
	{
		global $DSP, $IN;

		# Variables
		$files_path = $this->settings['files_path'].$IN->GBL('entry_id').'/';
		$files_url = $this->settings['files_url'].$IN->GBL('entry_id').'/';
		$base_url = $this->settings['system_url'].'index.php?P=ext_gallery_upload_files';

		$r  = $this->_navbar_horizontal($this->navbar_gallery);
		# Small Images Operations
		if ($this->settings['small_scale'] == 'proportion') {
			$small_scale_proportion = 'checked="checked" ';
			$small_scale_crop = '';
		} else {
			$small_scale_proportion = '';
			$small_scale_crop = 'checked="checked" ';
		}
		$small_image =	$DSP->qdiv('itemWrapper', 
						'MAX Width x Height: '.BR.
						'<input type="text" name="small_width" style="font-family:Arial, Helvetica, sans-serif; font-size:10px; width:30px; height:10px;" value="'.$this->settings['small_width'].'"> x'.
						'<input type="text" name="small_height" style="font-family:Arial, Helvetica, sans-serif; font-size:10px; width:30px; height:10px;" value="'.$this->settings['small_height'].'">'.BR.BR.
						'<input type="radio" name="small_scale" style="height:10px;" value="proportion" '.$small_scale_proportion.'> Scale by Proportion'.BR.
						'<input type="radio" name="small_scale" style="height:10px;" value="crop" '.$small_scale_crop.'> Scale by Cropping'.BR.BR.
						'<input type="text" name="small_quality" style=" font-family:Arial, Helvetica, sans-serif; font-size:10px; width:30px; height:10px;" value="'.$this->settings['small_quality'].'" > % Image Quality'
		);

		# Medium Images Operations
		if ($this->settings['medium_scale'] == 'proportion') {
			$medium_scale_proportion = 'checked="checked" ';
			$medium_scale_crop = '';
		} else {
			$medium_scale_proportion = '';
			$medium_scale_crop = 'checked="checked" ';
		}
		$medium_image =	$DSP->qdiv('itemWrapper', 
						'MAX Width x Height: '.BR.
						'<input type="text" name="medium_width" style="font-family:Arial, Helvetica, sans-serif; font-size:10px; width:30px; height:10px;" value="'.$this->settings['medium_width'].'"> x'.
						'<input type="text" name="medium_height" style="font-family:Arial, Helvetica, sans-serif; font-size:10px; width:30px; height:10px;" value="'.$this->settings['medium_height'].'">'.BR.BR.
						'<input type="radio" name="medium_scale" style="height:10px;" value="proportion" '.$medium_scale_proportion.'> Scale by Proportion'.BR.
						'<input type="radio" name="medium_scale" style="height:10px;" value="crop" '.$medium_scale_crop.'> Scale by Cropping'.BR.BR.
						'<input type="text" name="medium_quality" style=" font-family:Arial, Helvetica, sans-serif; font-size:10px; width:30px; height:10px;" value="'.$this->settings['medium_quality'].'" > % Image Quality'
		);

		# Large Images Operations
		if ($this->settings['large_scale'] == 'proportion') {
			$large_scale_proportion = 'checked="checked" ';
			$large_scale_crop = '';
		} else {
			$large_scale_proportion = '';
			$large_scale_crop = 'checked="checked" ';
		}
		$large_image =	$DSP->qdiv('itemWrapper', 
						'MAX Width x Height: '.BR.
						'<input type="text" name="large_width" style="font-family:Arial, Helvetica, sans-serif; font-size:10px; width:30px; height:10px;" value="'.$this->settings['large_width'].'"> x'.
						'<input type="text" name="large_height" style="font-family:Arial, Helvetica, sans-serif; font-size:10px; width:30px; height:10px;" value="'.$this->settings['large_height'].'">'.BR.BR.
						'<input type="radio" name="large_scale" style="height:10px;" value="proportion" '.$large_scale_proportion.'> Scale by Proportion'.BR.
						'<input type="radio" name="large_scale" style="height:10px;" value="crop" '.$large_scale_crop.'> Scale by Cropping'.BR.BR.
						'<input type="text" name="large_quality" style=" font-family:Arial, Helvetica, sans-serif; font-size:10px; width:30px; height:10px;" value="'.$this->settings['large_quality'].'" > % Image Quality'
		);

		$r .= NL."<table class='clusterBox' border='0' cellpadding='0' cellspacing='0' style='margin:5px; width:475px; float:left;'><tr>";	
		$r .= '<tr>'.NL;
		$r .= '<td colspan="3" style="padding: 10px 10px 0px 10px;" class="publishItemWrapper"><button id="btnBrowse" type="button" style="width: 100%; padding: 5px;" onclick="swfu.browse(); this.blur(); "><img src="'.$this->settings['jquery_url'].'images/page_white_add.png" style="padding-right: 3px; vertical-align: bottom;">Select Images to Upload <span style="font-size: 7pt;">(2 MB Max)</span><img src="'.$this->settings['jquery_url'].'images/page_white_add.png" style="padding-left: 3px; vertical-align: bottom;"></button></td>'.NL;
		$r .= '</tr>'.NL;
		
		if ($small_image != '')
		{
			$r .= NL.'<td class="publishItemWrapper" valign="top">'.BR;
			$r .= $DSP->div('clusterLineR');
			$r .= $DSP->heading(NBS.'Small Images Size', 5);
			$r .= $small_image;
			$r .= $DSP->div_c();
			$r .= '</td>';
		}

		if ($medium_image != '')
		{
			$r .= NL.'<td class="publishItemWrapper" valign="top">'.BR;
			$r .= $DSP->div('clusterLineR');
			$r .= $DSP->heading(NBS.'Medium Images Size', 5);
			$r .= $medium_image;
			$r .= $DSP->div_c();
			$r .= '</td>';
		}
		
		if ($large_image != '')
		{
			$r .= NL.'<td class="publishItemWrapper" valign="top">'.BR;
			$r .= $DSP->heading(NBS.'Large Images Size', 5);
			$r .= $large_image;
			$r .= '</td>';
		}
		

//			<div id="title" class="title"><a class="likeParent" href="../index.php">SWFUpload (Revision 6.2) Application Demo</a></div>

		$r .= '</tr>'.NL;
		$r .= '<tr>'.NL;
		$r .= '<td colspan="3" style="padding: 0px 10px 10px 10px;" class="publishItemWrapper"><button id="btnBrowse" type="button" style="width: 100%; padding: 5px;" onclick="mod_gallery_update_settings('.$IN->GBL('entry_id').'); this.blur(); "> - Update Image Settings - </span></button></td>'.NL;
		$r .= '</tr>'.NL;
		$r .= '</table>';

$r .= <<<EOT

	<div id="swfu_container" style="display: none; margin: 5px;">
		<div style="float:left; margin: 10px;">
			<div id="divFileProgressContainer" style="height: 75px;"></div>
			<div id="degraded_container">
				SWFUpload has not loaded.  It may take a few moments.  SWFUpload requires JavaScript and Flash Player 8 or later.
			</div>
		</div>
		<div id="thumbnails"></div>
	</div>

EOT;

		echo $r;
		exit;
	}


	#########################
    #  mc_gallery SETTINGS
	#########################

	function ext_gallery_settings_action() 
	{
		global $IN, $DB, $SESS;

		# Images Path
		$files_path = $this->settings['files_path'].$IN->GBL('entry_id').'/';
		$files_url = $this->settings['files_url'].$IN->GBL('entry_id').'/';

		# $small_height,$small_width,$small_quality,$small_scale,$medium_height,$medium_width,$medium_quality,$medium_scale,$large_height,$large_width,$large_quality,$large_scale

		$field_values['status'] = $IN->GBL('status');
		$field_values['entry_id'] = $IN->GBL('entry_id');
		$field_values['small_width'] = $IN->GBL('small_width');
		$field_values['small_height'] = $IN->GBL('small_height');
		$field_values['small_quality'] = $IN->GBL('small_quality');
		$field_values['small_scale'] = $IN->GBL('small_scale');
		$field_values['medium_width'] = $IN->GBL('medium_width');
		$field_values['medium_height'] = $IN->GBL('medium_height');
		$field_values['medium_quality'] = $IN->GBL('medium_quality');
		$field_values['medium_scale'] = $IN->GBL('medium_scale');
		$field_values['large_width'] = $IN->GBL('large_width');
		$field_values['large_height'] = $IN->GBL('large_height');
		$field_values['large_quality'] = $IN->GBL('large_quality');
		$field_values['large_scale'] = $IN->GBL('large_scale');

		# Multiple Entries Multiplication
		$sql_query = 'SELECT * FROM exp_mc_gallery WHERE entry_id = "'.$IN->GBL('entry_id').'"';
		$results = $DB->query($sql_query);
		if ($results->num_rows > 0)
		{
			$sql_query = $DB->update_string('exp_mc_gallery', $field_values, 'entry_id = "'.$IN->GBL('entry_id').'"');
#			echo 'update: '.$sql_query.'<BR>';;
			$DB->query($sql_query);
		}
		else
		{
			$sql_query = $DB->insert_string('exp_mc_gallery', $field_values);
#			echo 'insert: '.$sql_query.'<BR>';;
			$DB->query($sql_query);
		}


		$message = "alert('Successfully added gallery settings to the database.')";

		echo $message;
		exit;
	}

	
	function ext_gallery_settings() 
	{

		global $DSP, $DB, $IN;

		# Variables
		$files_path = $this->settings['files_path'].$IN->GBL('entry_id').'/';
		$files_url = $this->settings['files_url'].$IN->GBL('entry_id').'/';
		$base_url = $this->settings['system_url'].'index.php?P=ext_gallery_upload_files';

		$r = '';
		if ($this->settings['exists'] == 'true') {
			$r = $this->_navbar_horizontal($this->navbar_gallery);
		}

		# Defaults
		if (empty($this->settings['small_width'])) $this->settings['small_width'] = '100';
		if (empty($this->settings['small_quality'])) $this->settings['small_quality'] = '75';
		if (empty($this->settings['small_scale'])) $this->settings['small_scale'] = 'crop';
		if (empty($this->settings['medium_width'])) $this->settings['medium_width'] = '250';
		if (empty($this->settings['medium_quality'])) $this->settings['medium_quality'] = '85';
		if (empty($this->settings['medium_scale'])) $this->settings['medium_scale'] = 'proportion';
		if (empty($this->settings['large_width'])) $this->settings['large_width'] = '500';
		if (empty($this->settings['large_quality'])) $this->settings['large_quality'] = '95';
		if (empty($this->settings['large_scale'])) $this->settings['large_scale'] = 'proportion';

		# Small Images Operations
		if ($this->settings['small_scale'] == 'proportion') {
			$small_scale_proportion = 'checked="checked" ';
			$small_scale_crop = '';
		} else {
			$small_scale_proportion = '';
			$small_scale_crop = 'checked="checked" ';
		}
		$small_image =	$DSP->qdiv('itemWrapper', 
						'MAX Width x Height: '.BR.
						'<input type="text" name="small_width" style="font-family:Arial, Helvetica, sans-serif; font-size:10px; width:30px; height:10px;" value="'.$this->settings['small_width'].'"> x'.
						'<input type="text" name="small_height" style="font-family:Arial, Helvetica, sans-serif; font-size:10px; width:30px; height:10px;" value="'.$this->settings['small_height'].'">'.BR.BR.
						'<input type="radio" name="small_scale" style="height:10px;" value="proportion" '.$small_scale_proportion.'> Scale by Proportion'.BR.
						'<input type="radio" name="small_scale" style="height:10px;" value="crop" '.$small_scale_crop.'> Scale by Cropping'.BR.BR.
						'<input type="text" name="small_quality" style=" font-family:Arial, Helvetica, sans-serif; font-size:10px; width:30px; height:10px;" value="'.$this->settings['small_quality'].'" > % Image Quality'
		);

		# Medium Images Operations
		if ($this->settings['medium_scale'] == 'proportion') {
			$medium_scale_proportion = 'checked="checked" ';
			$medium_scale_crop = '';
		} else {
			$medium_scale_proportion = '';
			$medium_scale_crop = 'checked="checked" ';
		}
		$medium_image =	$DSP->qdiv('itemWrapper', 
						'MAX Width x Height: '.BR.
						'<input type="text" name="medium_width" style="font-family:Arial, Helvetica, sans-serif; font-size:10px; width:30px; height:10px;" value="'.$this->settings['medium_width'].'"> x'.
						'<input type="text" name="medium_height" style="font-family:Arial, Helvetica, sans-serif; font-size:10px; width:30px; height:10px;" value="'.$this->settings['medium_height'].'">'.BR.BR.
						'<input type="radio" name="medium_scale" style="height:10px;" value="proportion" '.$medium_scale_proportion.'> Scale by Proportion'.BR.
						'<input type="radio" name="medium_scale" style="height:10px;" value="crop" '.$medium_scale_crop.'> Scale by Cropping'.BR.BR.
						'<input type="text" name="medium_quality" style=" font-family:Arial, Helvetica, sans-serif; font-size:10px; width:30px; height:10px;" value="'.$this->settings['medium_quality'].'" > % Image Quality'
		);

		# Large Images Operations
		if ($this->settings['large_scale'] == 'proportion') {
			$large_scale_proportion = 'checked="checked" ';
			$large_scale_crop = '';
		} else {
			$large_scale_proportion = '';
			$large_scale_crop = 'checked="checked" ';
		}
		$large_image =	$DSP->qdiv('itemWrapper', 
						'MAX Width x Height: '.BR.
						'<input type="text" name="large_width" style="font-family:Arial, Helvetica, sans-serif; font-size:10px; width:30px; height:10px;" value="'.$this->settings['large_width'].'"> x'.
						'<input type="text" name="large_height" style="font-family:Arial, Helvetica, sans-serif; font-size:10px; width:30px; height:10px;" value="'.$this->settings['large_height'].'">'.BR.BR.
						'<input type="radio" name="large_scale" style="height:10px;" value="proportion" '.$large_scale_proportion.'> Scale by Proportion'.BR.
						'<input type="radio" name="large_scale" style="height:10px;" value="crop" '.$large_scale_crop.'> Scale by Cropping'.BR.BR.
						'<input type="text" name="large_quality" style=" font-family:Arial, Helvetica, sans-serif; font-size:10px; width:30px; height:10px;" value="'.$this->settings['large_quality'].'" > % Image Quality'
		);

		$r .= NL."<table class='clusterBox' border='0' cellpadding='0' cellspacing='0' style='margin:5px; width:90%; float:left;'><tr>";	

		if ($this->settings['status'] == 'Active') {
			$status_active = 'checked="checked" ';
			$status_inactive = '';
		} else {
			$status_active = '';
			$status_inactive = 'checked="checked" ';
		}

		$r .= '<tr>'.NL;
		$r .= '<td colspan="3" style="padding: 10px 10px 0px 10px;" class="publishItemWrapper">'.NL;
		$r .= $DSP->heading('Gallery Status', 5);
		//$r .= 'Do you want to show the gallery images in this entry or use it for image management purpose only.'.BR;
		//$r .= '<input type="radio" name="status" style="height:10px;" value="Active" '.$status_active.'> Active'.BR;
		//$r .= '<input type="radio" name="status" style="height:10px;" value="Inactive" '.$status_inactive.'> Inactive'.BR;
		$r .= '<input type="hidden" name="status" value="open">';	
$r .= '</td>'.NL;
		$r .= '</tr>'.NL;

		if ($small_image != '')
		{
			$r .= NL.'<td class="publishItemWrapper" valign="top">'.BR;
			$r .= $DSP->div('clusterLineR');
			$r .= $DSP->heading('Small Images Size', 5);
			$r .= $small_image;
			$r .= $DSP->div_c();
			$r .= '</td>';
		}

		if ($medium_image != '')
		{
			$r .= NL.'<td class="publishItemWrapper" valign="top">'.BR;
			$r .= $DSP->div('clusterLineR');
			$r .= $DSP->heading('Medium Images Size', 5);
			$r .= $medium_image;
			$r .= $DSP->div_c();
			$r .= '</td>';
		}
		
		if ($large_image != '')
		{
			$r .= NL.'<td class="publishItemWrapper" valign="top">'.BR;
			$r .= $DSP->heading('Large Images Size', 5);
			$r .= $large_image;
			$r .= '</td>';
		}
		
		$r .= '</tr>'.NL;
		$r .= '<tr>'.NL;
		$r .= '<td colspan="3" style="padding: 0px 10px 10px 10px;" class="publishItemWrapper"><button id="btnBrowse" type="button" style="width: 100%; padding: 5px;" onclick="mod_gallery_settings('.$IN->GBL('entry_id').'); this.blur(); "> - Update Image Settings - </span></button></td>'.NL;
		$r .= '</tr>'.NL;
		$r .= '</table>';

		echo $r;
		exit;
	}


	####################
	# MODULES
	####################

	function update_settings() {

		global $IN;
		echo 'alert("accesses: update_settings function");';

		# Images Path
		$entry_id = $IN->GBL('entry_id');

		# Images Path
		$files_path = $this->settings['files_path'].$entry_id.'/';
		$files_url = $this->settings['files_url'].$entry_id.'/';

		# Other Settings
		$session_id = $SESS->userdata['session_id'];
		$base_url = $this->settings['system_url'].'index.php?S='.$session_id.'&C=modules&M=mc_gallery';

		# $small_height,$small_width,$small_quality,$small_scale,$medium_height,$medium_width,$medium_quality,$medium_scale,$large_height,$large_width,$large_quality,$large_scale
		$this->settings['small_width'] = $IN->GBL('small_width');
		$this->settings['small_height'] = $IN->GBL('small_height');
		$this->settings['small_quality'] = $IN->GBL('small_quality');
		$this->settings['small_scale'] = $IN->GBL('small_scale');
		$this->settings['medium_width'] = $IN->GBL('medium_width');
		$this->settings['medium_height'] = $IN->GBL('medium_height');
		$this->settings['medium_quality'] = $IN->GBL('medium_quality');
		$this->settings['medium_scale'] = $IN->GBL('medium_scale');
		$this->settings['large_width'] = $IN->GBL('large_width');
		$this->settings['large_height'] = $IN->GBL('large_height');
		$this->settings['large_quality'] = $IN->GBL('large_quality');
		$this->settings['large_scale'] = $IN->GBL('large_scale');

		$data = $this->settings['small_width'].'||'.$this->settings['small_height'].'||'.$this->settings['small_quality'].'||'.$this->settings['small_scale'].'||'.$this->settings['medium_width'].'||'.$this->settings['medium_height'].'||'.$this->settings['medium_quality'].'||'.$this->settings['medium_scale'].'||'.$this->settings['large_width'].'||'.$this->settings['large_height'].'||'.$this->settings['large_quality'].'||'.$this->settings['large_scale'];

		echo 'alert("data: '.addslashes($data).'");';

		$fp = fopen($files_path.'data.txt','w');
		if(!$fp) {
			echo 'alert("Error: Cannot open file.");';
			exit;
		}

		fwrite($fp,$data);
		fclose($fp);

		//echo 'alert("Successfully Updated entry_id '.$entry_id.' Image Settings");';
		exit;
	}


	#########################
    #  mc_gallery Internal Options
	#########################

	//function to return the extension of a filename in lowercase without a "." (DOT)
	function _get_file_extension($filename)
	{
		$file_extension = strtolower(str_replace('.', '', strrchr($filename, '.')));
		$filename = str_replace('.'.$file_extension, '', $filename);
		return array($filename,$file_extension);
	}

	function _recreate_image($source_file,$target_file,$target_width='100',$target_height='100',$target_quality='85',$target_scale='proportion',$file_extension='jpeg')
	{
		# Other file extensions
		if ($file_extension == 'jpg') $file_extension = 'jpeg';

		# Set Defaults
			if ($target_width == '') {
				$target_width = '100';
			} else if ($target_quality == '') {
				$target_quality = '85';
			} else if ($target_scale == '') {
				$target_scale = 'proportion';
			}

		// Get new dimensions
		list($source_width, $source_height) = getimagesize($source_file);

		if ($target_scale == 'crop') {

			if (!empty($target_width)) {
				$target_height = $target_width;
			} else {
				$target_width = $target_height;
			}

			# RESIZE

			if ($source_width > $source_height) {
				$new_height = $target_height;
				$new_width = $target_height * $source_width / $source_height;
			} else {
				$new_height = $target_width * $source_height / $source_width;
				$new_width = $target_width;
			}

			// Resample Image
			$target_image = imagecreatetruecolor($new_width, $new_height);

			if ($file_extension == 'jpeg') {
				$image = @imagecreatefromjpeg($source_file);
			} else if ($file_extension == 'gif') {
				$image = @imagecreatefromgif($source_file);
			}

			if (!@imagecopyresampled($target_image, $image, 0, 0, 0, 0, $new_width, $new_height, $source_width, $source_height)) {
				echo 'alert("Could not resize image")';
				exit(0);
			}

			if ($file_extension == 'jpeg') {
				if (!imagejpeg($target_image,$target_file,$target_quality)) {
					echo 'alert("Can not create file ('.$target_file.')")';
					exit;
				}
			} else if ($file_extension == 'gif') {
				if (!imagegif($target_image,$target_file)) {
					echo 'alert("Can not create file ('.$target_file.')")';
					exit;
				}
			}

			imagedestroy($target_image);
			imagedestroy($image);

			# CROPPING

			// cut out a rectangle from the resized image and store in thumbnail
			$thumbx = (($new_width / 2) - ($target_width / 2));
			$thumby = (($new_height / 2) - ($target_height / 2));

			// Resample Image
			$target_image = imagecreatetruecolor($target_width, $target_height);

			if ($file_extension == 'jpeg') {
				$image = @imagecreatefromjpeg($target_file);
			} else if ($file_extension == 'gif') {
				$image = @imagecreatefromgif($target_file);
			}

			//this is the thumbnail image, where the above is cropped.
			imagecopyresized($target_image, $image, 0, 0, $thumbx, $thumby, $target_width, $target_height, $target_width, $target_height);

			if ($file_extension == 'jpeg') {
				if (!imagejpeg($target_image,$target_file,$target_quality)) {
					echo 'alert("Can not create file ('.$target_file.')")';
					exit;
				}
			} else if ($file_extension == 'gif') {
				if (!imagegif($target_image,$target_file)) {
					echo 'alert("Can not create file ('.$target_file.')")';
					exit;
				}
			}
		} else {

			if (!empty($target_width)) {
				$target_height = '';
			} else {
				$target_width = '';
			}

			# RESIZE PROPORTION
			if ($target_height > $target_width) {
				$new_height = $target_height;
				$new_width = $target_height * $source_width / $source_height;
			} else {
				$new_height = $target_width * $source_height / $source_width;
				$new_width = $target_width;
			}

			// Resample Image
			$target_image = imagecreatetruecolor($new_width, $new_height);

			if ($file_extension == 'jpeg') {
				$image = @imagecreatefromjpeg($source_file);
			} else if ($file_extension == 'gif') {
				$image = @imagecreatefromgif($source_file);
			}

			if (!@imagecopyresampled($target_image, $image, 0, 0, 0, 0, $new_width, $new_height, $source_width, $source_height)) {
				echo 'alert("Could not resize image")';
				exit(0);
			}

			if ($file_extension == 'jpeg') {
				if (!imagejpeg($target_image,$target_file,$target_quality)) {
					echo 'alert("Can not create file ('.$target_file.')")';
					exit;
				}
			} else if ($file_extension == 'gif') {
				if (!imagegif($target_image,$target_file)) {
					echo 'alert("Can not create file ('.$target_file.')")';
					exit;
				}
			}
		}
	}


	#########################
    #  mc_gallery HOMEPAGE
	#########################
		
	function dashboard()
	{
        global $DSP, $IN, $DB, $LANG, $PREFS;

        $DSP->title  = $this->title_prefix.$LANG->line('main_navbar_dashboard');
        $DSP->crumb .= $DSP->anchor(BASE.AMP.'C=modules'.AMP.'M=mc_gallery', $LANG->line('mc_gallery_module_name'));
		$DSP->crumb	.= $DSP->build_crumb($LANG->line('main_navbar_dashboard'));

		# Display main navbar
		$DSP->body .= $this->_navbar_tabbed($this->navbar_main);

		# Header and Messages
		$DSP->body .= $this->_message_box($IN->GBL['msg']);

		$DSP->body .= BR;

		# Preload HTML
		$DSP->body .= '<div class="box">';
		$DSP->body .= '<h5>mc Gallery Information</h5>'.BR.NL;
//		$DSP->body .= 'Whatever'.BR.NL;
		$DSP->body .= '</div>';     

	}
    /* END */


	#########################
    # Settings General
	#########################
	
	function settings()
	{
        global $DSP, $IN, $DB, $LANG, $PREFS;

        $DSP->title  = $this->title_prefix.$LANG->line('settings_navbar_general');
        $DSP->crumb .= $DSP->anchor(BASE.AMP.'C=modules'.AMP.'M=mc_gallery', $LANG->line('mc_gallery_module_name'));
		$DSP->crumb	.= $DSP->build_crumb($LANG->line('settings_navbar_general'));


		# Send message header and message if any      
        if ($this->settings['general_install_date'] > 1) {
			# Show Main Navbar
			$DSP->body .= $this->_navbar_tabbed($this->navbar_main);

			# Header and Messages
			$DSP->body .= $this->_message_box($IN->GBL('msg'));
		}


		/** ---------------------------------
		/**  Settings Matrix
		/** ---------------------------------*/
		
        $form = array(
/*
        				'general'	=> array(
        									'general_name'		=> array('t', array('40', '150', '100%')),
        									'general_url'		=> array('t', array('40', '150', '100%')),
        									'general_weblog' 	=> array('t', array('40', '150', '100%')),
        									'general_trigger'	=> array('t', array('30', '70', '200px')),
											'general_enabled'	=> array('r', array('y' => 'yes', 'n' => 'no'))
        									),
*/
						'structure'	=> array(
        									'files_url'	=> array('t', array('40', '255', '100%')),
        									'files_path'	=> array('t', array('40', '255', '100%')),
        									'jquery_url'	=> array('t', array('40', '255', '100%')),
        									'system_url'	=> array('t', array('40', '255', '100%'))
//        									'directory'	=> array('r', array('entry_id' => 'entry_id', 'site_id' => 'site_id', 'weblog' => 'weblog'))
        									),

						'small_properties'	=> array(
        									'small_width'		=> array('t', array('40', '255', '100%')),
        									'small_height'		=> array('t', array('40', '255', '100%')),
        									'small_quality'		=> array('t', array('40', '255', '100%')),
        									'small_scale'		=> array('r', array('proportion' => 'proportion', 'crop' => 'crop'))
        									),

						'medium_properties'	=> array(
        									'medium_width'		=> array('t', array('40', '255', '100%')),
        									'medium_height'		=> array('t', array('40', '255', '100%')),
        									'medium_quality'	=> array('t', array('40', '255', '100%')),
        									'medium_scale'		=> array('r', array('proportion' => 'proportion', 'crop' => 'crop'))
        									),

						'large_properties'	=> array(
        									'large_width'		=> array('t', array('40', '255', '100%')),
        									'large_height'		=> array('t', array('40', '255', '100%')),
        									'large_quality'		=> array('t', array('40', '255', '100%')),
        									'large_scale'		=> array('r', array('proportion' => 'proportion', 'crop' => 'crop'))
        									)       
        			);
        
       
       $form_notes = array(
       						'general_url'		=> 'general_url_notes',
       						'general_trigger'	=> 'general_trigger_notes',
       						'general_enabled'	=> 'general_enabled_notes',
							'small_width' 	=> 'small_width_notes',
							'small_height' 	=> 'small_height_notes',
							'small_quality' => 'small_quality_notes',
							'small_scale' => 'small_scale_notes',
							'medium_width' 	=> 'medium_width_notes',
							'medium_height' 	=> 'medium_height_notes',
							'medium_quality' => 'medium_quality_notes',
							'medium_scale' => 'medium_scale_notes',
							'large_width' 	=> 'large_width_notes',
							'large_height' 	=> 'large_height_notes',
							'large_quality' => 'large_quality_notes',
							'large_scale' => 'large_scale_notes',
							'files_path' 	=> 'files_path_notes',
      						'files_url'	=> 'files_url_notes',
       						'jquery_url' 	=> 'jquery_url_notes',
       						'system_url' 	=> 'system_url_notes',
       						'directory'	=> 'directory_notes'
       					);
           
		# Generate Form
		$DSP->body .= $this->_generate_form($form,$form_notes);

	}
    /* END */


	#########################
	# Settings General Update
	#########################
	
	function settings_update()
	{
		global $IN, $LANG, $DSP, $DB, $FNS;
		
		/** ----------------------------------------
		/**  Error Trapping
		/** ----------------------------------------*/
		
		// Required Fields
		
		$required = array('files_url','files_path','jquery_url','system_url');
		
		$error = array();
		
		foreach ($required as $val)
		{
			if ($IN->GBL($val) == '')
			{
				$error[] = $LANG->line($val);
			}
		}

		if (count($error) > 0)
		{
			$msg = $DSP->qdiv('itemWrapper', $LANG->line('mc_gallery_empty_fields'));
			
			foreach ($error as $val)
			{
				$msg .= $DSP->qdiv('highlight_alt', $val);
			}
		
			return $DSP->error_message($msg);
		}


		/** ----------------------------------------
		/**  Insert/Update the DB
		/** ----------------------------------------*/

		foreach($_POST as $key => $val) {
			$results = $DB->query('SELECT * FROM exp_mc_gallery_settings WHERE name = "'.$key.'"');
			$field_value = array('name' => $key, 'value' => $val);

			if ($results->num_rows > 0)
			{
				$sql_query = $DB->update_string('exp_mc_gallery_settings', $field_value, 'name = "'.$key.'"');
#				echo 'update: '.$sql_query.'<BR>';;
				$DB->query($sql_query);
			}
			else
			{
				$sql_query = $DB->insert_string('exp_mc_gallery_settings', $field_value);
#				echo 'insert: '.$sql_query.'<BR>';;
				$DB->query($sql_query);
			}
		}

		# Set the install date
        if (empty($this->settings['general_install_date'])) {
			$field_value = array('name' => 'general_install_date', 'value' => date("Y-m-d H:i:s"));
			$sql_query = $DB->insert_string('exp_mc_gallery_settings', $field_value);
#			echo 'insert: '.$sql_query.'<BR>';;
			$DB->query($sql_query);
		}

		# Send message header and message if any      
		$FNS->redirect(BASE.AMP.'C=modules'.AMP.'M=mc_gallery'.AMP.'P=settings'.AMP.'msg=settings_general_updated');
		exit;        
	}
	/* END */



	#########################
	# Tabbed Navigation
	#########################

	function _navbar_tabbed($navbar_array)
    {
        global $IN, $DSP, $LANG;

# Insert hover javascript   
$output = <<<EOT
        
        <script type="text/javascript"> 
        <!--

		function styleswitch(link)
		{                 
			if (document.getElementById(link).className == 'altTabs')
			{
				document.getElementById(link).className = 'altTabsHover';
			}
		}
	
		function stylereset(link)
		{                 
			if (document.getElementById(link).className == 'altTabsHover')
			{
				document.getElementById(link).className = 'altTabs';
			}
		}
		
		-->
		</script>
		
EOT;

		# Equalize the text length.
		$temp = array();
		foreach ($navbar_array as $key => $val) {
			$temp[$key] = $LANG->line($key);
		}
		$temp = $DSP->equalize_text($temp);

		# Current Page
		$current_page = $IN->GBL('P');

		# Walkthrough Navigation Array
		$nav = array();
		foreach ($navbar_array as $page => $options)
		{

			if (is_array($options))
			{
				# Current processed page
				$processed_page = $options['name'];

				# Process URL
				$url = $options['link'];

				# Set highlight current page
				if (in_array($current_page,$options['page_children'])) {
					$div = 'altTabSelected';
				} else {
					$div = 'altTabs';
				}
			} else {

				# Link page
				$url = $val;
				$div = 'altTabs';
			}

			$link = '<div class="'.$div.'" id="'.$processed_page.'"  onClick="navjump(\''.$url.'\');" onmouseover="styleswitch(\''.$processed_page.'\');" onmouseout="stylereset(\''.$processed_page.'\');">'.$temp[$page].'</div>';
					
			$nav[] = array('text' => $DSP->anchor($url, $link));
		}

		# Create the table
		$output .= $DSP->table_open(array('width' => '100%;'));
		$output .= $DSP->table_row($nav);		
		$output .= $DSP->table_close();

		return $output;          
    }
    /* END */


	#########################
	# HORIZONTAL Navigation
	#########################

	function _navbar_horizontal($navbar_array)
    {
        global $IN, $DSP, $LANG;

		# Equalize the text length.
		$temp = array();
		foreach ($navbar_array as $key => $val) {
			$temp[$key] = $LANG->line($key);
		}
		$temp = $DSP->equalize_text($temp);

		# Current Page
		$current_page = $IN->GBL('P');

		# Walkthrough Navigation Array
		$i = 0;
		$nav = array();
		foreach ($navbar_array as $page => $options)
		{

			if (is_array($options))
			{
				# Current processed page
				$processed_page = $options['name'];

				# Process URL
				$url = $options['link'];

				# Set highlight current page
				if (in_array($current_page,$options['page_children'])) {
					$text = $DSP->qdiv('defaultBold', $temp[$page]);
				} else {
					$text = $DSP->anchor($url, $temp[$page]);
				}

				# Process Row Properties
				if (is_array($options['row_properties'])) {
					foreach ($options['row_properties'] as $key => $value) {
						$nav[$i] = array($key => $value);
					}
				}

			} else {

				# Link page
				$text = $DSP->anchor($val, $temp[$page]);
			}


			# Input or replace navbar properties
			$nav[$i]['text'] = $text;
			if (!$nav[$i]['class']) $nav[$i]['class'] = 'tableCellOne';
			if (!$nav[$i]['width']) $nav[$i]['width'] = '';
			if (!$nav[$i]['align']) $nav[$i]['align'] = 'center';
			$nav[$i]['id'] = $processed_page;

			# Increment counter
			$i++;
		}

		# Start Table
		$output = $DSP->table_open(array('class' => 'tableBorder', 'width'	=> '100%; margin-top:10px;'));
		
		# Table Header
		$output .= $DSP->table_row(array(
									array(
											'text'	=> $LANG->line('this_section'),
											'class'	=> 'tableHeading',
											'colspan' => 10
										)
									)
							);

		# Insert Navbar Links
		$output .= $DSP->table_row($nav);	
		
		# Close Table
		$output .= $DSP->table_close();

		return $output;          
    }
    /* END */

		
	#########################
	# GENERATE FORM
	#########################

	function _generate_form($form,$form_notes) {

        global $DSP, $IN, $DB, $LANG, $PREFS;

		$collapse = '';

		# Image code to names
        $img_prots = array('gd' => 'GD', 'gd2' => 'GD2', 'imagemagick' => 'Image Magick', 'netpbm' => 'NetPBM');

		$output = $DSP->form_open(array('action' => 'C=modules'.AMP.'M=mc_gallery'.AMP.'P=settings_update','style' => 'margin-top:10px;'));

        foreach ($form as $title => $menu)
        {	
			/** -----------------------------
			/**  Create Table Heading
			/** -----------------------------*/

			$output .= '<div id="'.$title.'_on" style="padding:0; margin: 0;">';
			$output .= $DSP->table_open(array('class' => 'tableBorder', 'width' => '100%'));
			$output .= $DSP->tr();
			
			$output .= "<td class='tableHeadingAlt' id='".$title."2' colspan='2'>";
			$output .= $collapse.NBS.NBS.$LANG->line('settings_'.$title).$DSP->td_c();     
			$output .= $DSP->tr_c();
			
									
				/** ---------------------------------
				/**  Settings Rows
				/** ---------------------------------*/
	
				$i = 0;
				foreach ($menu as $item => $val)
				{
					$form = '';
					
					/** -----------------------------
					/**  Text Input Fields
					/** -----------------------------*/
				
					if ($val['0'] == 't')
					{
						$form = $DSP->input_text($item, $this->settings[$item], $val['1']['0'], $val['1']['1'], 'input', $val['1']['2']);
					}
					
					/** -----------------------------
					/**  Radio buttons
					/** -----------------------------*/

					elseif ($val['0'] == 'r')
					{					
						foreach ($val['1'] as $k => $v)
						{						
							$form .= $LANG->line($v).$DSP->nbs();
							$form .= $DSP->input_radio($item, $k, ($k == $this->settings[$item]) ? 1 : '').$DSP->nbs(3);
						}					
					}

					/** -----------------------------
					/**  Drop-down menus
					/** -----------------------------*/

					elseif ($val['0'] == 'd')
					{
						$form .= $DSP->input_select_header($item);
				
						foreach ($val['1'] as $k => $v)
						{
							$form .= $DSP->input_select_option($k, (isset($img_prots[$k]) ? $img_prots[$k] : $LANG->line($v)), ($k == $this->settings[$item]) ? 1 : '');
						}
						
						$form .= $DSP->input_select_footer();
					}
					
					/** -----------------------------
					/**  Functions
					/** -----------------------------*/
				
					if ($val['0'] == 'f')
					{
						$form = $this->$val['1']($this->settings[$item]);
					}
				
					/** -----------------------------
					/**  Create Table Row
					/** -----------------------------*/
										
					$sub = ( ! isset($form_notes[$item])) ? '' : $DSP->qdiv('default', $LANG->line($form_notes[$item]));

					$class = ($i++ % 2) ? 'tableCellOne' : 'tableCellTwo';
					
					$output .= $DSP->table_row(array(
												array(
														'text'	=> $DSP->qdiv('defaultBold', $LANG->line($item)).$sub,
														'width'	=> '45%',
														'class'	=> $class
													),
												array(
														'text'	=> $form,
														'width'	=> '55%',
														'class'	=> $class
													)
											)
										);
				}
							
			$output .= $DSP->table_close();
			$output .= $DSP->div_c();
			$output .= $DSP->qdiv('defaultSmall', '');
        }

		$output .= $DSP->qdiv('itemWrapperTop', $DSP->input_submit($LANG->line('update')));
		return $output;
	}


	#########################
    # mc_gallery Header and Message Handler
	#########################
	
	function _message_box($message = '')
	{
		global $IN, $DSP, $LANG;

		$output = '';

		if (!empty($message)) {
			$output .= $DSP->qdiv('itemWrapperTop', $DSP->qdiv('successBox', $DSP->qdiv('success', $LANG->line($message))));
		}
        
		return $output;
	}
	/* END */
	

	#########################
	# Is GD Installed?
	#########################
	
	function gd_loaded()
	{
		if ( ! extension_loaded('gd'))
		{
			if ( ! @dl('gd.so'))
			{
				return FALSE;
			}
		}
		
		return TRUE;
	}
	/* END */



	#########################
	# Fetch GD Version
	#########################

	function gd_version()
	{
		if (function_exists('gd_info'))
		{
			$gd_version = @gd_info();
			$gd_version = preg_replace("/\D/", "", $gd_version['GD Version']);
			
			return $gd_version;
		}

		return FALSE;
	}
	/* END */


	#########################
    # Module installer
	#########################
/*
        $sql[] = "INSERT INTO exp_actions (action_id, class, method) VALUES ('', 'Mc_gallery', 'submit_post')";
        $sql[] = "INSERT INTO exp_actions (action_id, class, method) VALUES ('', 'Mc_gallery', 'delete_post')";
        $sql[] = "INSERT INTO exp_actions (action_id, class, method) VALUES ('', 'Mc_gallery', 'change_status')";
        $sql[] = "INSERT INTO exp_actions (action_id, class, method) VALUES ('', 'Mc_gallery', 'move_topic')";
        $sql[] = "INSERT INTO exp_actions (action_id, class, method) VALUES ('', 'Mc_gallery', 'delete_subscription')";
        $sql[] = "INSERT INTO exp_actions (action_id, class, method) VALUES ('', 'Mc_gallery', 'display_attachment')";
        $sql[] = "INSERT INTO exp_actions (action_id, class, method) VALUES ('', 'Mc_gallery', 'do_merge')";
        $sql[] = "INSERT INTO exp_actions (action_id, class, method) VALUES ('', 'Mc_gallery', 'do_split')";
        $sql[] = "INSERT INTO exp_actions (action_id, class, method) VALUES ('', 'Mc_gallery', 'set_theme')";
		$sql[] = "INSERT INTO exp_actions (action_id, class, method) VALUES ('', 'Mc_gallery', 'do_report')";
*/


    function mc_gallery_module_install()
    {
        global $DB, $OUT, $LANG, $FNS;
          
        if ( ! is_writable(CONFIG_FILE))
        {
			$LANG->fetch_language_file('mc_gallery_cp');
        
        	return $OUT->fatal_error($LANG->line('config_not_writable'));
        }
                
        $sql[] = "INSERT INTO exp_modules (module_id, module_name, module_version, has_cp_backend) VALUES ('', 'Mc_gallery', '$this->version', 'y')";

		$sql[] = "CREATE TABLE exp_mc_gallery_settings (
			setting_id smallint(4) unsigned NOT NULL auto_increment ,
			name varchar(255) NOT NULL,
			value varchar(255) NOT NULL,
			PRIMARY KEY (setting_id)
		)";
    
		$sql[] = "CREATE TABLE exp_mc_gallery ( 
			gallery_id int(8) unsigned NOT NULL auto_increment ,
			label varchar(255) NOT NULL ,
			name varchar(255) NOT NULL ,
			entry_id int(8) unsigned NOT NULL ,
			order_id tinyint(3) unsigned NOT NULL ,
			keywords varchar(255) NOT NULL ,
			description varchar(255) NOT NULL ,
			small_width int(8) unsigned NOT NULL ,
			small_height int(8) unsigned NOT NULL ,
			small_quality int(8) unsigned NOT NULL ,
			small_scale enum('Crop','Proportion') default 'Proportion',
			medium_width int(8) unsigned NOT NULL ,
			medium_height int(8) unsigned NOT NULL ,
			medium_quality int(8) unsigned NOT NULL ,
			medium_scale enum('Crop','Proportion') default 'Proportion',
			large_width int(8) unsigned NOT NULL ,
			large_height int(8) unsigned NOT NULL ,
			large_quality int(8) unsigned NOT NULL ,
			large_scale enum('Crop','Proportion') default 'Proportion',
			status enum('Active','Inactive') default 'Inactive',
			date_stamp datetime NOT NULL, 
			date_modified datetime NOT NULL, 
			PRIMARY KEY (gallery_id)
		)";

		$sql[] = "CREATE TABLE exp_mc_gallery_images ( 
			image_id int(8) unsigned NOT NULL auto_increment ,
			entry_id int(8) unsigned NOT NULL ,
			order_id tinyint(3) unsigned NOT NULL ,
			filename varchar(128) NOT NULL ,
			keywords varchar(255) NOT NULL ,
			caption varchar(255) NOT NULL ,
			description varchar(255) NOT NULL ,
			cover enum('Yes','No') default 'No',
			date_stamp datetime NOT NULL, 
			date_modified datetime NOT NULL, 
			size_800x600 INT,
			size_1024x768 INT,
			size_1280x1024 INT,
			size_1600x1280 INT,
			size_2650x1600 INT,
			PRIMARY KEY (image_id)
		)";


		foreach ($sql as $query)
        {
            $DB->query($query);
        }

        return TRUE;
    }
    /* END */
    
    
	#########################
    # Module de-installer
	#########################

    function mc_gallery_module_deinstall()
    {
        global $DB;    

        $query = $DB->query("SELECT module_id FROM exp_modules WHERE module_name = 'Mc_gallery'"); 
                
        $sql[] = "DELETE FROM exp_module_member_groups WHERE module_id = '".$query->row['module_id']."'";        
        $sql[] = "DELETE FROM exp_modules WHERE module_name = 'Mc_gallery'";
        $sql[] = "DELETE FROM exp_actions WHERE class = 'Mc_gallery'";
        $sql[] = "DELETE FROM exp_actions WHERE class = 'Mc_gallery_CP'";
        $sql[] = "DROP TABLE IF EXISTS exp_mc_gallery";
        $sql[] = "DROP TABLE IF EXISTS exp_mc_gallery_settings";
        $sql[] = "DROP TABLE IF EXISTS exp_mc_gallery_images";

        foreach ($sql as $query)
        {
            $DB->query($query);
        }
        
		/** ----------------------------------------
		/**  Remove a couple items to the config file
		/** ----------------------------------------*/
	  
		if ( ! class_exists('Admin'))
		{
			require PATH_CP.'cp.admin'.EXT;
		}
		
		Admin::append_config_file('', array('mc_gallery_is_installed', 'mc_gallery_trigger'));


        return TRUE;
    }
    /* END */



}
// END CLASS

/*
	#########################
    #  mc_gallery HTML Order
	#########################
	
	function ext_gallery_order() 
	{

		global $DB, $DSP, $IN;

		# Images Path
		$entry_id = $IN->GBL('entry_id');

		# Images Path
		$files_path = $this->settings['files_path'].$entry_id.'/';
		$files_url = $this->settings['files_url'].$entry_id.'/';

		# Other Settings
		$session_id = $SESS->userdata['session_id'];
		$base_url = '/system/index.php?S='.$session_id.'&C=modules&M=easy_gallery';
		$base_url = '/system/'.$this->base_url.'&P=ext_gallery_order';
		
		$r  = $this->_navbar_horizontal($this->navbar_gallery);

		$r .= '';
		$r .= "<table class='clusterBox' border='0' cellpadding='0' cellspacing='0' style='width:100%'>";	
		
		$r .= NL.'<tr>';
		$r .= NL.'<td class="tableHeadingAlt" valign="top" align="center" style="width:75px;">Image ID</td>';
		$r .= NL.'<td class="tableHeadingAlt" valign="top" align="center" style="width:75px;">Thumbnail</td>';
		$r .= NL.'<td class="tableHeadingAlt" valign="top" align="center">Image Caption</td>';
		$r .= NL.'<td class="tableHeadingAlt" valign="top" align="center" style="width:100px;">Order ID</td>';
		$r .= NL.'</tr>';
		
		$results = $DB->query('SELECT * FROM exp_easy_gallery_images WHERE entry_id="'.$entry_id.'" ORDER BY order_id ASC');
		foreach($results->result as $fields) {

			#list($filename,$file_extension) = explode('.',$fields['filename']);
			list($filename,$file_extension) = $this->_get_file_extension($fields['filename']);
			$filename_thumb = $filename.'_thumb.'.$file_extension;
			$filename_small = $filename.'_s.'.$file_extension;
			$filename_medium = $filename.'_m.'.$file_extension;
			$filename_large = $filename.'_l.'.$file_extension;

			if (empty($fields['description'])) $fields['description'] = '<em>No Caption Data</em>';

			$r .= NL.'<tr>';
			$r .= NL.'<td class="tableCellTwo" align="center">'.$fields['image_id'].'</td>';
			$r .= NL.'<td class="tableCellTwo" align="center"><img src="'.$files_url.$filename_thumb.'" width="50px" height="50px" alt="Thumbnail"></td>';
			$r .= NL.'<td class="tableCellTwo" align="center">'.$fields['description'].'</td>';
			$r .= NL.'<td class="tableCellTwo" align="center"><input type="text" name="order_id_'.$fields['image_id'].'" value="'.$fields['order_id'].'" style="width:25px;" onblur="mod_gallery_image_order('.$entry_id.','.$fields['image_id'].',this.value);"></td>';
			$r .= NL.'</tr>';
		}
		
		$r .= "</tr></table>";

		echo $r;
		exit;
	}


	#########################
    #  Easy_gallery HTML Upload
	#########################
	
	function ext_gallery_upload_html() 
	{

		$base_url = '/system/'.$this->base_url.'&P=ext_gallery_upload_files';
		
		$r  = $this->_navbar_horizontal($this->navbar_gallery);

$r .= <<<EOT

	<div id="swfu_container" style="display: none; margin: 5px;">
		<div style="float:left; margin: 10px;">
			<button id="btnBrowse" type="button" style="padding: 5px;" onclick="swfu.browse(); this.blur();"><img src="'.$this->settings['jquery_url'].'images/page_white_add.png" style="padding-right: 3px; vertical-align: bottom;">Select Images <span style="font-size: 7pt;">(2 MB Max)</span></button>
			<div id="divFileProgressContainer" style="height: 75px;"></div>
			<div id="degraded_container">
				SWFUpload has not loaded.  It may take a few moments.  SWFUpload requires JavaScript and Flash Player 8 or later.
			</div>
		</div>
		<div id="thumbnails"></div>
	</div>

EOT;
		echo $r;
		exit;
	}



*/
?>
