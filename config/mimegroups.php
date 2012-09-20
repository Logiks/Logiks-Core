<?php
// This is inspired from many of the other systems eg. Drupal

/**
 * Default File MIME extension mapping.
 *
 * @return
 *   Array of mimetypes correlated to the extensions that relate to them.
 *
 * @see getFileMimeArray()
 */
if(!function_exists("getFileMimeGroups")) {
	function getFileMimeGroups() {
		return array(
				"xls,xlsx"=>"SpreadSheets",
				"doc,docx"=>"Documents",
				"ppt,pptx"=>"Presentation",
				
				"pdf"=>"PDF Documents",
				"mm"=>"Mindmaps",
				"txt"=>"Text Documents",
				
				"png"=>"PNG Graphics",
				"jpg,jpeg"=>"JPEG Graphics",
				"gif"=>"GIF Graphics",
				"BMP"=>"BMP Graphics",
				"png,gif,jpeg,jpg,bmp,psd"=>"All Graphics",
				
				"mp3"=>"MP3 Music",
				"wav"=>"WAV Music",
				"mp3,mp4,wav"=>"All Music",
				
				"avi"=>"AVI Videos",
				"mpeg"=>"MPEG Videos",
				"avi,mpeg"=>"All Videos",
			);
	}
	function getMimeGroups() {
		return array(
				"Office Documents"=>array(
					"xls,xlsx"=>"SpreadSheets",
					"doc,docx"=>"Documents",
					"ppt,pptx"=>"Presentation",
				),
				"Other Documents"=>array(
					"pdf"=>"PDF Documents",
					"mm"=>"Mindmaps",
					"txt"=>"Text Documents",
				),
				"Graphics"=>array(
					"png"=>"PNG Graphics",
					"jpg,jpeg"=>"JPEG Graphics",
					"gif"=>"GIF Graphics",
					"BMP"=>"BMP Graphics",
					"png,gif,jpeg,jpg,bmp,psd"=>"All Graphics",
				),
				"Music"=>array(
					"mp3"=>"MP3 Music",
					"wav"=>"WAV Music",
					"mp3,mp4,wav"=>"All Music",
				),
				"Videos"=>array(
					"avi"=>"AVI Videos",
					"mpeg"=>"MPEG Videos",
					"avi,mpeg"=>"All Videos",
				)
			);
	}
}
?>
