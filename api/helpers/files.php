<?php
if(!defined('ROOT')) exit('No direct script access allowed');

if(!function_exists("getDirTree")) {
	/**
	 * Get Directory File Information
	 *
	 * Reads the specified directory and builds an array containing the filenames,
	 * filesize, dates, and permissions
	 *
	 * Any sub-folders contained within the specified path are read as well.
	 *
	 * @access	public
	 * @param	string	path to source
	 * @param	bool	Look only at the top level directory specified?
	 * @param	bool	internal variable to determine recursion status - do not use in calls
	 * @return	array
	 */
	function getDirTree($source_dir, $top_level_only = TRUE, $_recursion = FALSE) {
		static $_filedata = array();
		$relative_path = $source_dir;

		if ($fp = @opendir($source_dir)) {
			// reset the array and make sure $source_dir has a trailing slash on the initial call
			if ($_recursion === FALSE) {
				$_filedata = array();
				$source_dir = rtrim(realpath($source_dir), DIRECTORY_SEPARATOR).DIRECTORY_SEPARATOR;
			}

			// foreach (scandir($source_dir, 1) as $file) // In addition to being PHP5+, scandir() is simply not as fast
			while (FALSE !== ($file = readdir($fp))) {
				if (@is_dir($source_dir.$file) AND strncmp($file, '.', 1) !== 0 AND $top_level_only === FALSE) {
					get_dir_file_info($source_dir.$file.DIRECTORY_SEPARATOR, $top_level_only, TRUE);
				} elseif (strncmp($file, '.', 1) !== 0) {
					$_filedata[$file] = get_file_info($source_dir.$file);
					$_filedata[$file]['relative_path'] = $relative_path;
				}
			}

			return $_filedata;
		} else {
			return FALSE;
		}
	}
	
	/**
	 * Create a Directory Map
	 *
	 * Reads the specified directory and builds an array
	 * representation of it.  Sub-folders contained with the
	 * directory will be mapped as well.
	 *
	 * @access	public
	 * @param	string	path to source
	 * @param	int		depth of directories to traverse (0 = fully recursive, 1 = current dir, etc)
	 * @return	array
	 */
	function directory_map($source_dir, $directory_depth = 0, $hidden = FALSE) {
		if ($fp = @opendir($source_dir)) {
			$filedata	= array();
			$new_depth	= $directory_depth - 1;
			$source_dir	= rtrim($source_dir, DIRECTORY_SEPARATOR).DIRECTORY_SEPARATOR;

			while (FALSE !== ($file = readdir($fp))) {
				// Remove '.', '..', and hidden files [optional]
				if ( ! trim($file, '.') OR ($hidden == FALSE && $file[0] == '.')) {
					continue;
				}

				if (($directory_depth < 1 OR $new_depth > 0) && @is_dir($source_dir.$file)) {
					$filedata[$file] = directory_map($source_dir.$file.DIRECTORY_SEPARATOR, $new_depth, $hidden);
				} else {
					$filedata[] = $file;
				}
			}

			closedir($fp);
			return $filedata;
		}

		return FALSE;
	}
	
	function getFileInfo($file, $returned_values = array('name', 'server_path', 'size', 'date')) {
		if (!file_exists($file)) {
			return FALSE;
		}
		if(is_string($returned_values)) {
			$returned_values = explode(',', $returned_values);
		}
		$fileinfo=array();
		foreach ($returned_values as $key) {
			switch ($key) {
				case 'name':
					$fileinfo['name'] = substr(strrchr($file, DIRECTORY_SEPARATOR), 1);
					break;
				case 'server_path':
					$fileinfo['server_path'] = $file;
					break;
				case 'size':
					$fileinfo['size'] = filesize($file);
					break;
				case 'date':
					$fileinfo['date'] = filemtime($file);
					break;
				case 'readable':
					$fileinfo['readable'] = is_readable($file);
					break;
				case 'writable':
					// There are known problems using is_weritable on IIS.  It may not be reliable - consider fileperms()
					$fileinfo['writable'] = is_writable($file);
					break;
				case 'executable':
					$fileinfo['executable'] = is_executable($file);
					break;
				case 'fileperms':
					$fileinfo['fileperms'] = fileperms($file);
					break;
			}
		}

		return $fileinfo;
	}
	
	function getFilePermissions($perms) {
		if (($perms & 0xC000) == 0xC000) {
			$symbolic = 's'; // Socket
		} elseif (($perms & 0xA000) == 0xA000){
			$symbolic = 'l'; // Symbolic Link
		} elseif (($perms & 0x8000) == 0x8000){
			$symbolic = '-'; // Regular
		} elseif (($perms & 0x6000) == 0x6000){
			$symbolic = 'b'; // Block special
		} elseif (($perms & 0x4000) == 0x4000){
			$symbolic = 'd'; // Directory
		} elseif (($perms & 0x2000) == 0x2000){
			$symbolic = 'c'; // Character special
		} elseif (($perms & 0x1000) == 0x1000){
			$symbolic = 'p'; // FIFO pipe
		} else {
			$symbolic = 'u'; // Unknown
		}

		// Owner
		$symbolic .= (($perms & 0x0100) ? 'r' : '-');
		$symbolic .= (($perms & 0x0080) ? 'w' : '-');
		$symbolic .= (($perms & 0x0040) ? (($perms & 0x0800) ? 's' : 'x' ) : (($perms & 0x0800) ? 'S' : '-'));

		// Group
		$symbolic .= (($perms & 0x0020) ? 'r' : '-');
		$symbolic .= (($perms & 0x0010) ? 'w' : '-');
		$symbolic .= (($perms & 0x0008) ? (($perms & 0x0400) ? 's' : 'x' ) : (($perms & 0x0400) ? 'S' : '-'));

		// World
		$symbolic .= (($perms & 0x0004) ? 'r' : '-');
		$symbolic .= (($perms & 0x0002) ? 'w' : '-');
		$symbolic .= (($perms & 0x0001) ? (($perms & 0x0200) ? 't' : 'x' ) : (($perms & 0x0200) ? 'T' : '-'));
		
		return $symbolic;
	}
	
	function getDirSize($f) {
		if(is_dir($f)) {
			$fs=scandir($f);
			unset($fs[0]);unset($fs[1]);
			$cnt=0;
			foreach($fs as $a) {
				$cnt+=getDirSize($f."/".$a);
			}
			return $cnt;
		} else {
			return filesize($f);
		}
	}
	
	function getFileSizeInString($size,$n=0) {
		$size1=$size;
		for($i=0;$i<$n;$i++) {
			$size1=$size1/1024;
		}
		if($size1>1024 && $n<2) {
			return getFileSizeInString($size,$n+1);
		} else {
			$nx=strpos($size1,".");
			if($nx>0) $size1=substr($size1,0,$nx+3);
			
			if($n<=0) return $size1." bytes";
			elseif($n==1) return $size1." kb";
			elseif($n==2) return $size1." mb";
			elseif($n==3) return $size1." Gb";
			else return $size1." Tb";
		}
	}
	
	function fileExtension($filename) {
		return end(explode(".", $filename));
	}
	
	function fileName($filename) {
		$arr=explode(".", $filename);
		unset($arr[count($arr)-1]);
		return implode(".",$arr);
	}
	
	function deleteDir($path) {
		return is_file($path)? @unlink($path): array_map('deleteDir',glob($path.'/*'))==@rmdir($path);
	}
	
	function mkdirs($dir,$recreate=false) {
		if(file_exists($dir)  && is_dir($dir)) {
			if(!$recreate) {
				return true;
			}
			@deleteDir($dir);
		}
		@mkdir($dir,0777,true);
		@chmod($dir,0777);
		return is_dir($dir);
	}
}
?>
