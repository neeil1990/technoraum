<?
$MESS["AG_GM_TAB1_DESC"] = "Настройка соответствия полей";
$MESS["AG_GM_TAB2_TITLE"] = "Цены и отбор товаров";
$MESS["AG_GM_TAB2_DESC"] = "Настройка выгрузки цен";

$MESS["AG_GM_PRICE_FILTER"] = "Фильтр по цене";
$MESS["AG_GM_PRICE_FILTER_ANNOTATION"] = "Фильтр работает конечной ценой включающей скидку. В качестве фильтра могут быть указаны только целые числа";
$MESS["AG_GM_PRICE_FILTER_INCLUDE_TEXT"] = "Включить в прайс товары с ценой";
$MESS["AG_GM_PRICE_FILTER_INCLUDE_TEXT_FROM"] = "от";
$MESS["AG_GM_PRICE_FILTER_INCLUDE_TEXT_TO"] = "до";

$MESS["AG_GM_PRICES"] = "Выгружаемая цена";
$MESS["AG_GM_PRICE_TYPE"] = "Вывести в фиде цену";
$MESS["AG_GM_PRICE_ANNOTATION"] = "При выборе оптимальной цены учитываются цены доступные для группы Все пользователи (включая неавторизованных). При выборе конкретной цены, флаг доступности для группы Все пользователи (включая неавторизованных) не проверяется. Рекомендую <b>не менять данную опцию без необходимости</b> и выводить оптимальную цену.
<br/>
<br/>
Товары без цены в фид не добавляются
";

$MESS["AG_GM_CURRENCY"] = "Конвертация в валюту";
$MESS["AG_GM_CURRENCY_CONVERT"] = "Конвертировать цену в";
$MESS["AG_GM_CURRENCY_CONVERT_NO_CONVERT"] = "Не конвертировать";
$MESS["AG_GM_CURRENCY_CONVERT_SITE_CURRENCY"] = "Валюта из настроек сайта";




$MESS["GOOGLE_FILTER_ELEMENTS"] = "Отбор товаров по свойствам";
$MESS["GOOGLE_FILTER_ELEMENTS_EMPTY"] = "Выберите свойство";
$MESS["GOOGLE_FILTER_SYMBOL_EMPTY"] = "Пусто";
$MESS["GOOGLE_FILTER_SYMBOL_NOEMPTY"] = "Не пусто";
$MESS["GOOGLE_FILTER_SYMBOL_EQUALL"] = "Равно";
$MESS["GOOGLE_FILTER_SYMBOL_NOEQUALL"] = "Не равно";
$MESS["GOOGLE_FILTER_SYMBOL_MORE"] = "Больше";
$MESS["GOOGLE_FILTER_SYMBOL_LESS"] = "Меньше";
$MESS["GOOGLE_FILTER_SYMBOL_LIKE"] = "Входит";
$MESS["GOOGLE_FILTER_SYMBOL_OR"] = "или";
$MESS["YANDEX_FILTER_DESCRIPTION_TITLE"] = "Памятка по работе с отбором товаров";
$MESS["GOOGLE_FILTER_PROPERTY_DIRECTORY"] = "справочник";
$MESS["GOOGLE_FILTER_PROPERTY_NUMBER"] = "число";
$MESS["GOOGLE_FILTER_PROPERTY_IB_ELEMENT"] = "привязка к инфоблоку";
$MESS["YANDEX_FILTER_DESCRIPTION"] = "
<b>Типы свойств:</b>
<div>1. Если свойство типа 'Справочник' - в значении указывайте UF_XML_ID элемента справочника</div>
<div>2. Если свойство типа 'Привязка к элементам' - в значении указывайте название элемента</div><br>
<b>Знаки:</b>
<div>1. Знаки 'Больше' и 'Меньше' рекомендуется использовать только для свойств типа 'Число', при использовании 'Больше' или 'Меньше' логика или не работает, поэтому берется значение первого поля</div>
<div>2. Знак 'Входит' является эквивалентом sql like %value%. Выбираются все товары, имеющие в значении свойства указанную подстроку. Рекомендуется использовать только для свойств типа Строка</div>
";
$MESS["GOOGLE_URL_UTMS"] = "Настройка UTM меток";


