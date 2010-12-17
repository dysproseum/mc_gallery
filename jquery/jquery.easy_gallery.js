//Control Panel Gallery functions

// prepare the form when the DOM is ready 
$(document).ready(function() { 

	// GET Request
	$.get("${base_url}&P=ext_gallery_images",
	   {},
	   function(data){
		 $('#extension_gallery').html(data);
	   }
	 );

}); 

// AJAX HTML UPLOAD
function ext_gallery_upload_html() {
	// GET Request
	$.get("${base_url}&P=ext_gallery_upload_html",
	   {},
	   function(data){
		 $('#extension_gallery').html(data);
		 initUpload();
	   }
	 );
}  // end ajax_test


// AJAX FLASH UPLOAD
function ext_gallery_upload_flash() {
	// GET Request
	$.get("${base_url}&P=ext_gallery_upload_flash",
	   {},
	   function(data){
		 $('#extension_gallery').html(data);
		 initUpload();
	   }
	 );
}  // end ajax_test


// AJAX TEST
function ext_gallery_images() {
	// GET Request
	$.get("${base_url}&P=ext_gallery_images",
	   {},
	   function(data){
		 $('#extension_gallery').html(data);
	   }
	 );
}  // end ajax_test


// AJAX TEST
function ext_gallery_operations() {
	// GET Request
	$.get("${base_url}&P=ext_gallery_operations",
	   {},
	   function(data){
		 $('#extension_gallery').html(data);
	   }
	 );
}  // end ajax_test


// AJAX TEST
function ext_gallery_audio() {
	// GET Request
	$.get("${base_url}&P=ext_gallery_audio",
	   {},
	   function(data){
		 $('#extension_gallery').html(data);
	   }
	 );
}  // end ajax_test


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


function image_recreate(which,entry_id,image_id) {

	if (which == 'small') {
		var image_width = $("input[@name=small_width]").val();
		var image_height = $("input[@name=small_height]").val();
	} else if (which == 'medium') {
		var image_width = $("input[@name=medium_width]").val();
		var image_height = $("input[@name=medium_height]").val();
	} else if (which == 'large') {
		var image_width = $("input[@name=large_width]").val();
		var image_height = $("input[@name=large_height]").val();
	}

	$.ajax({
		type: "GET",
		url: "{$base_url}",
		data: "P=ext_gallery_images_action&A=image_recreate&which="+which+"&image_width="+image_width+"&image_height="+image_height+"&entry_id="+entry_id+"&image_id="+image_id,
		success: 
			function(msg){
				ext_gallery_images();
			},
		dataType: "script"
	})
}


function image_delete(entry_id,image_id) {
	$.ajax({
		type: "GET",
		url: "{$base_url}",
		data: "P=ext_gallery_images_action&A=image_delete&entry_id="+entry_id+"&image_id="+image_id,
		success: 
			function(msg){
				ext_gallery_images();
			},
		dataType: "script"
	})
}

function image_cover(entry_id,image_id) {
	$.ajax({
		type: "GET",
		url: "{$base_url}",
		data: "P=ext_gallery_images_action&A=image_cover&entry_id="+entry_id+"&image_id="+image_id,
		success: 
			function(msg){
				ext_gallery_images();
			},
		dataType: "script"
	})
}

function image_caption(image_id,caption) {
	var caption = prompt("Enter your caption", "")

	$.ajax({
		type: "GET",
		url: "{$base_url}",
		data: "P=ext_gallery_images_action&A=image_caption&image_id="+image_id+"&caption="+caption,
		success: 
			function(msg){
				ext_gallery_images();
			},
		dataType: "script"
	})
}

function entry_recreate(which,entry_id) {

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
			alert("P=ext_gallery_images_action&A=entry_recreate&which="+which+"&image_width="+image_width+"&image_height="+image_height+"&image_quality="+image_quality+"&image_scale="+image_scale+"&entry_id="+entry_id);
			alert("which="+which+"&image_width="+image_width+"&image_height="+image_height+"&image_quality="+image_quality+"&image_scale="+image_scale);
			alert("&image_scale="+image_scale);


		$("body").css('cursor','wait');
		$.ajax({
			type: "GET",
			url: "{$base_url}",
			data: "P=ext_gallery_images_action&A=entry_recreate&which="+which+"&image_width="+image_width+"&image_height="+image_height+"&image_quality="+image_quality+"&image_scale="+image_scale+"&entry_id="+entry_id,
			success: 
				function(msg){
					ext_gallery_images();
					$("body").css('cursor','default');
				},
			dataType: "script"
		})
	}
}


function entry_delete(which,entry_id) {

	document.write("http://localhost/system/index.php?S=20880686c2a9b4c95fd8b5b412cb6920d9644aff&C=modules&M=mc_gallery&P=ext_gallery_images_action&A=entry_delete&entry_id="+entry_id);

	var answer = confirm('Are you sure you want to delete '+which+' images?');

	if (answer){
		$.ajax({
			type: "GET",
			url: "{$base_url}",
			data: "P=ext_gallery_images_action&A=entry_delete&which="+which+"&entry_id="+entry_id,
			success: 
				function(msg){
					ext_gallery_images();
				},
			dataType: "script"
		})
	}
}