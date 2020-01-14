<?php
/**
 * Company developer: ALTASIB
 * Developer: adumnov
 * Site: http://www.altasib.ru
 * E-mail: dev@altasib.ru
 * @copyright (c) 2006-2019 ALTASIB
 */

class CAltasibGeoBaseJSPopup extends CJSPopup
{
	function StartContent($arAdditional = array())
	{
		$this->InitSystem();

		$this->EndDescription();
		$this->bContentStarted = true;

		if ($arAdditional['buffer'])
		{
			$this->bContentBuffered = true;
			//ob_start();
			$this->cont_id = RandString(10);
			echo '<div id="'.$this->cont_id.'" style="display: none;">';
		}

		echo '<form name="'.$this->__form_name.'" enctype="multipart/form-data" method="post">'."\r\n";
		echo bitrix_sessid_post()."\r\n";

		if (is_set($_REQUEST, 'back_url'))
			echo '<input type="hidden" name="back_url" value="'.htmlspecialcharsbx($_REQUEST['back_url']).'" />'."\r\n";
	}

}