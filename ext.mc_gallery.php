<?php

if ( ! defined('EXT')) {
    exit('Invalid file request'); }

$field_translator = array();


class Mc_gallery
{
    var $settings        = array();
    
    var $name            = 'MindComet Gallery';
    var $version         = '1.0.0';
    var $description     = 'Adds a new "gallery" tab when adding or editing a new entry.';
    var $settings_exist  = 'y';
    var $docs_url        = 'http://expressionengine.com';

	var $session_id		= '';
	var $base_url		= '';

    // -------------------------------
    //   Constructor - Extensions use this for settings
    // -------------------------------
    
    function Mc_gallery($settings='')
    {
		global $DB, $PREFS;

		$this->settings = $settings;

		# Fetch the settings
		$query = $DB->query("SELECT * FROM exp_mc_gallery_settings");
		foreach($query->result as $row) {
			
			// Sean: This should allow us to override settings in conf.php
			if ($PREFS->ini('mc_gallery_'.$row['name']) != '') {
				$row['value'] = $PREFS->ini('mc_gallery_'.$row['name']);
			}		

			$this->settings[$row['name']] = $row['value'];
		}
	}
    // END


	// --------------------------------
	//  Settings
	// --------------------------------  

	function settings()
	{
		global $DB;

		$settings = array();

		$results = $DB->query('SELECT * FROM exp_weblogs');
		foreach($results->result as $fields) {
			$weblog_ids[$fields['weblog_id']] = $fields['blog_title'];
		}

		#$settings['extension_weblogs']    = '';
		$settings['extension_weblogs']   = array('ms', $weblog_ids, '1');
		
		// Complex:
		// [variable_name] => array(type, values, default value)
		// variable_name => short name for setting and used as the key for language file variable
		// type:  t - textarea, r - radio buttons, s - select, ms - multiselect, f - function calls
		// values:  can be array (r, s, ms), string (t), function name (f)
		// default:  name of array member, string, nothing
		//
		// Simple:
		// [variable_name] => 'Butter'
		// Text input, with 'Butter' as the default.
		
		return $settings;
	}
	// END


	// --------------------------------
	//  Activate Extension
	// --------------------------------

	function activate_extension()
	{
		global $DB;
		
		$DB->query($DB->insert_string('exp_extensions',
									  array(
											'extension_id' => '',
											'class'        => "Mc_gallery",
											'method'       => "publish_form_headers",
											'hook'         => "publish_form_headers",
											'settings'     => "",
											'priority'     => 9,
											'version'      => $this->version,
											'enabled'      => "y"
										  )
									 )
				  );
		$DB->query($DB->insert_string('exp_extensions',
									  array(
											'extension_id' => '',
											'class'        => "Mc_gallery",
											'method'       => "publish_form_new_tabs",
											'hook'         => "publish_form_new_tabs",
											'settings'     => "",
											'priority'     => 9,
											'version'      => $this->version,
											'enabled'      => "y"
										  )
									 )
				  );
		$DB->query($DB->insert_string('exp_extensions',
									  array(
											'extension_id' => '',
											'class'        => "Mc_gallery",
											'method'       => "publish_form_new_tabs_block",
											'hook'         => "publish_form_new_tabs_block",
											'settings'     => "",
											'priority'     => 9,
											'version'      => $this->version,
											'enabled'      => "y"
										  )
									 )
				  );
				
		//DY: Add publish_form_start hook to rewrite publish page	
		$DB->query($DB->insert_string('exp_extensions',
									  array(
											'extension_id' => '',
											'class'        => "Mc_gallery",
											'method'       => "publish_form_start",
											'hook'         => "publish_form_start",
											'settings'     => "",
											'priority'     => 9,
											'version'      => $this->version,
											'enabled'      => "y"
										  )
									 )
				  );
				
		//DY: Add submit_new_entry_redirect hook to redirect to gallery edit
		$DB->query($DB->insert_string('exp_extensions',
									  array(
											'extension_id' => '',
											'class'        => "Mc_gallery",
											'method'       => "submit_new_entry_redirect",
											'hook'         => "submit_new_entry_redirect",
											'settings'     => "",
											'priority'     => 9,
											'version'      => $this->version,
											'enabled'      => "y"
											  )
										 )
					  );
				
				
				

	}
	// END

