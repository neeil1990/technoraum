<?
class CAltasibGeobaseArchiver{
	var $_strArchiveName = "";
	var $_bCompress = false;
	var $_strSeparator = " ";
	var $_dFile = 0;

	var $_arErrors = array();
	var $iArchSize = 0;
	var $iCurPos = 0;
	var $bFinish = false;

	function __construct($strArchiveName, $bCompress = false){
		$this->_bCompress = false;
		if (!$bCompress){
			if (file_exists($strArchiveName)){
				if ($fp = fopen($strArchiveName, "rb"))	{
					$data = fread($fp, 2);
					if ($data == "\37\213"){
						$this->_bCompress = True;
					}
				}
			}else{
				if (substr($strArchiveName, -2) == 'gz'){
					$this->_bCompress = True;
				}
			}
		}
		else{
			$this->_bCompress = True;
		}
		$this->_strArchiveName = $strArchiveName;
		$this->_arErrors = array();
	}

	function extractFiles($strPath, $vFileList = false)	{
		$this->_arErrors = array();

		$v_result = true;
		$v_list_detail = array();

		$strExtrType = "complete";
		$arFileList = 0;
		if ($vFileList!==false){
			$arFileList = &$this->_parseFileParams($vFileList);
			$strExtrType = "partial";
		}

		if ($v_result = $this->_openRead()){
			$v_result = $this->_extractList($strPath, $v_list_detail, $strExtrType, $arFileList, '', '');
			$this->_close();
		}

		return $v_result;
	}

	function &GetErrors() {
		return $this->_arErrors;
	}