// _run.php
$MESS["GOOGLE_PRODUCT_TYPE_MAIL_PAGE"] = "Главная > ";
$MESS["GOOGLE_EXPORT_ERROR_NO_AVIAL_PRICE"] = "Ошибка. Нет доступных цен. Хотя бы одна цена должна быть доступна для всех групп пользователей включая не авторизованных";

// google xml props subtitle
$MESS["YANDEX_PROP_UTM_TITLE"] = "UTM-метки";
$MESS["YANDEX_PROP_CUSTOM_LABEL_TITLE"] = "Метки продавца";
$MESS["YANDEX_PROP_WEIGHT_PARAM"] = "Параметры товара";

// google xml props
$MESS["YANDEX_PROP_title"] = "Название для простых товаров";
$MESS["YANDEX_PROP_title_EMPTY"] = "Название элемента";
$MESS["YANDEX_PROP_title_sku"] = "Название для торговых предложений";
$MESS["YANDEX_PROP_title_sku_EMPTY"] = "Название элемента";
$MESS["YANDEX_PROP_description"] = "Описание для простых товаров";
$MESS["YANDEX_PROP_description_EMPTY"] = "Детальное описание товара";
$MESS["YANDEX_PROP_description_sku"] = "Описание для торговых предложений";
$MESS["YANDEX_PROP_description_sku_EMPTY"] = "Детальное описание торгового предложения";
$MESS["YANDEX_PROP_condition"] = "Состояние";
$MESS["YANDEX_PROP_condition_EMPTY"] = "New";
$MESS["YANDEX_PROP_product_type"] = "Тип товара";
$MESS["YANDEX_PROP_product_type_EMPTY"] = "Путь разделов. Пример: Главная > Платья";
$MESS["YANDEX_PROP_additional_image_link"] = "Дополнительные картинки";
$MESS["YANDEX_PROP_brand"] = "Марка товара";
$MESS["YANDEX_PROP_gtin"] = "Код международной маркировки";
$MESS["YANDEX_PROP_gtin_sku"] = "Код международной маркировки для торговых предложений";
$MESS["YANDEX_PROP_mpn"] = "Код производителя товара";
$MESS["YANDEX_PROP_mobile_link"] = "Ссылка для мобильных устройств";
$MESS["YANDEX_PROP_color"] = "Цвет товара";
$MESS["YANDEX_PROP_gender"] = "Пол людей, для которых предназначен товар";
$MESS["YANDEX_PROP_age_group"] = "Возрастная группа";
$MESS["YANDEX_PROP_material"] = "Материал, из которого изготовлен товар";
$MESS["YANDEX_PROP_pattern"] = "Узор или рисунок на товаре";
$MESS["YANDEX_PROP_size"] = "Размер товара";
$MESS["YANDEX_PROP_size_alternative_1"] = "Альтернативное поле размер товара";
$MESS["YANDEX_PROP_size_alternative_2"] = "Альтернативное поле размер товара";
$MESS["YANDEX_PROP_size_alternative_3"] = "Альтернативное поле размер товара";
$MESS["YANDEX_PROP_size_alternative_4"] = "Альтернативное поле размер товара";
$MESS["YANDEX_PROP_cost_of_goods_sold"] = "Себестоимость (валюта берется из цены)";
$MESS["YANDEX_PROP_shipping_weight"] = "Вес брутто";
$MESS["YANDEX_PROP_unit_pricing_measure"] = "Количество товара";
$MESS["YANDEX_PROP_unit_pricing_base_measure"] = "Единица измерения товара";
$MESS["YANDEX_PROP_shipping_length"] = "Длина посылки";
$MESS["YANDEX_PROP_shipping_width"] = "Ширина посылки";
$MESS["YANDEX_PROP_shipping_height"] = "Высота посылки";
$MESS["YANDEX_PROP_custom_label_0"] = "Метка продавца 0";
$MESS["YANDEX_PROP_custom_label_1"] = "Метка продавца 1";
$MESS["YANDEX_PROP_custom_label_2"] = "Метка продавца 2";
$MESS["YANDEX_PROP_custom_label_3"] = "Метка продавца 3";
$MESS["YANDEX_PROP_custom_label_4"] = "Метка продавца 4";

