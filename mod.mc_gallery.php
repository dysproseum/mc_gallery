<?php

/*
=====================================================
 ExpressionEngine - MindComet Gallery Module
-----------------------------------------------------
 http://mindcomet.com/
=====================================================
 File: mod.mc_gallery.php
-----------------------------------------------------
 Purpose: Gallery embedded weblog
=====================================================

*/


error_reporting(E_ERROR | E_WARNING | E_PARSE);


if ( ! defined('EXT'))
{
    exit('Invalid file request');
}


class Mc_gallery {

    var $return_data		= '';
	var $paginate_data		= '';
    var $debug				= FALSE;
    var $possible_post;
    var $post				= array();

	var $base_url			 = '';
	var $base_path			 = '';
	var $settings			= array();
    
    /** ----------------------------------------
    /**  Constructor
    /** ----------------------------------------*/

    function Mc_gallery()
    {
	
		global $DB, $SESS, $TMPL, $PREFS;

		# Fetch the settings
		$query = $DB->query("SELECT * FROM exp_mc_gallery_settings");
		if($query->num_rows)
		foreach($query->result as $row) {
			
			// Sean: This should allow us to override settings in conf.php
			if ($PREFS->ini('mc_gallery_'.$row['name']) != '') {
				$row['value'] = $PREFS->ini('mc_gallery_'.$row['name']);
			}
			
			$this->settings[$row['name']] = $row['value'];
		}

		$this->base_url			= $this->settings['files_url'];
		$this->base_path		= $this->settings['files_path'];;
 
	}
    /* END */

	function image()
	{
		global $TMPL, $DB;
		$tagdata_output = '';
		
		$image_id = $TMPL->fetch_param('image_id');
		$entry_id = $TMPL->fetch_param('entry_id');
		
		$this->base_url = $this->base_url.$entry_id.'/';
		$this->base_path = $this->base_path.$entry_id.'/';
		
		$sql_query = "SELECT * FROM exp_mc_gallery_images WHERE image_id=$image_id LIMIT 1";
		
		$results = $DB->query($sql_query);
		
		$result = $results->row;
			
		# Content of the Tag
		$tagdata = $TMPL->tagdata;

		# Create the image urls
		list($filename,$extension) = explode('.',$result['filename']);

		if (file_exists($this->base_path.$filename.'_s.'.$extension)) {
			$variables['url_image_small'] = $this->base_url.$filename.'_s.'.$extension;
		} else {
			$variables['url_image_small'] = '';
		}
		if (file_exists($this->base_path.$filename.'.m.'.$extension)) {
			$variables['url_image_medium'] = $this->base_url.$filename.'.m.'.$extension;
		} else {
			$variables['url_image_medium'] = '';
		}
		if (file_exists($this->base_path.$filename.'_l.'.$extension)) {
			$variables['url_image_large'] = $this->base_url.$filename.'_l.'.$extension;
		} else {
			$variables['url_image_large'] = '';
		}
			
		//DY: Added reference to thumbnail image
		if (file_exists($this->base_path.$filename.'.s.'.$extension)) {
			$variables['url_image_thumb'] = $this->base_url.$filename.'.s.'.$extension;
		} else {
			$variables['url_image_thumb'] = '';
		}
/*
			if (file_exists($this->base_path.'thumbnails/'.$filename.'.'.$extension)) {
				$variables['url_image_thumbnail'] = $this->base_url.'thumbnails/'.$filename.'.'.$extension;
			} else {
				$variables['url_image_thumbnail'] = '';
			}		
*/

		# Assign variables to the template
		foreach($variables as $key => $val)
		{
			$tagdata = $TMPL->swap_var_single($key, $val, $tagdata);
		}
		
		//echo print_r($tagdata);
			
		//$this->return_data .= $tagdata; //_output; //.$this->paginate_data;
		
	}
	
