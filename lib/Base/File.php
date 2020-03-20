<?php
/**
	@copyright	2008 Edoceo, Inc
	@package	edoceo-imperium
	@link	   http://imperium.edoceo.com
	@since	  File available since Release 1099
*/

namespace Edoceo\Imperium;

class Base_File extends ImperiumBase
{
	protected $_table = 'base_file';

	/**
		Copies the File to our Path
	*/
	function import($f)
	{
		$this->_data['path'] = $this->_savePath();
		if (is_file($this->_data['path'])) {
			// die(sprintf("Already Imported: %s",$this->path));
		}

		if (!is_dir(dirname($this->_data['path']))) {
			mkdir(dirname($this->_data['path']),0755,true);
		}

		move_uploaded_file($f , $this->_data['path']);
	}

	/**
		Updates the path
	*/
	function save()
	{
		if (empty($this->_data['path'])) {
			$this->_data['path'] = $this->_savePath();
		}
		$r = parent::save();
		return $r;
	}
	/**
		Calculate the Path where this file would be saved
	*/
	private function _savePath()
	{
		if (empty($this->_data['hash'])) {
			$this->_data['hash'] = $this->hash();
		}
		$name = sprintf('%s/%s/%s/%s',substr($this->_data['hash'],0,2),substr($this->_data['hash'],2,2),substr($this->_data['hash'],4,2),$this->_data['hash']);
		return APP_ROOT . '/var/file/' . $name;
	}

	/**
	*/
	static function mimeInfo($mime)
	{
		$mime = trim(strtolower($mime));
		if (!preg_match('/(\w+)\/([\w\-\.\+]+)/',$mime,$m)) {
			return false;
		}
		$mime_maj = $m[1];
		$mime_min = $m[2];
		$mime_all = sprintf('%s/%s',$mime_maj,$mime_min);

		static $tab;
		if (empty($tab)) $tab = parse_ini_file(APP_ROOT . '/etc/mime.ini',true);

		foreach (array($mime_all,"$mime_maj/*",'*/*') as $x) {
			if (!empty($tab[$x])) return $tab[$x];
		}

		return null;
	}

	/**
	*/
//	 static function niceMimeIcon($mime)
//	 {
//		 $mime = strtolower($mime);
//		 // func: mime_type_map($mime_type) - Maps MIME type to common type
//		 // todo: use /etc/apache2/mime.types
//		 $mt = array(
//		   'application/excel' => 'page_white_excel.png',
//		   'application/msword' => 'page_white_word.png',
//		   'application/octet-stream' => 'Application',
//		   'application/pdf' => 'page_white_acrobat.png',
//		   'application/powerpoint' => 'page_white_powerpoint.png',
//		   'application/x-zip' => 'page_white_zip.png',
//		   'application/x-zip-compressed' => 'page_white_zip.png',
//		   'application/vnd.oasis.opendocument.graphics' => 'OpenOffice Draw',
//		   'application/vnd.oasis.opendocument.spreadsheet' => 'OpenOffice Calc',
//		   'image/gif' => 'images.png',
//		   'image/jpeg' => 'images.png',
//		   'image/png' => 'images.png',
//		   'text/html' => 'page_white_world.png',
//		   'text/plain' => 'page_white_text.png',
//		 );
//		 $r = isset($mt[$mime]) ? $mt[$mime] : 'page.png';
//		 return $r;
//	 }
//
//	 static function niceMimeType($mime)
//	 {
//		 $mime = strtolower($mime);
//		 // func: mime_type_map($mime_type) - Maps MIME type to common type
//		 // todo: use /etc/apache2/mime.types
//		 $list = array(
//			 'application/excel' => 'MS Excel',
//			 'application/msword' => 'MS Word',
//			 'application/octet-stream' => 'Application',
//			 'application/pdf' => 'Acrobat PDF Document',
//			 'application/postscript' => 'PostScript',
//			 'application/powerpoint' => 'MS PowerPoint',
//			 'application/x-zip' => 'Zip Archive',
//			 'application/x-zip-compressed' => 'Zip Archive',
//			 'application/vnd.oasis.opendocument.graphics' => 'OpenOffice Draw',
//			 'application/vnd.oasis.opendocument.spreadsheet' => 'OpenOffice Calc',
//			 'image/gif' => 'GIF Image',
//			 'image/jpeg' => 'JPG Image',
//			 'image/png' => 'PNG Image',
//			 'text/html' => 'HTML Document',
//			 'text/plain' => 'Text Document',
//		 );
//		 $r = isset($list[$mime]) ? $list[$mime] : 'Unknown';
//		 return $r;
//	 }

	/**
		returns true if the posted thing is good
	*/
	static function goodPost($f)
	{
		if ($f['error'] != UPLOAD_ERR_OK) {
			return false;
		}

		if (is_uploaded_file($f['tmp_name'])==false) {
			return false;
		}

		return true;
	}

	/**
		File readPost
		Reads a Post Field into a File object
	*/
	function copyPost($pf)
	{
		$this->_data['name'] = $pf['name'];
		$this->_data['kind'] = $pf['type'];
		$this->_data['size'] = $pf['size'];
		$this->import($pf['tmp_name']);
	}
}
