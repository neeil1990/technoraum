<?php
use \Rover\AmoCRM\Entity\Source;

$MESS['rover-acrm__connected']          = 'есть подключение';
$MESS['rover-acrm__disconnected']       = 'нет подключения';
$MESS['rover-acrm__account-id']         = 'аккаунт #id#';

$MESS['rover-acrm__preset-type-' . Source::TYPE__EVENT]       = 'почтовое событие';
$MESS['rover-acrm__preset-type-' . Source::TYPE__FORM]        = 'веб-форма';

$MESS['rover-acrm__preset-' . Source::TYPE__FORM . '-exists'] = 'Веб-форма уже подключена';
$MESS['rover-acrm__preset-' . Source::TYPE__EVENT . '-exists']= 'Почтовое событие уже подключено';