	function images()
	{
		global $DB, $SESS, $TMPL;

		$this->paginate_data = '';
		$tagdata_output = '';

		# Fetch parameters
    	$entry_id = $TMPL->fetch_param('entry_id');
    	$cover = $TMPL->fetch_param('cover');
		$limit = $TMPL->fetch_param('limit');
    	$page = $TMPL->fetch_param('page');

		$entry_id = $DB->escape_str($entry_id);

    	$paginate = strtolower($TMPL->fetch_param('paginate'));
//		$offset = ( ! $TMPL->fetch_param('offset') OR ! is_numeric($TMPL->fetch_param('offset'))) ? '0' : $TMPL->fetch_param('offset');

		# Base URL
		$this->base_url = $this->base_url.$entry_id.'/';
		$this->base_path = $this->base_path.$entry_id.'/';

		# If Paginate
		if ($paginate == 'yes')
		{
			# Extract Page
			if (substr($page,0,1) == 'P') {
				$page = substr($page,1);
			}

			# Defualt Page
			if (empty($page)) {
				$page = 1;
			}

			# Calculate Offset
			if (!empty($limit)) {
				$offset = ($page - 1) * $limit;
			} else {
				$offset = '0';
				$limit = '1';
			}
		}

		# Prepare SQL Statement
		if (!empty($cover)) $sql_cover = ' AND cover="Yes"';
			else $sql_cover = '';
		if (!empty($limit)) $sql_limit = ' LIMIT '.$offset.', '.$limit;
			else $sql_limit = '';
/*
		# Retrieve Images for entry id
		$sql_query = '	SELECT * 
						FROM exp_easy_gallery_images 
						WHERE entry_id="'.$entry_id.'"'.$sql_cover.$sql_limit;
		$results = $DB->query($sql_query);

		# If no covers found
		if (!empty($cover) && count($results->result) == 0) {
			$sql_query = '	SELECT * 
							FROM exp_easy_gallery_images 
							WHERE entry_id="'.$entry_id.'" LIMIT 0,1';

			$results = $DB->query($sql_query);
		}
		
		// get count
		$count_sql_query = 'SELECT * 
						FROM exp_easy_gallery_images 
						WHERE entry_id="'.$entry_id.'"';
		$count_results = $DB->query($count_sql_query);
		
		$num_images = $count_results->num_rows;
*/

// BEGIN DEAN'S UPDATE

		# Retrieve Images for entry id
                $sql_query = 'SELECT *
                                                FROM exp_mc_gallery_images
                                                WHERE entry_id="'.$entry_id.'"';

                $results = $DB->query($sql_query);

		# Get count
		$num_images = $results->num_rows;
		if($num_images == 0) return;

		if(!empty($cover))
		{
			# holder 
			$holder = $results->result[0];

			for($i = 0; $i < $num_images; $i++)
			{
				if($results->result[$i]['cover'] == 'Yes')
				{
					$holder = $results->result[$i] = $results->result[$i];
					break;
				}	
			}


			# Reset result array
			unset($results->result);
			$results->result = array(); 
			$results->result[0] = $holder;

			# Reset num_rows to 1 as expected
			$results->num_rows = '1';
		}
// END DEAN'S UPDATE

/*
		echo 'echo: '.$sql_query;

		echo 'print: ';
		print_r($results->result);

		echo 'limit: '.$limit.'<br />';
		echo 'page: '.$page.'<br />';
		echo 'offset: '.$offset.'<br />';
		echo 'sql_query: '.$sql_query.'<br />';

		echo 'before paginate: '.$TMPL->tagdata.'<Br><Br>';
		echo 'AFTER tagdata: '.$TMPL->tagdata.'<br><Br>';
		print_r($match);
		die();
*/
		if ($paginate == 'yes')
		{
			# Extract easy_paginate data and remove contents from the template
			if (preg_match("/".LD."easy_paginate".RD."(.+?)".LD.SLASH."easy_paginate".RD."/s", $TMPL->tagdata, $match)) {
				$this->paginate_data = $match['1'];
			}
			$TMPL->tagdata = preg_replace("/".LD."easy_paginate".RD.".+?".LD.SLASH."easy_paginate".RD."/s", "", $TMPL->tagdata);

			# Get total records
			$sql_query = '	SELECT entry_id 
							FROM exp_mc_gallery_images 
							WHERE entry_id="'.$entry_id.'"'.$sql_cover;

			$totals = $DB->query($sql_query);
			$total_result = count($totals->result);

			# Avoid divide by zero
			if ($total_result > 0) {
				$variables['page_total'] = ceil($total_result / $limit);
			} else {
				$variables['page_total'] = 0;
			}
//			echo 'total_result: '.$total_result.' - limit: '.$limit.'<br />';

			# Default Page
			if ($page == '' || $page == '0')
			{
				$page = 1;
			}

			if ($variables['page_total'] == 1)
			{
				$page_previous = '';
				$page_next = '';
			} 
			else if ($page >= 0 && $page <= 1)
			{
				$page_previous = '';
				$page_next = $page + 1;
			} 
			else if ($variables['page_total'] <= $page)
			{
				$page_previous = $page - 1;
				$page_next = '';
			} 
			else 
			{
				$page_previous = $page - 1;
				$page_next = $page + 1;
			}

        	if (preg_match("/".LD."if page_previous".RD."(.+?)".LD.SLASH."if".RD."/s", $this->paginate_data, $match))
        	{
        		if ($page_previous == '')
        		{
        			 $this->paginate_data = preg_replace("/".LD."if previous_page".RD.".+?".LD.SLASH."if".RD."/s", '', $this->paginate_data);
        		}
				else
				{
					$match['1'] = preg_replace("/".LD.'page_previous.*?'.RD."/", 	$page_previous, $match['1']);
				
					$this->paginate_data = str_replace($match['0'],	$match['1'], $this->paginate_data);
				}
			}
        	if (preg_match("/".LD."if page_next".RD."(.+?)".LD.SLASH."if".RD."/s", $this->paginate_data, $match))
        	{
        		if ($page_next == '')
        		{
        			 $this->paginate_data = preg_replace("/".LD."if page_next".RD.".+?".LD.SLASH."if".RD."/s", '', $this->paginate_data);
        		}
        		else
        		{
					$match['1'] = preg_replace("/".LD.'page_next'.RD."/",	$page_next, $match['1']);
				
					$this->paginate_data = str_replace($match['0'],	$match['1'], $this->paginate_data);
				}
        	}

			foreach($variables as $key => $val) {
				$this->paginate_data = $TMPL->swap_var_single($key, $val, $this->paginate_data);
			}
			
			unset($variables);
		}

		foreach($results->result as $fields) {

			# Content of the Tag
			$tagdata = $TMPL->tagdata;

			# Retrieve Cover for entry id
			if($fields)
			foreach($fields as $key => $val) {
				if($key && $val)
					$variables[$key] = $val;
			}
			
			$variables['caption'] = $fields['caption'];
			$variables['img_description'] = $fields['description'];

			# Create the image urls
			list($filename,$extension) = explode('.',$fields['filename']);

			$variables['url_image_small'] = $this->base_url.$filename.'_s.'.$extension;
			$variables['url_image_medium'] = $this->base_url.$filename.'_m.'.$extension;
			$variables['url_image_large'] = $this->base_url.$filename.'_l.'.$extension;
			$variables['url_image_orig'] = $this->base_url.$filename.'.'.$extension;
			$variables['url_image_thumb'] = $this->base_url.$filename.'_thumb.'.$extension;

			/*
			if (file_exists($this->base_path.$filename.'_s.'.$extension)) {
				$variables['url_image_small'] = $this->base_url.$filename.'_s.'.$extension;
			} else {
				$variables['url_image_small'] = '';
			}
			if (file_exists($this->base_path.$filename.'_m.'.$extension)) {
				$variables['url_image_medium'] = $this->base_url.$filename.'_m.'.$extension;
			} else {
				$variables['url_image_medium'] = '';
			}
			if (file_exists($this->base_path.$filename.'_l.'.$extension)) {
				$variables['url_image_large'] = $this->base_url.$filename.'_l.'.$extension;
			} else {
				$variables['url_image_large'] = '';
			}
			
			//DY: Added reference to original image
			if (file_exists($this->base_path.$filename.'.'.$extension)) {
				$variables['url_image_orig'] = $this->base_url.$filename.'.'.$extension;
			} else {
				$variables['url_image_orig'] = '';
			}

			//DY: Added reference to thumbnail image
			if (file_exists($this->base_path.$filename.'_thumb.'.$extension)) {
				$variables['url_image_thumb'] = $this->base_url.$filename.'_thumb.'.$extension;
			} else {
				$variables['url_image_thumb'] = '';
			}
			*/
/*
			if (file_exists($this->base_path.'thumbnails/'.$filename.'.'.$extension)) {
				$variables['url_image_thumbnail'] = $this->base_url.'thumbnails/'.$filename.'.'.$extension;
			} else {
				$variables['url_image_thumbnail'] = '';
			}		
*/
			//set number of images in this gallery
			$variables['num_images'] = $num_images;

			# Assign variables to the template
			foreach($variables as $key => $val) {
				$tagdata = $TMPL->swap_var_single($key, $val, $tagdata);
			}
			
			# Adding tagdata content into the final output
			$tagdata_output .= $tagdata;
		}

		return $tagdata_output.$this->paginate_data;
	}

   
	/** -------------------------------------
    /**  Round Money
    /** -------------------------------------*/
    
