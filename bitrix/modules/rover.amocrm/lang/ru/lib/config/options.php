<?php
/**
 * Created by PhpStorm.
 * User: lenovo
 * Date: 10.02.2016
 * Time: 0:50
 *
 * @author Pavel Shulaev (https://rover-it.me)
 */
use \Rover\AmoCRM\Entity\Source;
use \Rover\AmoCRM\Config\Tabs;

$MESS['rover-acrm__preset-add-success'] = '����������� ������� �������';
$MESS['rover-acrm__preset-del-success'] = '����������� �������';



$MESS[Tabs::INPUT__UNSORTED_NAME . '_default_' . Source::TYPE__FORM]  = '�������������� �� ����� �#NAME#�';
$MESS[Tabs::INPUT__UNSORTED_NAME . '_default_' . Source::TYPE__EVENT] = '�������������� �� ��������� ������� �#NAME#�';
$MESS[Tabs::INPUT__UNSORTED_NAME . '_help_' . Source::TYPE__FORM]     = '�������� <abbr title="#legend#">����������� ������������</abbr>, � ��� �� #NAME# - �������� ������� ���-�����<br>���� ���� ������, ������� �������� ��-���������';
$MESS[Tabs::INPUT__UNSORTED_NAME . '_help_' . Source::TYPE__EVENT]    = '�������� <abbr title="#legend#">����������� ������������</abbr>, � ��� �� #NAME# - �������� �������� ��������� �������<br>���� �������� ������, ������� �������� ��-���������';

$MESS[Tabs::INPUT__LEAD_NAME . '_default_' . Source::TYPE__FORM]  = '������ �� ����� �#FORM_NAME#�';
$MESS[Tabs::INPUT__LEAD_NAME . '_default_' . Source::TYPE__EVENT] = '������ �� ��������� ������� �#EVENT_NAME#�';
$MESS[Tabs::INPUT__LEAD_NAME . '_help_' . Source::TYPE__FORM]     = '�������� <abbr title="#legend#">����������� ������������</abbr>, � ��� �� #FORM_NAME# - �������� ������� ���-�����<br>���� ���� ������, ������� �������� ��-���������';
$MESS[Tabs::INPUT__LEAD_NAME . '_help_' . Source::TYPE__EVENT]    = '�������� <abbr title="#legend#">����������� ������������</abbr>, � ��� �� #EVENT_NAME# - �������� �������� ��������� �������<br>���� �������� ������, ������� �������� ��-���������';