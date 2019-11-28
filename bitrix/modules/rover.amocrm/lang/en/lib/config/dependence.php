<?php
use \Rover\AmoCRM\Config\Dependence;
/**
 * Created by PhpStorm.
 * User: lenovo
 * Date: 12.05.2017
 * Time: 16:36
 *
 * @author Pavel Shulaev (https://rover-it.me)
 */
$MESS['rover_acrm__php_version_error']	    = "Php version below #min_php_version#";
$MESS['rover_acrm__no_curl_error']	        = "CURL library not found";
$MESS['rover_acrm__no_intl_error']	        = "The php-intl extension was not found";
$MESS['rover_acrm__rover-fadmin_not_found']	= 'Module not found: «<a href="http://marketplace.1c-bitrix.ru/solutions/rover.fadmin/">Administrative Constructor</a>» (rover.fadmin)';
$MESS['rover_acrm__rover-params_not_found']	= 'Module not found: «<a href="http://marketplace.1c-bitrix.ru/solutions/rover.params/">Param\'s lists</a>» (rover.params)';

$MESS['rover-acrm__main-version-error']      = 'Required module «Main Module» (main) version ' . Dependence::MIN_VERSION__MAIN . ' or higher. Please, update it in <a href="/bitrix/admin/update_system.php">Platform Update</a>.';
$MESS['rover-acrm__fadmin-version-error']    = 'Required module «<a href="http://marketplace.1c-bitrix.ru/solutions/rover.fadmin/">Administrative Constructor</a>» (rover.fadmin) version ' . Dependence::MIN_VERSION__FADMIN . ' or higher. Please, update it in <a href="/bitrix/admin/update_system_partner.php">Solution Update</a>.';
$MESS['rover-acrm__params-version-error']    = 'Required module «<a href="http://marketplace.1c-bitrix.ru/solutions/rover.params/">Param\'s lists</a>» (rover.params) version ' . Dependence::MIN_VERSION__PARAMS . ' or higher. Please, update it in <a href="/bitrix/admin/update_system_partner.php">Solution Update</a>.';

$MESS['rover-acrm__is_trial']       = 'Before the trial-period expires, there are #num# days left.<br>You can purchase the module "AmoCRM - integration with web forms and mail events" on <a href="http://marketplace.1c-bitrix.ru/solutions/rover.amocrm/">Bitrix Marketplace</a>.';
$MESS['rover-acrm__trial_expired']  = 'Trial-period has expired.<br>You can purchase the module "AmoCRM - integration with web forms and mail events" on <a href="http://marketplace.1c-bitrix.ru/solutions/rover.amocrm/">Bitrix Marketplace</a>.';
$MESS['rover-acrm__writable-error'] = 'File "#path#" is not writable';
$MESS['rover-acrm__mkdir-error']    = 'Could not create directory "#dir#"';