    function round_money($value, $dec=2)
    {
    	global $TMPL;
    	
    	$decimal = ($TMPL->fetch_param('decimal') == ',')  ? ',' : '.';
    	
    	$value += 0.0;
    	$unit	= floor($value * pow(10, $dec+1)) / 10;
    	$round	= round($unit);
    	return str_replace('.', $decimal, sprintf("%01.2f", ($round / pow(10, $dec))));
    }
    /* END */
    
        
    /** ----------------------------------------
    /**  Sing a Song, Have a Dance
    /** ----------------------------------------*/
    
	function curl_process($url)
	{
		$postdata = 'cmd=_notify-validate';

		foreach ($_POST as $key => $value)
		{
			// str_replace("\n", "\r\n", $value)
			// put line feeds back to CR+LF as that's how PayPal sends them out
			// otherwise multi-line data will be rejected as INVALID
			$postdata .= "&$key=".urlencode(stripslashes(str_replace("\n", "\r\n", $value)));
		}

		$ch=curl_init(); 
		curl_setopt($ch,CURLOPT_SSL_VERIFYPEER,FALSE);
		curl_setopt($ch,CURLOPT_URL,$url); 
		curl_setopt($ch,CURLOPT_POST,1); 
		curl_setopt($ch,CURLOPT_POSTFIELDS,$postdata); 

		// Start ob to prevent curl_exec from displaying stuff. 
		ob_start(); 
		curl_exec($ch);

		//Get contents of output buffer 
		$info=ob_get_contents(); 
		curl_close($ch);

		//End ob and erase contents.  
		ob_end_clean(); 

		return $info; 
	}
	/* END */
	
	
	/** ----------------------------------------
    /**  Drinking with Friends is Fun!
    /** ----------------------------------------*/
	
