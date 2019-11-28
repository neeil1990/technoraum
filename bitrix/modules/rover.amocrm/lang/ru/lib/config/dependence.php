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
$MESS['rover_acrm__php_version_error']	    = "������ php ���� #min_php_version#";
$MESS['rover_acrm__no_curl_error']	        = "�� ������� ���������� CURL";
$MESS['rover_acrm__no_intl_error']	        = "�� ������� ���������� php-intl";
$MESS['rover_acrm__rover-fadmin_not_found']	= '�� ������ ������ �<a href="http://marketplace.1c-bitrix.ru/solutions/rover.fadmin/">����������� ���������������� �����</a>� (rover.fadmin)';
$MESS['rover_acrm__rover-params_not_found']	= '�� ������ ������ �<a href="http://marketplace.1c-bitrix.ru/solutions/rover.params/">������ ����������</a>� (rover.params)';

$MESS['rover-acrm__main-version-error']      = '��������� ������ �������� ������� (main) ������ ' . Dependence::MIN_VERSION__MAIN . ' ��� ������. �������� ��� � <a href="/bitrix/admin/update_system.php">������� ���������� ���������</a>.';
$MESS['rover-acrm__fadmin-version-error']    = '��������� ������ �<a href="http://marketplace.1c-bitrix.ru/solutions/rover.fadmin/">����������� ���������������� �����</a>� (rover.fadmin) ������ ' . Dependence::MIN_VERSION__FADMIN . ' ��� ������. �������� ��� � <a href="/bitrix/admin/update_system_partner.php">������� ���������� �������</a>.';
$MESS['rover-acrm__params-version-error']    = '��������� ������ �<a href="http://marketplace.1c-bitrix.ru/solutions/rover.params/">������ ����������</a>� (rover.params) ������ ' . Dependence::MIN_VERSION__PARAMS . ' ��� ������. �������� ��� � <a href="/bitrix/admin/update_system_partner.php">������� ���������� �������</a>.';

$MESS['rover-acrm__is_trial']       = '������� �������� � ����-������.<br>�� ������ ���������� �AmoCRM � ���������� � ���-������� � ��������� ��������� �� <a href="http://marketplace.1c-bitrix.ru/solutions/rover.amocrm/">������� �����������</a>.';
$MESS['rover-acrm__trial_expired']  = '����-������ ����.<br>�� ������ ���������� �AmoCRM � ���������� � ���-������� � ��������� ��������� �� <a href="http://marketplace.1c-bitrix.ru/solutions/rover.amocrm/">������� �����������</a>.';
$MESS['rover-acrm__writable-error'] = '���� "#path#" ���������� ��� ������';
$MESS['rover-acrm__mkdir-error']    = '�� ������� ������� ��������� "#dir#"';