$MESS["YANDEX_PROP_utm_source"] = "Метка utm_source";
$MESS["YANDEX_PROP_utm_medium"] = "Метка utm_medium";
$MESS["YANDEX_PROP_utm_campaign"] = "Метка utm_campaign";
$MESS["YANDEX_PROP_utm_content"] = "Метка utm_content";
$MESS["YANDEX_PROP_utm_term"] = "Метка utm_term";

// default bitrix fields
$MESS["GOOGLE_SALE_FIELDS"] = "--- стандартные поля товаров ---";
$MESS["GOOGLE_SALE_FIELD_CATALOG_WEIGHT"] = "Вес единицы товара в граммах";
$MESS["GOOGLE_SALE_FIELD_CATALOG_LENGTH"] = "Длина товара (в мм)";
$MESS["GOOGLE_SALE_FIELD_CATALOG_HEIGHT"] = "Высота товара (в мм)";
$MESS["GOOGLE_SALE_FIELD_CATALOG_WIDTH"] = "Ширина товара (в мм)";

$MESS["GOOGLE_SALE_FIELD_NAME_MAIN"] = "Название элемента товара";
$MESS["GOOGLE_SALE_FIELD_IBLOCK_SECTION_ID_MAIN"] = "ID родительского раздела";
$MESS["GOOGLE_SALE_FIELD_IBLOCK_SECTION_NAME_MAIN"] = "Название родительского раздела";
$MESS["GOOGLE_SALE_FIELD_PREVIEW_TEXT_MAIN"] = "Превью описание товара";
$MESS["GOOGLE_SALE_FIELD_DETAIL_TEXT_MAIN"] = "Детальное описание товара";
$MESS["GOOGLE_SALE_FIELD_NAME_SKU"] = "Название элемента торгового предложения";
$MESS["GOOGLE_SALE_FIELD_PREVIEW_TEXT_SKU"] = "Превью описание торгового предложения";
$MESS["GOOGLE_SALE_FIELD_DETAIL_TEXT_SKU"] = "Детальное описание торгового предложения";

$MESS["GOOGLE_SALE_FIELD_TEXT_FIELD"] = "Произвольное значение из поля ввода";

$MESS["GOOGLE_SALE_SKU_FIELDS"] = "--- стандартные поля торговых предложений ---";


// other
$MESS["YANDEX_DETAIL_TITLE"] = "Настройка дополнительных параметров";
$MESS["YANDEX_PROP_google_product_category"] = "Категория товара в google";
$MESS["YANDEX_ERR_NO_ACCESS_EXPORT"] = "Нет доступа к управлению экспортом";
$MESS["YANDEX_ERR_NO_IBLOCK_CHOSEN"] = "Не выбран инфоблок";
$MESS["YANDEX_ERR_NO_IBLOCK_FOUND"] = "Инфоблок не найден";
$MESS["YANDEX_ERR_NO_ACCESS_IBLOCK"] = "Нет доступа к инфоблоку";
$MESS["YANDEX_TAB1_TITLE"] = "Настройка выгрузки";
$MESS["YANDEX_SKIP_PROP"] = "(не выводить)";