	function _extractList($p_path, &$p_list_detail, $p_mode, $p_file_list, $p_remove_path, $v_filename)	{
		global $iNumDistrFiles;

		$v_result = true;
		$v_nb = 0;
		$v_extract_all = true;
		$v_listing = false;

		$p_path = str_replace("\\", "/", $p_path);

		if ($p_path == '' || (substr($p_path, 0, 1) != '/' && substr($p_path, 0, 3) != "../" && !strpos($p_path, ':'))){
			$p_path = "./".$p_path;
		}

		$p_remove_path = str_replace("\\", "/", $p_remove_path);
		if (($p_remove_path != '') && (substr($p_remove_path, -1) != '/'))
			$p_remove_path .= '/';

		$p_remove_path_size = strlen($p_remove_path);

		switch ($p_mode){
			case "complete" :
				$v_extract_all = TRUE;
				$v_listing = FALSE;
				break;
			case "partial" :
				$v_extract_all = FALSE;
				$v_listing = FALSE;
				break;
			case "list" :
				$v_extract_all = FALSE;
				$v_listing = TRUE;
				break;
			default :
				$this->_arErrors[] = array("ERR_PARAM", "Invalid extract mode (".$p_mode.")");
				return false;
		}

		clearstatcache();

		$tm=time();
		while((extension_loaded("mbstring")? mb_strlen($v_binary_data = $this->_readBlock(), "latin1") : strlen($v_binary_data = $this->_readBlock())) != 0){
			$v_extract_file = FALSE;
			$v_extraction_stopped = 0;

			if (!$this->_readHeader($v_binary_data, $v_header))
				return false;

			if ($v_header['filename'] == '')
				continue;

			// -- Look for long filename
				if ($v_header['typeflag'] == 'L')
			{
				if (!$this->_readLongHeader($v_header))
					return false;
			}


			if ((!$v_extract_all) && (is_array($p_file_list)))
			{
				// -- By default no unzip if the file is not found
				$v_extract_file = false;

				for ($i = 0; $i < count($p_file_list); $i++)
				{
					// -- Look if it is a directory
					if (substr($p_file_list[$i], -1) == '/')
					{
						// -- Look if the directory is in the filename path
						if ((strlen($v_header['filename']) > strlen($p_file_list[$i]))
							&& (substr($v_header['filename'], 0, strlen($p_file_list[$i])) == $p_file_list[$i]))
						{
							$v_extract_file = TRUE;
							break;
						}
					}
					elseif ($p_file_list[$i] == $v_header['filename'])
					{
						// -- It is a file, so compare the file names
						$v_extract_file = TRUE;
						break;
					}
				}
			}
			else{
				$v_extract_file = TRUE;
			}

			// -- Look if this file need to be extracted
			if (($v_extract_file) && (!$v_listing)){
				if (($p_remove_path != '') && (substr($v_header['filename'], 0, $p_remove_path_size) == $p_remove_path)){
					$v_header['filename'] = substr($v_header['filename'], $p_remove_path_size);
				}
				if (($p_path != './') && ($p_path != '/')){
					while (substr($p_path, -1) == '/')
						$p_path = substr($p_path, 0, strlen($p_path)-1);

					if (substr($v_header['filename'], 0, 1) == '/')
						$v_header['filename'] = $p_path.$v_header['filename'];
					else
						$v_header['filename'] = $p_path.'/'.$v_header['filename'];
				}
				if (file_exists($v_header['filename'])){
					if ((@is_dir($v_header['filename'])) && ($v_header['typeflag'] == '')){
						$this->_arErrors[] = array("DIR_EXISTS", "File '".$v_header['filename']."' already exists as a directory");
						return false;
					}
					if ((is_file($v_header['filename'])) && ($v_header['typeflag'] == "5")){
						$this->_arErrors[] = array("FILE_EXISTS", "Directory '".$v_header['filename']."' already exists as a file");
						return false;
					}
					if (!is_writeable($v_header['filename'])){
						$this->_arErrors[] = array("FILE_PERMS", "File '".$v_header['filename']."' already exists and is write protected");
						return false;
					}
				}elseif (($v_result = $this->_dirCheck(($v_header['typeflag'] == "5" ? $v_header['filename'] : dirname($v_header['filename'])))) != 1){
					$this->_arErrors[] = array("NO_DIR", "Unable to create path for '".$v_header['filename']."'");
					return false;
				}

				if ($v_extract_file){
					if ($v_header['typeflag'] == "5"){
						if (!@file_exists($v_header['filename'])){
							if (!@mkdir($v_header['filename'], BX_DIR_PERMISSIONS)){
								$this->_arErrors[] = array("ERR_CREATE_DIR", "Unable to create directory '".$v_header['filename']."'");
								return false;
							}
						}
					}else{
						if (($v_dest_file = fopen($v_header['filename'], "wb")) == 0){
							$this->_arErrors[] = array("ERR_CREATE_FILE", LoaderGetMessage('NO_PERMS') .' '. $v_header['filename']);
							return false;
						}else{
							$n = floor($v_header['size']/512);
							for ($i = 0; $i < $n; $i++){
								$v_content = $this->_readBlock();
								fwrite($v_dest_file, $v_content, 512);
							}
							if (($v_header['size'] % 512) != 0){
								$v_content = $this->_readBlock();
								fwrite($v_dest_file, $v_content, ($v_header['size'] % 512));
							}

							@fclose($v_dest_file);

							@chmod($v_header['filename'], BX_FILE_PERMISSIONS);
							@touch($v_header['filename'], $v_header['mtime']);
						}

						clearstatcache();
						if (filesize($v_header['filename']) != $v_header['size']){
							$this->_arErrors[] = array("ERR_SIZE_CHECK", "Extracted file '".$v_header['filename']."' have incorrect file size '".filesize($v_filename)."' (".$v_header['size']." expected). Archive may be corrupted");
							return false;
						}
					}
				}else{
					$this->_jumpBlock(ceil(($v_header['size']/512)));
				}
			}else{
				$this->_jumpBlock(ceil(($v_header['size']/512)));
			}

			if ($v_listing || $v_extract_file || $v_extraction_stopped){
				if (($v_file_dir = dirname($v_header['filename'])) == $v_header['filename'])
					$v_file_dir = '';
				if ((substr($v_header['filename'], 0, 1) == '/') && ($v_file_dir == ''))
					$v_file_dir = '/';

				$p_list_detail[$v_nb++] = $v_header;

				if ($v_nb % 100 == 0)
					SetCurrentProgress($this->iCurPos, $this->iArchSize, False);
			}

			if ($_REQUEST['by_step'] && (time()-$tm) > TIMEOUT){
				SetCurrentProgress($this->iCurPos, $this->iArchSize, False);
				return true;
			}
		}
		$this->bFinish = true;
		return true;
	}

	function _readBlock(){
		$v_block = "";
		if (is_resource($this->_dFile)){
			if (isset($_REQUEST['seek'])){
				if ($this->_bCompress)
					gzseek($this->_dFile, intval($_REQUEST['seek']));
				else
					fseek($this->_dFile, intval($_REQUEST['seek']));

				$this->iCurPos = IntVal($_REQUEST['seek']);

				unset($_REQUEST['seek']);
			}
			if ($this->_bCompress)
				$v_block = gzread($this->_dFile, 512);
			else
				$v_block = fread($this->_dFile, 512);

			$this->iCurPos += (extension_loaded("mbstring")? mb_strlen($v_block, "latin1") : strlen($v_block));
		}
		return $v_block;
	}

