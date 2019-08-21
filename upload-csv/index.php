<?
require($_SERVER["DOCUMENT_ROOT"]."/bitrix/header.php");
$APPLICATION->SetTitle("Загрузка CSV");
global $USER;
if(!$USER->IsAdmin())
    LocalRedirect("/");

if($_REQUEST['import_file'] && $_REQUEST['IBLOCK_ID']):

 $base_columns = [];
 $import_files_dir = 'files/'; // Временная папка
 $import_file = ($_REQUEST['import_file']) ? $_REQUEST['import_file'] : 'import.csv';           // Временный файл
 $columns = [];
 $column_delimiter = ';';

$f = fopen($import_files_dir.$import_file, 'r');
$columns = fgetcsv($f, null, $column_delimiter);

$arResult = [];
for($k=0; !feof($f); $k++)
{
    // Читаем строку
    $line = fgetcsv($f, 0, $column_delimiter);
    $product = null;

    if(is_array($line))
    foreach($columns as $i => $col)
    {
        if(!$i)
            continue;

        if(isset($line[$i]) && !empty($line) && !empty($col))
            $arResult[$line[0]][$col] = $line[$i];
    }
}
fclose($f);

foreach($arResult as $art => $value){

    $arSelect = Array("ID", "IBLOCK_ID", "NAME", "PRICE_TYPE","PROPERTY_*");
    $arFilter = Array("IBLOCK_ID" => $_REQUEST['IBLOCK_ID'], "=CODE" => $art);
    $res = CIBlockElement::GetList(Array(), $arFilter, false, false, $arSelect);
    if($ob = $res->GetNextElement()){
        $arFields = $ob->GetFields();
        $arProps = $ob->GetProperties();

        if($value['Цена']){
            $price = CPrice::GetBasePrice($arFields['ID']);
            CPrice::Update($price['ID'], array("PRICE" => floatval(str_replace(array(',',' '), array('.',''), $value['Цена']))));
        }

        if(is_array($value)){
            $arPropSet = [];
            foreach ($value as $desc => $val){
                if($desc != 'Цена')
                    $arPropSet[] = ['VALUE' => $val,'DESCRIPTION' => $desc];
            }
            CIBlockElement::SetPropertyValuesEx($arFields['ID'], $arFilter['IBLOCK_ID'], array('CML2_ATTRIBUTES' => $arPropSet));
        }
    }
}

LocalRedirect("/upload-csv/?rec=end");
endif;
?>

    <?if ($_REQUEST['rec'] == 'end'):?>
    <div class="row">
        <div class="col-md-6">
            <div class="alert alert-success alert-dismissible" role="alert">
                <button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                <strong>Запись завершена!</strong>
            </div>
        </div>
    </div>
    <?endif;?>

    <div class="col-md-6">

        <pre>
            Поля: Артикул; Цена; Длина; Ширина; Высота; Вес
            Путь к папке файлов: /upload-csv/files/
        </pre>

        <form class="form-horizontal" action="">
            <div class="form-group">
                <label for="inputEmail3" class="col-sm-2 control-label">Файл</label>
                <div class="col-sm-10">
                    <input type="text" class="form-control" name="import_file" value="import.csv">
                </div>
            </div>
            <div class="form-group">
                <label for="inputPassword3" class="col-sm-2 control-label">Инфоблок</label>
                <div class="col-sm-10">
                    <input type="number" class="form-control" name="IBLOCK_ID" value="16">
                </div>
            </div>
            <div class="form-group">
                <div class="col-sm-offset-2 col-sm-10">
                    <button type="submit" class="btn btn-default">Загрузить</button>
                </div>
            </div>
        </form>


    </div>



<?require($_SERVER["DOCUMENT_ROOT"]."/bitrix/footer.php");?>