	//DY: Add this extension to prevent adding images to undefined post
	function publish_form_start($which, $submission_error, $entry_id)
	{
		global $IN, $EXT, $DSP, $DB;
		$weblog_id = $IN->GBL('weblog_id', 'GET');
		$entry_id = $IN->GBL('entry_id', 'GET');
		$sid = $IN->GBL('S', 'GET');
		$title = $IN->GBL('title', 'POST');
		$url_title = $IN->GBL('url_title', 'POST');
		
		// Check to see if the gallery should attach to this weblog
		if(in_array($weblog_id, $this->settings['extension_weblogs']))
		{	
			// A valid entry_id is required to properly upload photos
			if(!$entry_id)
			{
				//display a form for user to save this entry to the DB
				$DSP->body .= <<<EOT
						<script language="JavaScript">
						/** ------------------------------------
						/**  Live URL Title Function
						/** -------------------------------------*/
					        function liveUrlTitle()
					        {
					        	var defaultTitle = '';
								var NewText = document.getElementById("title").value;

								if (defaultTitle != '')
								{
									if (NewText.substr(0, defaultTitle.length) == defaultTitle)
									{
										NewText = NewText.substr(defaultTitle.length)
									}	
								}

								NewText = NewText.toLowerCase();
								var separator = "_";

								if (separator != "_")
								{
									NewText = NewText.replace(/\_/g, separator);
								}
								else
								{
									NewText = NewText.replace(/\-/g, separator);
								}

								// Foreign Character Attempt

								var NewTextTemp = '';
								for(var pos=0; pos<NewText.length; pos++)
								{
									var c = NewText.charCodeAt(pos);

									if (c >= 32 && c < 128)
									{
										NewTextTemp += NewText.charAt(pos);
									}
									else
									{
										if (c == '223') {NewTextTemp += 'ss'; continue;}
									if (c == '224') {NewTextTemp += 'a'; continue;}
									if (c == '225') {NewTextTemp += 'a'; continue;}
									if (c == '226') {NewTextTemp += 'a'; continue;}
									if (c == '229') {NewTextTemp += 'a'; continue;}
									if (c == '227') {NewTextTemp += 'ae'; continue;}
									if (c == '230') {NewTextTemp += 'ae'; continue;}
									if (c == '228') {NewTextTemp += 'ae'; continue;}
									if (c == '231') {NewTextTemp += 'c'; continue;}
									if (c == '232') {NewTextTemp += 'e'; continue;}
									if (c == '233') {NewTextTemp += 'e'; continue;}
									if (c == '234') {NewTextTemp += 'e'; continue;}
									if (c == '235') {NewTextTemp += 'e'; continue;}
									if (c == '236') {NewTextTemp += 'i'; continue;}
									if (c == '237') {NewTextTemp += 'i'; continue;}
									if (c == '238') {NewTextTemp += 'i'; continue;}
									if (c == '239') {NewTextTemp += 'i'; continue;}
									if (c == '241') {NewTextTemp += 'n'; continue;}
									if (c == '242') {NewTextTemp += 'o'; continue;}
									if (c == '243') {NewTextTemp += 'o'; continue;}
									if (c == '244') {NewTextTemp += 'o'; continue;}
									if (c == '245') {NewTextTemp += 'o'; continue;}
									if (c == '246') {NewTextTemp += 'oe'; continue;}
									if (c == '249') {NewTextTemp += 'u'; continue;}
									if (c == '250') {NewTextTemp += 'u'; continue;}
									if (c == '251') {NewTextTemp += 'u'; continue;}
									if (c == '252') {NewTextTemp += 'ue'; continue;}
									if (c == '255') {NewTextTemp += 'y'; continue;}
									if (c == '257') {NewTextTemp += 'aa'; continue;}
									if (c == '269') {NewTextTemp += 'ch'; continue;}
									if (c == '275') {NewTextTemp += 'ee'; continue;}
									if (c == '291') {NewTextTemp += 'gj'; continue;}
									if (c == '299') {NewTextTemp += 'ii'; continue;}
									if (c == '311') {NewTextTemp += 'kj'; continue;}
									if (c == '316') {NewTextTemp += 'lj'; continue;}
									if (c == '326') {NewTextTemp += 'nj'; continue;}
									if (c == '353') {NewTextTemp += 'sh'; continue;}
									if (c == '363') {NewTextTemp += 'uu'; continue;}
									if (c == '382') {NewTextTemp += 'zh'; continue;}
									if (c == '256') {NewTextTemp += 'aa'; continue;}
									if (c == '268') {NewTextTemp += 'ch'; continue;}
									if (c == '274') {NewTextTemp += 'ee'; continue;}
									if (c == '290') {NewTextTemp += 'gj'; continue;}
									if (c == '298') {NewTextTemp += 'ii'; continue;}
									if (c == '310') {NewTextTemp += 'kj'; continue;}
									if (c == '315') {NewTextTemp += 'lj'; continue;}
									if (c == '325') {NewTextTemp += 'nj'; continue;}
									if (c == '352') {NewTextTemp += 'sh'; continue;}
									if (c == '362') {NewTextTemp += 'uu'; continue;}
									if (c == '381') {NewTextTemp += 'zh'; continue;}

									}
								}

								NewText = NewTextTemp;

								NewText = NewText.replace('/<(.*?)>/g', '');
								NewText = NewText.replace('/\&#\d+\;/g', '');
								NewText = NewText.replace('/\&\#\d+?\;/g', '');
								NewText = NewText.replace('/\&\S+?\;/g','');
								NewText = NewText.replace(/['\"\?\.\!*$\#@%;:,=\(\)\[\]]/g,'');
								NewText = NewText.replace(/\s+/g, separator);
								NewText = NewText.replace(/\//g, separator);
								NewText = NewText.replace(/[^a-z0-9-_]/g,'');
								NewText = NewText.replace(/\+/g, separator);
								NewText = NewText.replace(/[-_]+/g, separator);
								NewText = NewText.replace(/\&/g,'');
								NewText = NewText.replace(/-$/g,'');
								NewText = NewText.replace(/_$/g,'');
								NewText = NewText.replace(/^_/g,'');
								NewText = NewText.replace(/^-/g,'');

								if (document.getElementById("url_title"))
								{
									document.getElementById("url_title").value = "" + NewText + "-gallery";			
								}
								else
								{
									document.forms['entryform'].elements['url_title'].value = "" + NewText + "-gallery"; 
								}		
							}
							</script>


EOT;
				$DSP->body .= $DSP->div('mc_gallery_message');
				$DSP->body .= "<strong>Please enter a title for this gallery:</strong><p>";
				$DSP->body .= "<form id=entryform name=entryform method=post action=index.php?S=$sid&C=publish&M=new_entry>";
				$DSP->body .= "<table><tr><td>Title<td><input size=40 maxlength=64 type=text name=title id=title onkeyup=\"liveUrlTitle();\"><br />";
				$DSP->body .= "<tr><td>URL<td><input size=40 maxlength=64 type=text name=url_title id=url_title><br />";
				
				$DSP->body .= "<input type=hidden name=entry_date value=\"" . date("y-m-d H:i") . "\">";
				$DSP->body .= "<input type=hidden name=comment_expiration_date value=>";
				$DSP->body .= "<input type=hidden name=weblog_id value=$weblog_id>";
				$DSP->body .= "<input type=hidden name=return_url value=index.php?C=edit&M=edit_entry>";
				$DSP->body .= "<tr><td><td><input type=submit value=Next></table></form>";
				$DSP->body .= $DSP->div_c();
				$EXT->end_script = TRUE;
			}
			else
			{
				$DSP->div('mc_gallery_instructions');
				$DSP->body .= "Click the <strong>Gallery</strong> tab to manage photos; tags and keywords may be entered in the text field below.";
				$DSP->div_c();
			}
		}
		else
		{
			//do not attach gallery to this weblog
			return;
		}
		
	}

	//DY: Add this hook to redirect user back to edit the gallery after saving it
	function submit_new_entry_redirect($entry_id, $data, $cp_call)
	{			
		global $IN;
		$weblog_id = $IN->GBL('weblog_id', 'POST');
		$submit = $IN->GBL('submit', 'POST');

		$continue_editing = "index.php?C=edit&M=edit_entry&entry_id=$entry_id";
		$entry_updated = "index.php?C=edit&M=view_entry&weblog_id=$weblog_id&entry_id=$entry_id&U=update";
				
		//if the gallery is attached to this weblog AND is a first entry,
		// redirect to the edit page
		if(in_array($weblog_id, $this->settings['extension_weblogs']) && $submit != "Update")
			return $continue_editing;
		else
			return $entry_updated;
	}

	//DY: Assign entry_id to fix "undefined" entry_id when first editing a post
	function publish_form_headers($which, $submission_error, $entry_id, $weblog_id, $hidden) {

		global $SESS, $EXT;

		global $IN;
		if(!$entry_id)
			$entry_id = $IN->GBL('entry_id', 'GET');
		
		if(!in_array($weblog_id, $this->settings['extension_weblogs'])){

			if($EXT->last_call){
				return $headers . $EXT->last_call;
			} else {
				return;
			}
		}

		# Other Settings
		$session_id = $SESS->userdata['session_id'];
		$base_url = $this->settings['system_url'].'index.php?S='.$session_id.'&C=modules&M=mc_gallery&P=ext_gallery_upload_files&weblog_id='.$weblog_id.'&entry_id='.$entry_id;
		$base_url = $this->settings['system_url'].'index.php?S='.$session_id.'&C=modules&M=mc_gallery';
		$jquery_url = $this->settings['jquery_url'];

		# Javascript Calls
$headers = <<<EOT

		<style type="text/css" src="{$jquery_url}SWFUpload.css">	</style>  
		<style type="text/css">
			.sortable_item
			{
				float: left;
				width: 215px;
			}		
			.sort_helper
			{
				background-color: #f00;
				float: left;
			}
			.sortable_active
			{
			}
			.sortable_hover
			{
			}
		</style>

		<script language="javascript" src="{$jquery_url}jquery.js"></script>
		<script language="javascript" src="{$jquery_url}SWFUpload.js"></script>
		<script language="javascript" src="{$jquery_url}handlers.js"></script>
		<script language="javascript" src="{$jquery_url}interface.js"></script>

		<script language="javascript">
		var swfu;

		function initUpload() {
			swfu = new SWFUpload({

				// Backend Settings
				upload_target_url: "{$jquery_url}mc_gallery_upload.php",	// Relative to the SWF file
				post_params: {"S": "{$session_id}", "M": "module", "P": "ext_gallery_upload_files", "weblog_id": "$weblog_id", "entry_id": "$entry_id"},

				// File Upload Settings
				file_size_limit : "2048",	// 2MB
				file_types : "*.jpg;*.gif",
				file_types_description : "JPG Images",
				file_upload_limit : "0",
				begin_upload_on_queue : true,
				use_server_data_event : true,
				validate_files : false,

				// Event Handler Settings
				file_queued_handler : fileQueued,
				file_progress_handler : fileProgress,
				file_cancelled_handler : fileCancelled,
				file_complete_handler : fileComplete,
				queue_complete_handler : queueComplete,
				//queue_stopped_handler : queueStopped,
				//dialog_cancelled_handler : fileDialogCancelled,
				error_handler : uploadError,

				// Flash Settings
				flash_url : "{$jquery_url}SWFUpload.swf",	// Relative to this file

				// UI Settings
				ui_container_id : "swfu_container",
				degraded_container_id : "degraded_container",

				// Debug Settings
				debug: true
			});
			swfu.addSetting("upload_target", "divFileProgressContainer");
		}
//		document.write("{$base_url}");
		</script>






		<script type="text/javascript">
        <!--

		// prepare the form when the DOM is ready 
		$(document).ready(function() { 

			// GET Request
			$.ajax({
				type: "GET",
				url: "{$base_url}",
				data: "P=ext_gallery_images&weblog_id={$weblog_id}&entry_id={$entry_id}",
				dataType: "html",
				success: 
				   function(data){
					 $('#extension_gallery').html(data);
				   }
			})
		}); 

		// AJAX HTML UPLOAD
		function ext_gallery_settings() {
			// GET Request

			$.ajax({
				type: "GET",
				url: "{$base_url}",
				data: "P=ext_gallery_settings&weblog_id={$weblog_id}&entry_id={$entry_id}",
				dataType: "html",
				success: 
				   function(data){
					 $('#extension_gallery').html(data);
				   }
			})
		}  // end ajax_test

		// AJAX HTML UPLOAD
		function ext_gallery_upload_html() {
			// GET Request

			$.ajax({
				type: "GET",
				url: "{$base_url}",
				data: "P=ext_gallery_upload_html&weblog_id={$weblog_id}&entry_id={$entry_id}",
				dataType: "html",
				success: 
				   function(data){
					 $('#extension_gallery').html(data);
					 initUpload();
				   }
			})
		}  // end ajax_test


		// AJAX FLASH UPLOAD
		function ext_gallery_upload_flash() {
			// GET Request

			$.ajax({
				type: "GET",
				url: "{$base_url}",
				data: "P=ext_gallery_upload_flash&weblog_id={$weblog_id}&entry_id={$entry_id}",
				dataType: "html",
				success: 
				   function(data){
					 $('#extension_gallery').html(data);
					 initUpload();
				   }
			})
		}  // end ajax_test

		// AJAX TEST
		function ext_gallery_images(entry_id) {
			// GET Request

			$.ajax({
				type: "GET",
				url: "{$base_url}",
				data: "P=ext_gallery_images&weblog_id={$weblog_id}&entry_id="+entry_id,
				dataType: "html",
				success: 
				   function(data){
					 $('#extension_gallery').html(data);
				   }
			})
		}  // end ajax_test

		// AJAX TEST
		function ext_gallery_order() {
			// GET Request

			$.ajax({
				type: "GET",
				url: "{$base_url}",
				data: "P=ext_gallery_order&weblog_id={$weblog_id}&entry_id={$entry_id}",
				dataType: "html",
				success: 
				   function(data){
					 $('#extension_gallery').html(data);
				   }
			})
		}  // end ajax_test

/*
		$('#items').Sortable(
			{
				accept: 'sortable_item',
				tolerance: 'pointer',
				floats:	true,
				opacity: 0.5,
				fit: true,

				helperclass: 'sort_helper',
				activeclass : 'sortable_active',
				hoverclass : 'sortable_hover',
			}
		);
*/
		function mod_gallery_settings(entry_id) {

			var answer = confirm('Are you sure you want to update entry_id "'+entry_id+'" settings?');
			if (answer){
					var status = $("input[@name=status][@checked]").val();
					var small_width = $("input[@name=small_width]").val();
					var small_height = $("input[@name=small_height]").val();
					var small_quality = $("input[@name=small_quality]").val();
					var small_scale = $("input[@name=small_scale][@checked]").val();
					var medium_width = $("input[@name=medium_width]").val();
					var medium_height = $("input[@name=medium_height]").val();
					var medium_quality = $("input[@name=medium_quality]").val();
					var medium_scale = $("input[@name=medium_scale][@checked]").val();
					var large_width = $("input[@name=large_width]").val();
					var large_height = $("input[@name=large_height]").val();
					var large_quality = $("input[@name=large_quality]").val();
					var large_scale = $("input[@name=large_scale][@checked]").val();

//					alert("P=ext_gallery_images_action&A=entry_recreate&which="+which+"&image_width="+image_width+"&image_height="+image_height+"&image_quality="+image_quality+"&image_scale="+image_scale+"&entry_id="+entry_id);
//					alert("&status="+status+"&small_width="+small_width+"&small_height="+small_height+"&small_quality="+small_quality+"&small_scale="+small_scale+"&medium_width="+medium_width+"&medium_height="+medium_height+"&medium_quality="+medium_quality+"&medium_scale="+medium_scale+"&large_width="+large_width+"&large_height="+large_height+"&large_quality="+large_quality+"&large_scale="+large_scale+"");


				$.ajax({
					type: "GET",
					url: "{$base_url}",
					data: "P=ext_gallery_settings_action&status="+status+"&small_width="+small_width+"&small_height="+small_height+"&small_quality="+small_quality+"&small_scale="+small_scale+"&medium_width="+medium_width+"&medium_height="+medium_height+"&medium_quality="+medium_quality+"&medium_scale="+medium_scale+"&large_width="+large_width+"&large_height="+large_height+"&large_quality="+large_quality+"&large_scale="+large_scale+"&entry_id="+entry_id,
					dataType: "script",
					success: 
					   function(data){
						ext_gallery_upload_flash();
					   }
/*
					   function(data){
						 $('#extension_gallery').html(data);
					   }
*/
				})
			}
		}

		function mod_gallery_image_order(entry_id,image_id,order_id) {

//			alert('entry_id: '+entry_id+' image_id: '+image_id+' order_id: '+order_id);

			$.ajax({
				type: "GET",
				url: "{$base_url}",
				data: "P=ext_gallery_images_action&A=image_order&entry_id="+entry_id+"&image_id="+image_id+"&order_id="+order_id,
				dataType: "script",
				success: 
					function(msg){
						ext_gallery_images(entry_id);
					}
			})
		}

		function mod_gallery_image_caption(entry_id,image_id,caption) {
//			var caption = prompt("Enter your caption", "")

			$.ajax({
				type: "GET",
				url: "{$base_url}",
				data: "P=ext_gallery_images_action&A=image_caption&entry_id="+entry_id+"&image_id="+image_id+"&caption="+caption,
				dataType: "script",
				success: 
					function(msg){
						//ext_gallery_images(entry_id);
	//				 $('#extension_gallery').html(msg);
					}
			})
		}
		
		function mod_gallery_image_description(entry_id,image_id,caption) {
//			var caption = prompt("Enter your description", "")

			$.ajax({
				type: "GET",
				url: "{$base_url}",
				data: "P=ext_gallery_images_action&A=image_description&entry_id="+entry_id+"&image_id="+image_id+"&description="+caption,
				dataType: "script",
				success: 
					function(msg){
						//ext_gallery_images(entry_id);
			//		 $('#extension_gallery').html(msg);
					}
			})
		}

		function mod_gallery_image_delete(entry_id,image_id) {
			$.ajax({
				type: "GET",
				url: "{$base_url}",
				data: "P=ext_gallery_images_action&A=image_delete&entry_id="+entry_id+"&image_id="+image_id,
				dataType: "script",
				success: 
					function(msg){
						ext_gallery_images(entry_id);
					}
			})
		}

		function mod_gallery_image_cover(entry_id,image_id) {
			$.ajax({
				type: "GET",
				url: "{$base_url}",
				data: "P=ext_gallery_images_action&A=image_cover&entry_id="+entry_id+"&image_id="+image_id,
				dataType: "script",
				success: 
					function(msg){
						ext_gallery_images(entry_id);
					}
			})
		}

		function mod_gallery_entry_recreate(which,entry_id) {

			var answer = confirm('Are you sure you want to reacreate the '+which+' images?');
			if (answer){
				if (which == 'small') {
					var image_width = $("input[@name=small_width]").val();
					var image_height = $("input[@name=small_height]").val();
					var image_quality = $("input[@name=small_quality]").val();
					var image_scale = $("input[@name=small_scale][@checked]").val();
				} else if (which == 'medium') {
					var image_width = $("input[@name=medium_width]").val();
					var image_height = $("input[@name=medium_height]").val();
					var image_quality = $("input[@name=medium_quality]").val();
					var image_scale = $("input[@name=medium_scale][@checked]").val();
				} else if (which == 'large') {
					var image_width = $("input[@name=large_width]").val();
					var image_height = $("input[@name=large_height]").val();
					var image_quality = $("input[@name=large_quality]").val();
					var image_scale = $("input[@name=large_scale][@checked]").val();
				}
//					alert("P=ext_gallery_images_action&A=entry_recreate&which="+which+"&image_width="+image_width+"&image_height="+image_height+"&image_quality="+image_quality+"&image_scale="+image_scale+"&entry_id="+entry_id);
//					alert("which="+which+"&image_width="+image_width+"&image_height="+image_height+"&image_quality="+image_quality+"&image_scale="+image_scale);
//					alert("&image_scale="+image_scale);


//				$("body").css('cursor','wait');
				$.ajax({
					type: "GET",
					url: "{$base_url}",
					data: "P=ext_gallery_images_action&A=entry_recreate&entry_id="+entry_id+"&which="+which+"&image_width="+image_width+"&image_height="+image_height+"&image_quality="+image_quality+"&image_scale="+image_scale,
					dataType: "script",
					success: 
						function(msg){
							ext_gallery_images(entry_id);
//							$("body").css('cursor','default');
						}
				})
			}
		}

		function mod_gallery_update_settings(entry_id) {

			var answer = confirm('Are you sure you want to update entry_id "'+entry_id+'" settings?');
			if (answer){
					var small_width = $("input[@name=small_width]").val();
					var small_height = $("input[@name=small_height]").val();
					var small_quality = $("input[@name=small_quality]").val();
					var small_scale = $("input[@name=small_scale][@checked]").val();
					var medium_width = $("input[@name=medium_width]").val();
					var medium_height = $("input[@name=medium_height]").val();
					var medium_quality = $("input[@name=medium_quality]").val();
					var medium_scale = $("input[@name=medium_scale][@checked]").val();
					var large_width = $("input[@name=large_width]").val();
					var large_height = $("input[@name=large_height]").val();
					var large_quality = $("input[@name=large_quality]").val();
					var large_scale = $("input[@name=large_scale][@checked]").val();

//					alert("P=ext_gallery_images_action&A=entry_recreate&which="+which+"&image_width="+image_width+"&image_height="+image_height+"&image_quality="+image_quality+"&image_scale="+image_scale+"&entry_id="+entry_id);
					alert("&small_width="+small_width+"&small_height="+small_height+"&small_quality="+small_quality+"&small_scale="+small_scale+"&medium_width="+medium_width+"&medium_height="+medium_height+"&medium_quality="+medium_quality+"&medium_scale="+medium_scale+"&large_width="+large_width+"&large_height="+large_height+"&large_quality="+large_quality+"&large_scale="+large_scale+"");


//				$("body").css('cursor','wait');
				$.ajax({
					type: "GET",
					url: "{$base_url}",
					data: "P=update_settings&entry_id="+entry_id+"&small_width="+small_width+"&small_height="+small_height+"&small_quality="+small_quality+"&small_scale="+small_scale+"&medium_width="+medium_width+"&medium_height="+medium_height+"&medium_quality="+medium_quality+"&medium_scale="+medium_scale+"&large_width="+large_width+"&large_height="+large_height+"&large_quality="+large_quality+"&large_scale="+large_scale,
					dataType: "script",
					success: 
					   function(data){
						ext_gallery_upload_flash();
//						 $("body").css('cursor','default');
						}
				})
			}
		}


		function mod_gallery_entry_delete(which,entry_id) {

//			document.write("http://localhost'.$this->settings['system_url'].'index.php?S=20880686c2a9b4c95fd8b5b412cb6920d9644aff&C=modules&M=mc_gallery&P=ext_gallery_images_action&A=entry_delete&entry_id="+entry_id);

			var answer = confirm('Are you sure you want to delete '+which+' images?');

			if (answer){
				$.ajax({
					type: "GET",
					url: "{$base_url}",
					data: "P=ext_gallery_images_action&A=entry_delete&entry_id="+entry_id+"&which="+which,
					dataType: "script",
					success: 
						function(msg){
							ext_gallery_images(entry_id);
						}
				})
			}
		}

		-->
		</script>

EOT;

		if($EXT->last_call){
//		die('now: '.$headers);
			return $headers . $EXT->last_call;
		} else {
			return $headers;
		}

	}

	function publish_form_new_tabs($publish_tabs, $weblog_id, $entry_id, $hidden) {

		global $EXT;

		if(!in_array($weblog_id, $this->settings['extension_weblogs'])){
			if($EXT->last_call){
			  return $EXT->last_call;
			} else {
			  return $publish_tabs;      
			}
		}

		if($EXT->last_call){
		  $EXT->last_call['gallery'] = 'Gallery';
		  return $EXT->last_call;
		} else {
		  $publish_tabs['gallery'] = 'Gallery';
		  return $publish_tabs;      
		}

	}

	function publish_form_new_tabs_block($weblog_id) {

		global $DSP, $LANG, $EXT;

		$r = '';

		$menu_status = 'y';
		$menu_author = 'y';
		$menu_options = 'y';
		$menu_weblog = 'y';

        /** ---------------------------------------------
        /**  OPTIONS BLOCK
        /** ---------------------------------------------*/
        
		$r .= '<div id="blockgallery" style="display: none; padding:0; margin:0;">';
		$r .= NL.'<div class="publishTabWrapper">';	
		$r .= NL.'<div class="publishBox">';

$r .= <<<EOT

<div id="extension_gallery" style="HEIGHT:360px; WIDTH:100%; OVERFLOW:auto; padding:5px;">
</div>


EOT;

		$r .= NL.'<div class="publishInnerPad">';
/*
		$r .= '<input id="test1" type="button" name="test1" value="test1">';
		$r .= '<input id="test2" type="button" name="test2" value="test2">';
*/

		$r .= $DSP->div_c();
		$r .= $DSP->div_c();  
		$r .= $DSP->div_c();  
		$r .= $DSP->div_c(); 

		if($EXT->last_call){
			return $r . $EXT->last_call;
		} else {
			return $r;
		}

		return $r;
	}


	// --------------------------------
	//  Update Extension
	// --------------------------------  

	function update_extension($current='')
	{
		global $DB;
		
		if ($current == '' OR $current == $this->version)
		{
			return FALSE;
		}
		
		if ($current < '1.0.1')
		{
			// Update to next version 1.0.1
		}
		
		if ($current < '1.0.2')
		{
			// Update to next version 1.0.2
		}
		
		$DB->query("UPDATE exp_extensions 
					SET version = '".$DB->escape_str($this->version)."' 
					WHERE class = 'Example_extension'");
	}
	// END



	// --------------------------------
	//  Disable Extension
	// --------------------------------

	function disable_extension()
	{
		global $DB;
		
		$DB->query("DELETE FROM exp_extensions WHERE class = 'Mc_gallery'");
	}
	// END
}
// END CLASS

?>
