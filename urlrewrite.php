<?php
$arUrlRewrite=array (
  134 => 
  array (
    'CONDITION' => '#^/online/([\\.\\-0-9a-zA-Z]+)(/?)([^/]*)#',
    'RULE' => 'alias=$1',
    'ID' => NULL,
    'PATH' => '/desktop_app/router.php',
    'SORT' => 100,
  ),
  154 => 
  array (
    'CONDITION' => '#^ /catalog/([^/]+?)/\\??(.*)#',
    'RULE' => 'SECTION_CODE=$1&$2',
    'ID' => 'bitrix:catalog.top',
    'PATH' => '/include/product_popup.php',
    'SORT' => 100,
  ),
  155 => 
  array (
    'CONDITION' => '#^ /catalog/(.+?)/\\??(.*)#',
    'RULE' => 'SECTION_CODE_PATH=$1&$2',
    'ID' => 'bitrix:catalog.top',
    'PATH' => '/index.php',
    'SORT' => 100,
  ),
  135 => 
  array (
    'CONDITION' => '#^/online/(/?)([^/]*)#',
    'RULE' => '',
    'ID' => NULL,
    'PATH' => '/desktop_app/router.php',
    'SORT' => 100,
  ),
  133 => 
  array (
    'CONDITION' => '#^/stssync/calendar/#',
    'RULE' => '',
    'ID' => 'bitrix:stssync.server',
    'PATH' => '/bitrix/services/stssync/calendar/index.php',
    'SORT' => 100,
  ),
  3 => 
  array (
    'CONDITION' => '#^/personal/#',
    'RULE' => '',
    'ID' => 'bitrix:sale.personal.section',
    'PATH' => '/personal/index.php',
    'SORT' => 100,
  ),
  4 => 
  array (
    'CONDITION' => '#^/products/#',
    'RULE' => '',
    'ID' => 'bitrix:catalog',
    'PATH' => '/products/index.php',
    'SORT' => 100,
  ),
  2 => 
  array (
    'CONDITION' => '#^/services/#',
    'RULE' => '',
    'ID' => 'bitrix:catalog',
    'PATH' => '/services/index.php',
    'SORT' => 100,
  ),
  137 => 
  array (
    'CONDITION' => '#^/service/#',
    'RULE' => '',
    'ID' => 'bitrix:news',
    'PATH' => '/service/index.php',
    'SORT' => 100,
  ),
  157 => 
  array (
    'CONDITION' => '#^/actions/#',
    'RULE' => '',
    'ID' => 'bitrix:news',
    'PATH' => '/actions/index.php',
    'SORT' => 100,
  ),
  158 => 
  array (
    'CONDITION' => '#^/catalog/#',
    'RULE' => '',
    'ID' => 'bitrix:catalog',
    'PATH' => '/catalog/index.php',
    'SORT' => 100,
  ),
  9 => 
  array (
    'CONDITION' => '#^/brands/#',
    'RULE' => '',
    'ID' => 'bitrix:news',
    'PATH' => '/brands/index.php',
    'SORT' => 100,
  ),
  147 => 
  array (
    'CONDITION' => '#^/spares/#',
    'RULE' => '',
    'ID' => 'bitrix:catalog',
    'PATH' => '/spares/index.php',
    'SORT' => 100,
  ),
  136 => 
  array (
    'CONDITION' => '#^/rest/#',
    'RULE' => '',
    'ID' => NULL,
    'PATH' => '/bitrix/services/rest/index.php',
    'SORT' => 100,
  ),
  142 => 
  array (
    'CONDITION' => '#^/news/#',
    'RULE' => '',
    'ID' => 'bitrix:news',
    'PATH' => '/news/index.php',
    'SORT' => 100,
  ),
);