$MESS["YANDEX_TYPE"] = "Тип описания";
$MESS["YANDEX_TYPE_SIMPLE"] = "standart";
$MESS["YANDEX_TYPE_NOTE"] = "Подробнее см. <a href=\"https://yandex.ru/support/market-tech-requirements/\" target=\"_blank\">Требования к формату и методу передачи данных о товарных предложениях</a>";
$MESS["YANDEX_PROPS_COMMON"] = "Общие настройки";
$MESS["YANDEX_PROPS_TYPE"] = "Связь полей в выгрузке и свойств";
$MESS["YANDEX_PROPS_NO"] = "нет";
$MESS["YANDEX_PROPS_ADDITIONAL"] = "Дополнительные свойства для выгрузки";
$MESS["YANDEX_PROPS_ADDITIONAL_TITLE"] = "Выберите свойства";
$MESS["YANDEX_PROPS_ADDITIONAL_MORE"] = "Еще";
$MESS["YANDEX_PRICES"] = "Цены";
$MESS["YANDEX_PRICE_TYPE"] = "Выводить цену";
$MESS["YANDEX_PRICE_TYPE_NONE"] = "оптимальную";
$MESS["YANDEX_CURRENCIES"] = "Валюты";
$MESS["YANDEX_CURRENCY"] = "валюта";
$MESS["YANDEX_CURRENCY_RATE"] = "курс";
$MESS["YANDEX_CURRENCY_RATE_SITE"] = "сайт";
$MESS["YANDEX_CURRENCY_RATE_CBRF"] = "Центральный банк РФ";
$MESS["YANDEX_CURRENCY_RATE_NBU"] = "Национальный банк Украины";
$MESS["YANDEX_CURRENCY_RATE_NBK"] = "Национальный банк Казахстана";
$MESS["YANDEX_CURRENCY_RATE_CB"] = "банк своего региона";
$MESS["YANDEX_CURRENCY_PLUS"] = "коррекция курса";
$MESS["YANDEX_PARAMS_TITLE"] = "Свойство";
$MESS["YANDEX_PARAMS_TITLE_VALUE"] = "Значение";
$MESS["YANDEX_PARAMS_SYMBOL"] = "Знак";
$MESS["YANDEX_ERR_BAD_MODULE"] = "Демо-период модуля arturgolubev.gmerchant закончился, для продолжения работы необходимо приобрести полную версию модуля.";
$MESS["YANDEX_ERR_BAD_XML_DATA"] = "Неверный формат детальных настроек экспорта";
$MESS["YANDEX_ERR_NO_ACCESS_IBLOCK_SKU"] = "Нет доступа к инфоблоку торговых предложений";
$MESS["YANDEX_ERR_NO_IBLOCK_SKU_FOUND"] = "Инфоблок торговых предложений #ID# не найден";
$MESS["YANDEX_ERR_NO_IBLOCK_FOUND_EXT"] = "Инфоблок #ID# не найден";
$MESS["YANDEX_ERR_NO_IBLOCK_IS_CATALOG"] = "Инфоблок #ID# не является торговым каталогом и не имеет торговых предложений";
$MESS["YANDEX_ERR_NO_SECTION_LIST"] = "Список разделов не задан";
$MESS["YANDEX_ERR_FILE_ACCESS_DENIED"] = "Недостаточно прав для перезаписи файла #FILE#";
$MESS["YANDEX_ERR_FILE_OPEN_WRITING"] = "Невозможно открыть файл #FILE# для записи";
$MESS["YANDEX_ERR_SETUP_FILE_WRITE"] = "Запись в файл #FILE# невозможна";
$MESS["YANDEX_PRODUCT_PROPS"] = "--- Свойства инфоблока товаров ---";
$MESS["YANDEX_OFFERS_PROPS"] = "--- Свойства инфоблока торговых предложений ---";
$MESS["YANDEX_VAT_SETTINGS"] = "Выгрузка НДС";
$MESS["YANDEX_VAT_ATTENTION"] = "Внимание! Выгрузка ставок НДС для товаров необходима только субъектам РФ, работающим по модели CPA с включенной предоплатой на Маркете. Могут быть выгружены следующие ставки НДС: 0, 10%, 18%, НДС отсутствует.";
$MESS["YANDEX_USE_VAT_EXPORT"] = "Выгружать ставки НДС для товаров";
$MESS["YANDEX_BASE_VAT"] = "Базовая ставка на основной ассортимент";
$MESS["YANDEX_BASE_VAT_ABSENT"] = "не задана";
$MESS["YANDEX_BASE_VAT_EMPTY"] = "НДС не облагается";
$MESS["YANDEX_VAT_ERR_BASE_VAT_ABSENT"] = "Не указано значение ставки НДС";
$MESS["YANDEX_SKU_SETTINGS"] = "Настройка выгрузки торговых предложений";
$MESS["YANDEX_OFFERS_SELECT"] = "Условие отбора";
$MESS["YANDEX_SKU_EXPORT_ALL_TITLE"] = "Все предложения товара";
$MESS["YANDEX_SKU_EXPORT_MIN_PRICE_TITLE"] = "Предложение с минимальной ценой";
$MESS["YANDEX_SKU_EXPORT_PROP_TITLE"] = "Отбор по свойству";
$MESS["YANDEX_SKU_EXPORT_PROP_ID"] = "Свойство";
$MESS["YANDEX_SKU_EXPORT_PROP_COND"] = "Условие отбора";
$MESS["YANDEX_SKU_EXPORT_PROP_VALUE"] = "Значения";
$MESS["YANDEX_SKU_EXPORT_PROP_EMPTY"] = "--- выберите свойство ---";
$MESS["YANDEX_SKU_EXPORT_PROP_SELECT_ZERO"] = "пусто";
$MESS["YANDEX_SKU_EXPORT_PROP_SELECT_NONZERO"] = "не пусто";
$MESS["YANDEX_SKU_EXPORT_PROP_SELECT_EQUAL"] = "равно";
$MESS["YANDEX_SKU_EXPORT_PROP_SELECT_NONEQUAL"] = "не равно";
$MESS["YANDEX_SKU_EXPORT_ERR_CONDITION_ABSENT"] = "Не указано, по какому принципу фильтровать экспортируемые торговые предложения";
$MESS["YANDEX_SKU_EXPORT_ERR_PROPERTY_ABSENT"] = "Не указано свойство, по значению которого фильтруются торговые предложения";
$MESS["YANDEX_SKU_EXPORT_ERR_PROPERTY_COND_ABSENT"] = "Не указано условие фильтрации торговых предложений по свойству";
$MESS["YANDEX_SKU_EXPORT_ERR_PROPERTY_VALUES_ABSENT"] = "Не указаны значения свойств для фильтрации торговых предложений";
$MESS["YANDEX_SAVE_ERR"] = "Ошибки сохранения";
$MESS["YANDEX_ERR_BAD_PRICE_TYPE"] = "Задан неверный тип цен для выгрузки";
$MESS["YANDEX_ERR_BAD_OFFERS_IBLOCK_ID"] = "Неверный ID инфоблока торговых предложений";
$MESS["YANDEX_ERR_SKU_SETTINGS_ABSENT"] = "Отсутствуют настройки экспорта торговых предложений";
$MESS["YANDEX_ROOT_DIRECTORY"] = "Основной раздел каталога";
$MESS["CET_ERROR_IBLOCK_PERM"] = "Недостаточно прав для работы с инфоблоком ##IBLOCK_ID#";
$MESS["CES_ERROR_BAD_EXPORT_FILENAME"] = "Имя файла экспорта содержит запрещенные символы";
$MESS["YANDEX_BASE_CURRENCY"] = "Валюта, в которую конвертировать цены товаров";
$MESS["YANDEX_STEP_ERR_DATA_FILE_NOT_READ"] = "Невозможно открыть файл с данными товаров - экспорт не может быть завершен";
?>