	function fsockopen_process($url)
	{ 
		$parts	= parse_url($url);
		$host	= $parts['host'];
		$path	= (!isset($parts['path'])) ? '/' : $parts['path'];
		$port	= ($parts['scheme'] == "https") ? '443' : '80';
		$ssl	= ($parts['scheme'] == "https") ? 'ssl://' : '';
		
		
		if (isset($parts['query']) && $parts['query'] != '')
		{
			$path .= '?'.$parts['query'];
		}
		
		$postdata = 'cmd=_notify-validate';

		foreach ($_POST as $key => $value)
		{
			// str_replace("\n", "\r\n", $value)
			// put line feeds back to CR+LF as that's how PayPal sends them out
			// otherwise multi-line data will be rejected as INVALID
			$postdata .= "&$key=".urlencode(stripslashes(str_replace("\n", "\r\n", $value)));
		}
		
		$info = '';

		$fp = @fsockopen($ssl.$host, $port, $error_num, $error_str, 8); 

		if (is_resource($fp))
		{
			fputs($fp, "POST {$path} HTTP/1.0\r\n"); 
			fputs($fp, "Host: {$host}\r\n"); 
			fputs($fp, "Content-Type: application/x-www-form-urlencoded\r\n"); 
			fputs($fp, "Content-Length: ".strlen($postdata)."\r\n"); 
			fputs($fp, "Connection: close\r\n\r\n"); 
			fputs($fp, $postdata . "\r\n\r\n");
			
			while($datum = fread($fp, 4096))
			{
				$info .= $datum;
			}

			@fclose($fp); 
		}
		
		return $info; 
	}
	/* END */
    
    
	/** -------------------------------------
   /**  Clean the values for use in URLs
   /** -------------------------------------*/

	function prep_val($str)
	{
		global $REGX;
		
		// Oh, PayPal, the hoops I must jump through to woo thee...
		// PayPal is displaying its cart as UTF-8, sending UTF-8 headers, but when
		// processing the form data, is obviously wonking with it.  This will force
		// accented characters in item names to display properly on the shopping cart
		// but alas only for unencrypted data.  PayPal won't accept this same
		// workaround for encrypted form data.
		
		$str = str_replace('&amp;', '&', $str);
		$str = urlencode(utf8_decode($REGX->_html_entity_decode($str, 'utf-8')));
		
		return $str;
	}
	/* END */
	
}
/* END */

?>
