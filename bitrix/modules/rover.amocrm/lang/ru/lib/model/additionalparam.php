<?php
/**
 * Created by PhpStorm.
 * User: lenovo
 * Date: 16.10.2017
 * Time: 20:50
 *
 * @author Pavel Shulaev (https://rover-it.me)
 */
use \Rover\AmoCRM\Model\AdditionalParam\AdvMarks;
use \Rover\AmoCRM\Model\AdditionalParam\Domain;
use \Rover\AmoCRM\Model\AdditionalParam\SiteName;
use \Rover\AmoCRM\Model\AdditionalParam\PageUrl;
use \Rover\AmoCRM\Model\AdditionalParam\Ip;
use \Rover\AmoCRM\Model\AdditionalParam\VisitorUid;

$MESS['rover-acrm__' . AdvMarks::getTemplate() . '-label']  = '��� ����� ��������� ��������';
$MESS['rover-acrm__' . AdvMarks::getTemplate() . '-help']   = '��� ��������� ����� ��������� �������� � ��������� � ���� <code>mark1=value1&mark2=value2&...</code>';
$MESS['rover-acrm__' . Domain::getTemplate() . '-label']    = '����� �����';
//$MESS['rover-acrm__' . Domain::getTemplate() . '-help']   = '����� �����, �� ������� ��������� �������';
$MESS['rover-acrm__' . SiteName::getTemplate() . '-label']  = '�������� �����';
//$MESS['rover-acrm__' . SiteName::getTemplate() . '-help'] = '�������� �����, �� ������� ��������� �������';
$MESS['rover-acrm__' . PageUrl::getTemplate() . '-label']   = '����� ��������';
//$MESS['rover-acrm__' . PageUrl::getTemplate() . '-help']  = 'url ��������, �� ������� ��������� �������';
$MESS['rover-acrm__' . Ip::getTemplate() . '-label']        = 'ip-�����';
//$MESS['rover-acrm__' . Ip::getTemplate() . '-help']       = 'ip ����� �������, �� ������� ��������� �������';
$MESS['rover-acrm__' . VisitorUid::getTemplate() . '-label']= '������������� ����������';
$MESS['rover-acrm__' . VisitorUid::getTemplate() . '-help'] = '��������� � <a href="https://www.amocrm.ru/developers/content/digital_pipeline/site_visit" target="_blank">������������ amoCRM</a>';