	function _readHeader($v_binary_data, &$v_header){
		if ((extension_loaded("mbstring")? mb_strlen($v_binary_data, "latin1") : strlen($v_binary_data)) ==0)
		{
			$v_header['filename'] = '';
			return true;
		}

		if ((extension_loaded("mbstring")? mb_strlen($v_binary_data, "latin1") : strlen($v_binary_data)) != 512)
		{
			$v_header['filename'] = '';
			$this->_arErrors[] = array("INV_BLOCK_SIZE", "Invalid block size : ".strlen($v_binary_data)."");
			return false;
		}

		$v_checksum = 0;
		for ($i = 0; $i < 148; $i++)
			$v_checksum+=ord(substr($v_binary_data, $i, 1));
		for ($i = 148; $i < 156; $i++)
			$v_checksum += ord(' ');
		for ($i = 156; $i < 512; $i++)
			$v_checksum+=ord(substr($v_binary_data, $i, 1));

		$v_data = unpack("a100filename/a8mode/a8uid/a8gid/a12size/a12mtime/a8checksum/a1typeflag/a100link/a6magic/a2version/a32uname/a32gname/a8devmajor/a8devminor/a155prefix/a12temp", $v_binary_data);

		$v_header['checksum'] = OctDec(trim($v_data['checksum']));
		if ($v_header['checksum'] != $v_checksum)
		{
			$v_header['filename'] = '';

			if (($v_checksum == 256) && ($v_header['checksum'] == 0))
				return true;

			$this->_arErrors[] = array("INV_BLOCK_CHECK", "Invalid checksum for file '".$v_data['filename']."' : ".$v_checksum." calculated, ".$v_header['checksum']." expected");
			return false;
		}

		// -- Extract the properties
		$v_header['filename'] = trim($v_data['prefix']."/".$v_data['filename']);
		$v_header['mode'] = OctDec(trim($v_data['mode']));
		$v_header['uid'] = OctDec(trim($v_data['uid']));
		$v_header['gid'] = OctDec(trim($v_data['gid']));
		$v_header['size'] = OctDec(trim($v_data['size']));
		$v_header['mtime'] = OctDec(trim($v_data['mtime']));
		if (($v_header['typeflag'] = $v_data['typeflag']) == "5")
			$v_header['size'] = 0;

		return true;
	}

	function _readLongHeader(&$v_header){
		$v_filename = '';
		$n = floor($v_header['size']/512);
		for ($i = 0; $i < $n; $i++)
		{
			$v_content = $this->_readBlock();
			$v_filename .= $v_content;
		}
		if (($v_header['size'] % 512) != 0)
		{
			$v_content = $this->_readBlock();
			$v_filename .= $v_content;
		}

		$v_binary_data = $this->_readBlock();

		if (!$this->_readHeader($v_binary_data, $v_header))
			return false;

		$v_header['filename'] = $v_filename;

		return true;
	}

	function _jumpBlock($p_len = false){
		if (is_resource($this->_dFile))
		{
			if ($p_len === false)
				$p_len = 1;

			if ($this->_bCompress)
				gzseek($this->_dFile, gztell($this->_dFile)+($p_len*512));
			else
				fseek($this->_dFile, ftell($this->_dFile)+($p_len*512));
		}
		return true;
	}

	function &_parseFileParams(&$vFileList){
		if (isset($vFileList) && is_array($vFileList))
			return $vFileList;
		elseif (isset($vFileList) && strlen($vFileList)>0)
			return explode($this->_strSeparator, $vFileList);
		else
			return array();
	}

	function _openRead(){
		if ($this->_bCompress){
			$this->_dFile = gzopen($this->_strArchiveName, "rb");
			$this->iArchSize = filesize($this->_strArchiveName) * 3;
		}else {
			$this->_dFile = fopen($this->_strArchiveName, "rb");
			$this->iArchSize = filesize($this->_strArchiveName);
		}
		if (!$this->_dFile){
			$this->_arErrors[] = array("ERR_OPEN", "Unable to open '".$this->_strArchiveName."' in read mode");
			return false;
		}
		return true;
	}

	function _close(){
		if (is_resource($this->_dFile)){
			if ($this->_bCompress)
				gzclose($this->_dFile);
			else
				fclose($this->_dFile);

			$this->_dFile = 0;
		}

		return true;
	}

	function _dirCheck($p_dir){
		if ((is_dir($p_dir)) || ($p_dir == ''))
			return true;

		$p_parent_dir = dirname($p_dir);

		if (($p_parent_dir != $p_dir) &&
			($p_parent_dir != '') &&
			(!$this->_dirCheck($p_parent_dir)))
			return false;

		if (!is_dir($p_dir) && !mkdir($p_dir, BX_DIR_PERMISSIONS))
		{
			$this->_arErrors[] = array("CANT_CREATE_PATH", "Unable to create directory '".$p_dir."'");
			return false;
		}

		return true;
	}
}

?>