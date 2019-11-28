<? if ( !defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true){
    die();
}

use Dwstroy\Pricechanger\ConditionTable;
use Dwstroy\Pricechanger\RuntimeTable;
use Dwstroy\Pricechanger\Functions\Fabric;
use Dwstroy\Pricechanger\PriceChanger;
use Bitrix\Main\Loader;

class CPriceChanger{
    private $arCond             = array ();
    private $offersIblockSearch = false;
    private $offersIblock        = array();
    private $catalogIblock        = array();
    private $fields             = array ();
    private $props              = array ();
    private $price              = array ();
    private $purchasePrice      = array ();
    private $prodfields         = array ();

    private $operation
        = array (
            'Plus'     => '+',
            'Minus'    => '-',
            'Multiply' => '*',
            'Divide'   => '/',
        );

    private $aliasses
        = array (
            'CondIBSection'               => 'SECTION_ID',
            'CondIBSectionWithSubsection' => 'SECTION_ID',
            'CondIBIBlock'                => 'IBLOCK_ID',
            'CondIBElement'               => 'ID',
            'CondIBCode'                  => 'CODE',
            'CondIBXmlID'                 => 'XML_ID',
            'CondIBName'                  => 'NAME',
            'CondIBActive'                => 'ACTIVE',
            'CondIBDateActiveFrom'        => 'DATE_ACTIVE_FROM',
            'CondIBDateActiveTo'          => 'DATE_ACTIVE_TO',
            'CondIBSort'                  => 'SORT',
            'CondIBPreviewText'           => 'PREVIEW_TEXT',
            'CondIBDetailText'            => 'DETAIL_TEXT',
            'CondIBDateCreate'            => 'DATE_CREATE',
            'CondIBCreatedBy'             => 'CREATED_BY',
            'CondIBTimestampX'            => 'TIMESTAMP_X',
            'CondIBModifiedBy'            => 'MODIFIED_BY',
            'CondIBTags'                  => 'TAGS',
        );

    private $aliassesCat
        = array (
            'CondCatQuantity'    => 'CATALOG_QUANTITY',
            'CondCatWeight'      => 'CATALOG_WEIGHT',
            'CondCatVatID'       => 'CATALOG_VAT_ID',
            'CondCatHeight'      => 'CATALOG_HEIGHT',
            'CondCatLength'      => 'CATALOG_LENGTH',
            'CondCatWidth'       => 'CATALOG_WIDTH',
            'CondCatVatIncluded' => 'CATALOG_VAT_INCLUDED',
        );

    private $aliassesDate
        = array (
            'CondIBDateActiveFrom'        => 'DATE_ACTIVE_FROM',
            'CondIBDateActiveTo'          => 'DATE_ACTIVE_TO',
            'CondIBTimestampX'            => 'TIMESTAMP_X',
        );

    private $specificLogic
        = array (
            'Contain',
            'NotCont',
        );

    function getCnt(){
        if ( !empty($this->arCond)){
            return $this->arCond[ 'COUNT' ];
        }
    }

    public function get($condId){
        $filter = array (
            '=ACTIVE' => 'Y',
            '=ID'     => $condId
        );
        $order = array ('SORT' => 'desc');
        $result = ConditionTable::getList(
            array (
                'select' => array ('RULE', 'ACTIONS', 'COUNT', 'SITES'),
                'filter' => $filter,
                'order'  => $order,
            )
        );

        if ($row = $result->fetch()){
            $sites = unserialize($row[ 'SITES' ]);

            //if (in_array(SITE_ID, $sites)){
            $this->arCond = $row;
            //}
        }
    }


    function getLogic($val, $prev_true, $cur_true){
        $str = (($prev_true xor $cur_true) ? '!' : '');
        switch ($val){
            case 'Equal':
                return $str . '=';
            case 'Not':
                return (($prev_true xor $cur_true) ? '=' : '!');
            case 'Great':
                return $str . '>';
            case 'Less':
                return $str . '<';
            case 'EqGr':
                return $str . '>=';
            case 'EqLs':
                return $str . '<=';
        }
    }

    function getSpecLogic($logic, $field, $val, $prev_true, $cur_true){
        switch ($logic){
            case 'Contain':
                return array (
                    (($prev_true xor $cur_true) ? '!' : '') . $field => '%' . $val . '%'
                );
                break;
            case 'NotCont':
                return array (
                    (($prev_true xor $cur_true) ? '' : '!') . $field => '%' . $val . '%'
                );
                break;
        }
    }

    function getSectIDSubSectId($id){
        Loader::includeModule('iblock');
        $arSects = array ();
        if ($id){
            $rsParentSection = CIBlockSection::GetByID($id);
            if ($arParentSection = $rsParentSection->GetNext()){
                $arSects[ ] = $arParentSection[ 'ID' ];
                $arFilter = array (
                    'IBLOCK_ID'     => $arParentSection[ 'IBLOCK_ID' ],
                    '>LEFT_MARGIN'  => $arParentSection[ 'LEFT_MARGIN' ],
                    '<RIGHT_MARGIN' => $arParentSection[ 'RIGHT_MARGIN' ],
                    '>DEPTH_LEVEL'  => $arParentSection[ 'DEPTH_LEVEL' ]
                ); // выберет потомков без учета активности
                $rsSect = CIBlockSection::GetList(array ('left_margin' => 'asc'), $arFilter);
                while ($arSect = $rsSect->GetNext()){
                    $arSects[ ] = $arSect[ 'ID' ];
                }
            }
        }

        return $arSects;
    }

    function proccessChilds($filter_wo_cat_and_price, $filter, $offer_filter, $finded_offer_prop, $children, $prev_true, $cur_true){
        foreach ($children as $child){
            if ($child[ 'CLASS_ID' ] != 'CondGroup'){
                $tmp = explode(':', $child[ 'CLASS_ID' ]);

                if (isset($this->aliasses[ $tmp[ 0 ] ])){

                    if( isset($this->aliassesDate[ $tmp[ 0 ] ]) ){
                        $date = new DateTime();
                        $date->setTimestamp($child[ 'DATA' ][ 'value' ]);
                        $child[ 'DATA' ][ 'value' ] = $date->format('d.m.Y H:i:s');
                    }

                      if ($tmp[ 0 ] == 'CondIBSectionWithSubsection'){
                            $child[ 'DATA' ][ 'value' ] = $this->getSectIDSubSectId($child[ 'DATA' ][ 'value' ]);
                      }


                    if (in_array($child[ 'DATA' ][ 'logic' ], $this->specificLogic)){
                        $f1 = $this->getSpecLogic($child[ 'DATA' ][ 'logic' ], $this->aliasses[ $tmp[ 0 ] ], $child[ 'DATA' ][ 'value' ], $prev_true, $cur_true);
                    }else{
                        $f1 = array (
                            $this->getLogic($child[ 'DATA' ][ 'logic' ], $prev_true, $cur_true) . $this->aliasses[ $tmp[ 0 ] ] => $child[ 'DATA' ][ 'value' ]
                        );
                    }

                    $filter_wo_cat_and_price[] = $f1;
                    $filter[] = $f1;

                }elseif ($this->aliassesCat[ $tmp[ 0 ] ]){

                    if (in_array($child[ 'DATA' ][ 'logic' ], $this->specificLogic)){
                        $f1 = $this->getSpecLogic($child[ 'DATA' ][ 'logic' ], $this->aliassesCat[ $tmp[ 0 ] ], $child[ 'DATA' ][ 'value' ], $prev_true, $cur_true);
                    }else{
                        $f1 = array (
                            $this->getLogic($child[ 'DATA' ][ 'logic' ], $prev_true, $cur_true) . $this->aliassesCat[ $tmp[ 0 ] ] => $child[ 'DATA' ][ 'value' ]
                        );
                    }

                    $offer_filter[] = $f1;
                    $filter[] = $f1;

                }else{
                    switch ($tmp[ 0 ]){
                        case 'CondIBProp':
                            if (!empty($this->offersIblock) &&  in_array($tmp[ 1 ], $this->offersIblock) ){

                                $res = CIBlockProperty::GetByID($tmp[ 2 ]);
                                if($ar_res = $res->GetNext())
                                    if( in_array($ar_res['USER_TYPE'], array("Date", 'DateTime')) ){
                                        $date = new DateTime();
                                        $date->setTimestamp($child[ 'DATA' ][ 'value' ]);
                                        $child[ 'DATA' ][ 'value' ] = $date->format('d.m.Y H:i:s');
                                    }


                                if (in_array($child[ 'DATA' ][ 'logic' ], $this->specificLogic)){
                                    $f1 = $this->getSpecLogic($child[ 'DATA' ][ 'logic' ], 'PROPERTY_' . $tmp[ 2 ], $child[ 'DATA' ][ 'value' ], $prev_true, $cur_true);
                                }else{
                                    $f1 = array (
                                        $this->getLogic($child[ 'DATA' ][ 'logic' ], $prev_true, $cur_true) . 'PROPERTY_' . $tmp[ 2 ] => $child[ 'DATA' ][ 'value' ]
                                    );
                                }

                                $finded_offer_prop = true;
                                $offer_filter[] = $f1;
                            }else{

                                if (in_array($child[ 'DATA' ][ 'logic' ], $this->specificLogic)){
                                    $f1 = $this->getSpecLogic($child[ 'DATA' ][ 'logic' ], 'PROPERTY_' . $tmp[ 2 ], $child[ 'DATA' ][ 'value' ], $prev_true, $cur_true);
                                }else{
                                    $f1 = array (
                                        $this->getLogic($child[ 'DATA' ][ 'logic' ], $prev_true, $cur_true) . 'PROPERTY_' . $tmp[ 2 ] => $child[ 'DATA' ][ 'value' ]
                                    );
                                }
                                $filter[] = $f1;
                                $filter_wo_cat_and_price[] = $f1;
                            }
                            break;
                        case 'CondIBPrice':

                            $f1 = array (
                                $this->getLogic($child[ 'DATA' ][ 'logic' ], $prev_true, $cur_true) . 'CATALOG_PRICE_' . $tmp[ 1 ] => $child[ 'DATA' ][ 'value' ]
                            );

                            $filter[] = $f1;
                            $offer_filter[] = $f1;
                            break;
                    }
                }
            }else{
                $logic = $child[ 'DATA' ][ 'All' ];

                if ( !$cur_true){
                    if ($logic == "AND"){
                        $logic = "OR";
                    }else{
                        $logic = "AND";
                    }
                }

                $tt = $this->proccessChilds(
                    array (
                        'LOGIC' => $logic
                    ),
                    array (
                        'LOGIC' => $logic
                    ),
                    array (
                    'LOGIC' => $logic
                ), $finded_offer_prop, $child[ 'CHILDREN' ], $cur_true, ($child[ 'DATA' ][ 'True' ] == 'False' ? false : true)
                );

                $filter[] = $tt['filter'];
                $offer_filter[] = $tt['offer_filter'];
                $filter_wo_cat_and_price[] = $tt['filter_wo_cat_and_price'];
                $finded_offer_prop = $tt['finded_offer_prop'];
            }
        }

        return array(
            'filter_wo_cat_and_price' => $filter_wo_cat_and_price,
            'filter' => $filter,
            'offers_filter' => $offer_filter,
            'finded_offer_prop' => $finded_offer_prop,
        );
    }

    function proccessActionChilds($eval_str, $children){
        $childs_operation = '';
        foreach ($children as $child){
            if ($child[ 'CLASS_ID' ] != 'ActSaleSubGrp'){
                if ( !strlen($child[ 'DATA' ][ 'function' ])){
                    $child[ 'DATA' ][ 'function' ] = 'def';
                }


                $tmp = explode(':', $child[ 'CLASS_ID' ]);
                if (isset($this->aliasses[ $tmp[ 0 ] ])){
                    $eval_str .= $childs_operation . "Fabric::createInstance(" . $child[ 'DATA' ][ 'function' ] . ")->calculate" . '({' . $this->aliasses[ $tmp[ 0 ] ] . '})';

                    $this->fields[ $this->aliasses[ $tmp[ 0 ] ] ] = $this->aliasses[ $tmp[ 0 ] ];
                }elseif ($this->aliassesCat[ $tmp[ 0 ] ]){
                    $eval_str .= $childs_operation . "Fabric::createInstance(" . $child[ 'DATA' ][ 'function' ] . ")->calculate" . '({' . $this->aliassesCat[ $tmp[ 0 ] ] . '})';

                    $cc = str_replace("CATALOG_", '', $this->aliassesCat[ $tmp[ 0 ] ]);


                    $this->prodfields[ $cc ] = $cc;
                }else{
                    switch ($tmp[ 0 ]){
                        case 'CustomValue':
                            $eval_str .= $childs_operation . "Fabric::createInstance(" . $child[ 'DATA' ][ 'function' ] . ")->calculate" . '(' . intval($child[ 'DATA' ][ 'value' ]) . ')';

                            break;
                        case 'CondIBProp':
                            $eval_str .= $childs_operation . "Fabric::createInstance(" . $child[ 'DATA' ][ 'function' ] . ")->calculate" . '({PROPERTY_' . $tmp[ 1 ] . '_' . $tmp[ 2 ] . '})';

                            $this->props[ $tmp[ 1 ] ][ $tmp[ 2 ] ] = $tmp[ 2 ];
                            break;
                        case 'CondIBPrice':
                            if ($tmp[ 1 ] == 'Purchase'){
                                $this->purchasePrice[ ] = $tmp[ 1 ];

                                $eval_str .= $childs_operation . "Fabric::createInstance(" . $child[ 'DATA' ][ 'function' ] . ")->calculate" . '({PURCHASE_PRICE})';
                            }else{
                                $this->price[ $tmp[ 1 ] ] = $tmp[ 1 ];

                                $eval_str .= $childs_operation . "Fabric::createInstance(" . $child[ 'DATA' ][ 'function' ] . ")->calculate" . '({CATALOG_PRICE_' . $tmp[ 1 ] . '})';
                            }
                            break;
                    }
                }
                $childs_operation = $this->operation[ $child[ 'DATA' ][ 'logic' ] ];
            }else{
                if (strlen($child[ 'DATA' ][ 'function' ])){
                    $eval_str .= $childs_operation . "Fabric::createInstance(" . $child[ 'DATA' ][ 'function' ] . ")->calculate" . '(' . $this->proccessActionChilds(
                            '', $child[ 'CHILDREN' ], $child[ 'DATA' ][ 'logic' ]
                        ) . ')';
                }else{
                    if (count($child[ 'CHILDREN' ])){
                        $eval_str .= $childs_operation . '(' . $this->proccessActionChilds(
                                '', $child[ 'CHILDREN' ], $child[ 'DATA' ][ 'logic' ]
                            ) . ')';
                    }
                }

                $childs_operation = $this->operation[ $child[ 'DATA' ][ 'logic' ] ];
            }
        }

        return $eval_str;
    }

    public function prepareFilter(){

        $rsIBlocks = CCatalog::GetList(
            array (), array (), false, false, array (
                        'IBLOCK_ID',
                        'PRODUCT_IBLOCK_ID'
                    )
        );
        $data = array (
            'filter_wo_cat_and_price' => array(),
            'filter' => array(),
            'offers_filter' => array(),
            'finded_offer_prop' => false,
        );
        $this->offersIblock = array();
        $this->catalogIblock = array();
        $arIblockId = array ();
        while ($arIBlock = $rsIBlocks->Fetch()){
            if ($arIBlock[ 'PRODUCT_IBLOCK_ID' ]){
                $this->offersIblock[] = $arIBlock[ 'IBLOCK_ID' ];
            }else{
                $this->catalogIblock[] = $arIBlock[ 'IBLOCK_ID' ];
            }
            $arIblockId[ ] = $arIBlock[ 'IBLOCK_ID' ];
        }

        $rules = unserialize($this->arCond[ 'RULE' ]);
        if (count($arIblockId)){
            if ($rules[ 'CLASS_ID' ] == 'CondGroup' && count($rules[ 'CHILDREN' ])){
                $filter[ 'LOGIC' ] = $rules[ 'DATA' ][ 'All' ];
                $offers_filter[ 'LOGIC' ] = $rules[ 'DATA' ][ 'All' ];
                $filter_wo_cat_and_price[ 'LOGIC' ] = $rules[ 'DATA' ][ 'All' ];
                $finded_offer_prop = false;
                $data = $this->proccessChilds($filter_wo_cat_and_price, $filter, $offers_filter, $finded_offer_prop, $rules[ 'CHILDREN' ], true, ($rules[ 'DATA' ][ 'True' ] == 'False' ? false : true));
            }
        }

        if( !empty( $this->offersIblock ) )
            $data['offers_filter'] = array_merge($data['offers_filter'], array (array ('IBLOCK_ID' => $this->offersIblock)));

        if( !empty( $this->catalogIblock ) ){
            $data['filter_wo_cat_and_price'] = array_merge(array($data['filter_wo_cat_and_price']), array (array ('IBLOCK_ID' => $this->catalogIblock)));
            $data['filter'] = array_merge($data['filter'], array (array ('IBLOCK_ID' => $this->catalogIblock)));
        }


        return $data;
    }

    public function prepareActions(){
        $actions = unserialize($this->arCond[ 'ACTIONS' ]);
        $steps = array ();
        if ($actions[ 'CLASS_ID' ] == 'CondGroup' && count($actions[ 'CHILDREN' ])){
            foreach ($actions[ 'CHILDREN' ] as $child){
                if ($child[ 'CLASS_ID' ] == "ActSaleCatalogGrp"){
                    $this->fields = array ();
                    $this->props = array ();
                    $this->price = array ();
                    $this->purchasePrice = array ();
                    $child[ 'FORMULA' ] = $this->proccessActionChilds('', $child[ 'CHILDREN' ]);
                    $child[ 'FIELDS' ] = $this->fields;
                    $child[ 'PROPS' ] = $this->props;
                    $child[ 'PRICE' ] = $this->price;
                    $child[ 'PURCHASE_PRICE' ] = $this->purchasePrice;
                    $child[ 'PROD_FIELDS' ] = $this->prodfields;
                }
                $steps[ ] = $child;
            }
        }

        return $steps;
    }

    public function run($id, $proces_id = false){
        $error = true;
        if ($proces_id === false){
            $this->get($id);
            $filter = $this->prepareFilter();
            $actions = $this->prepareActions();
            $cnt = CIBlockElement::GetList(
                array (
                    'ID' => 'ASC'
                ), $filter['filter_wo_cat_and_price'], array ()
            );
            $pages = ceil($cnt / $this->getCnt());
            $page = 1;
            $cnt = $this->getCnt();
            $r = RuntimeTable::add(
                array (
                    'COND_ID' => $id,
                    'RULES'   => serialize($filter),
                    'ACTIONS' => serialize($actions),
                    'PAGES'   => $pages,
                    'PAGE'    => $page,
                    'CNT'     => $cnt,
                )
            );
            $proces_id = $r->getId();
            $error = false;
        }else{
            $rsRun = RuntimeTable::getList(
                array (
                    'filter' => array (
                        'ID'      => $proces_id,
                        'COND_ID' => $id
                    )
                )
            );
            if ($run = $rsRun->fetch()){
                $proces_id = $run[ 'ID' ];
                $filter = unserialize($run[ 'RULES' ]);
                $actions = unserialize($run[ 'ACTIONS' ]);
                $pages = $run[ 'PAGES' ];
                $page = $run[ 'PAGE' ];
                $cnt = $run[ 'CNT' ];
            }
            $error = false;
        }

        if ( !$error){
            if ($page <= $pages && $pages){
                $rsElems = CIBlockElement::GetList(
                    array (
                        'ID' => 'ASC'
                    ), $filter['filter_wo_cat_and_price'], false, array (
                        'iNumPage'  => $page,
                        'nPageSize' => $cnt,
                    )
                );
                while ($ob = $rsElems->GetNextElement()){
                    $arFields = $ob->GetFields();
                    $arFields[ 'PROPERTIES' ] = $ob->GetProperties();
                    $this->lastCheck($arFields, $filter, $actions);
                }
                $total = $page * 100 / $pages;
                if ($total == 100 && $proces_id){
                    RuntimeTable::delete($proces_id);
                    ConditionTable::update($id, array ('DATE_EXEC' => new Bitrix\Main\Type\DateTime()));

                }else{
                    RuntimeTable::update(
                        $proces_id, array (
                                      'PAGE' => ++$page,
                                  )
                    );
                }

                return array (
                    'TOTAL' => $total,
                    'PID'   => ($total == 100 ? false : $proces_id)
                );
            }

            if ($proces_id && RuntimeTable::getById($proces_id)->fetch()){
                RuntimeTable::delete($proces_id);
                ConditionTable::update($id, array ('DATE_EXEC' => new Bitrix\Main\Type\DateTime()));
            }
        }

        return array (
            'TOTAL' => 100,
            'PID'   => false
        );
    }


    function getLengthInt($number){
        $length = 0;
        if ($number == 0){
            $length = 1;
        }else{
            $length = pow(10, ((int) log10($number)));
        }

        return $length;
    }

    function getLengthFloat($number){
        $length = 0;
        if ($number == 0){
            $length = 1;
        }else{
            while (round($number, 0) != $number){
                $number *= 10;
                $length++;
            }
        }

        return $length;
    }


    public function lastCheck($arProduct, $new_filter, $steps){
        if ( !empty($arProduct) && count($steps)){

            Loader::includeModule('catalog');
            Loader::includeModule('sale');
            Loader::includeModule('currency');

            //проверка на наличиие торговых предложений
            $productList = array ($arProduct[ 'ID' ]);
            $offersList = CCatalogSKU::getOffersList($productList);
            if (is_array($offersList) && !empty($offersList[ $arProduct[ 'ID' ] ])){
                $arOffers = array();
                foreach( $offersList[ $arProduct[ 'ID' ] ] as $offer ){
                    $arOffers[] = $offer['ID'];
                }

                if( !empty($arOffers) ){
                    $rsElems = CIBlockElement::GetList(
                        array (
                            'ID' => 'ASC'
                        ), array_merge(array('ID' => $arOffers), array($new_filter['offers_filter']))
                    );
                    while ($ob = $rsElems->GetNextElement()){
                        $arFields = $ob->GetFields();
                        $arFields[ 'PROPERTIES' ] = $ob->GetProperties();
                        $this->runSteps($arFields, $steps);
                    }
                }
            }elseif( !$new_filter['finded_offer_prop'] ){
                //окончательная проверка простого товара
                $rsElems = CIBlockElement::GetList(
                    array (
                        'ID' => 'ASC'
                    ), array_merge(array('ID' => $arProduct['ID']), array($new_filter['filter']))
                );
                if ($ob = $rsElems->GetNextElement()){
                    $this->runSteps($arProduct, $steps);
                }
            }
        }
    }

    public function runSteps($arProduct, $steps){
        if ( !empty($arProduct) && count($steps)){

            Loader::includeModule('catalog');
            Loader::includeModule('sale');
            Loader::includeModule('currency');

            foreach ($steps as $step){
                $data = $step[ 'DATA' ];
                switch ($step[ 'CLASS_ID' ]){
                    case 'ActProdParams':
                        if ($data[ 'Params' ] == 'All'){
                            CCatalogProduct::Update(
                                $arProduct[ 'ID' ], array (
                                                      'QUANTITY_TRACE'        => $data[ 'Dyn' ],
                                                      'CAN_BUY_ZERO'          => $data[ 'Dyn' ],
                                                      'NEGATIVE_AMOUNT_TRACE' => $data[ 'Dyn' ],
                                                      'SUBSCRIBE'             => $data[ 'Dyn' ],
                                                  )
                            );
                        }else{
                            CCatalogProduct::Update(
                                $arProduct[ 'ID' ], array (
                                                      $data[ 'Params' ] => $data[ 'Dyn' ]
                                                  )
                            );
                        }
                        break;
                    case 'ActProdUnit':
                        CCatalogProduct::Update(
                            $arProduct[ 'ID' ], array (
                                                  'MEASURE' => $data[ 'Unit' ],
                                              )
                        );
                        break;
                    case 'ActProdVat':
                        $tmp = explode('_', $data[ 'Vat' ]);
                        CCatalogProduct::Update($arProduct[ 'ID' ], array ('VAT_ID' => $tmp[1]));
                        break;
                    case 'ActChangePrice':
                        if ($data[ 'Price' ] == 'Purchase'){
                            $rsProd = CCatalogProduct::GetList(
                                array (), array (
                                            'ID' => $arProduct[ 'ID' ]
                                        )
                            );
                            if ($arProd = $rsProd->Fetch()){
                                $new_price = ($arProd[ "PURCHASING_PRICE" ] ? $arProd[ "PURCHASING_PRICE" ] : 0);
                                $currency = (empty($arProd[ "PURCHASING_CURRENCY" ]) ? $arProd[ "PURCHASING_CURRENCY" ] : CCurrency::GetBaseCurrency());
                                if ($data[ 'Unit' ] != 'Perc'){
                                    $price = CCurrencyRates::ConvertCurrency($data[ 'Value' ], $data[ 'Unit' ], $currency);
                                }else{
                                    $price = $new_price * $data[ 'Value' ] / 100;
                                }

                                if ($data[ 'Type' ] == "Discount"){
                                    $new_price = $new_price - $price;
                                }else{
                                    $new_price = $new_price + $price;
                                }
                                if ( !$new_price){
                                    $new_price = 0;
                                }

                                CCatalogProduct::Update(
                                    $arProduct[ 'ID' ], array (
                                                          "PURCHASING_PRICE"    => $new_price,
                                                          "PURCHASING_CURRENCY" => $currency,
                                                      )
                                );
                            }
                        }else{
                            $filter = array (
                                "PRODUCT_ID" => $arProduct[ 'ID' ]
                            );
                            if ($data[ 'Price' ] != 'All'){
                                $tmp = explode('_', $data[ 'Price' ]);
                                $filter[ 'CATALOG_GROUP_ID' ] = $tmp[ 1 ];
                            }
                            $db_res = CPrice::GetList(
                                array (), $filter
                            );
                            while ($ar_res = $db_res->Fetch()){
                                $new_price = $ar_res[ 'PRICE' ];

                                if ($data[ 'Unit' ] != 'Perc'){
                                    $price = CCurrencyRates::ConvertCurrency($data[ 'Value' ], $data[ 'Unit' ], $ar_res[ 'CURRENCY' ]);
                                }else{
                                    $price = $new_price * $data[ 'Value' ] / 100;
                                }

                                if ($data[ 'Type' ] == "Discount"){
                                    $new_price = $new_price - $price;
                                }else{
                                    $new_price = $new_price + $price;
                                }

                                if ( !$new_price){
                                    $new_price = 0;
                                }

                                CPrice::Update($ar_res[ 'ID' ], array ('PRICE' => $new_price));
                            }
                        }
                        break;
                    case 'ActConvertCurrency':

                        if ($data[ 'Price' ] == 'Purchase'){
                            $rsProd = CCatalogProduct::GetList(
                                array (), array (
                                            'ID' => $arProduct[ 'ID' ]
                                        )
                            );
                            if ($arProd = $rsProd->Fetch()){
                                $new_price = ($arProd[ "PURCHASING_PRICE" ] ? $arProd[ "PURCHASING_PRICE" ] : 0);
                                $currency = (!empty($arProd[ "PURCHASING_CURRENCY" ]) ? $arProd[ "PURCHASING_CURRENCY" ] : CCurrency::GetBaseCurrency());


                                if ($currency != $data[ 'Currency' ]){
                                    $new_price = CCurrencyRates::ConvertCurrency($new_price, $currency, $data[ 'Currency' ]);

                                    CCatalogProduct::Update(
                                        $arProduct[ 'ID' ], array (
                                                              "PURCHASING_PRICE"    => $new_price,
                                                              "PURCHASING_CURRENCY" => $data[ 'Currency' ],
                                                          )
                                    );
                                }
                            }
                        }else{
                            $filter = array (
                                "PRODUCT_ID" => $arProduct[ 'ID' ]
                            );
                            if ($data[ 'Price' ] != 'All'){
                                $tmp = explode('_', $data[ 'Price' ]);
                                $filter[ 'CATALOG_GROUP_ID' ] = $tmp[ 1 ];
                            }
                            $db_res = CPrice::GetList(
                                array (), $filter
                            );
                            while ($ar_res = $db_res->Fetch()){
                                $new_price = $ar_res[ 'PRICE' ];

                                if ($ar_res[ 'CURRENCY' ] != $data[ 'Currency' ]){
                                    $new_price = CCurrencyRates::ConvertCurrency($new_price, $ar_res[ 'CURRENCY' ], $data[ 'Currency' ]);
                                    CPrice::Update(
                                        $ar_res[ 'ID' ], array (
                                                           'PRICE'    => $new_price,
                                                           'CURRENCY' => $data[ 'Currency' ]
                                                       )
                                    );
                                }
                            }
                        }
                        break;
                    case 'ActChangeCurrency':

                        if ($data[ 'Price' ] == 'Purchase'){
                            $rsProd = CCatalogProduct::GetList(
                                array (), array (
                                            'ID' => $arProduct[ 'ID' ]
                                        )
                            );
                            if ($arProd = $rsProd->Fetch()){
                                CCatalogProduct::Update(
                                    $arProduct[ 'ID' ], array (
                                                          "PURCHASING_CURRENCY" => $data[ 'Currency' ],
                                                      )
                                );
                            }
                        }else{
                            $filter = array (
                                "PRODUCT_ID" => $arProduct[ 'ID' ]
                            );
                            if ($data[ 'Price' ] != 'All'){
                                $tmp = explode('_', $data[ 'Price' ]);
                                $filter[ 'CATALOG_GROUP_ID' ] = $tmp[ 1 ];
                            }
                            $db_res = CPrice::GetList(
                                array (), $filter
                            );
                            while ($ar_res = $db_res->Fetch()){
                                if ($ar_res[ 'CURRENCY' ] != $data[ 'Currency' ]){
                                    CPrice::Update($ar_res[ 'ID' ], array ('CURRENCY' => $data[ 'Currency' ]));
                                }
                            }
                        }
                        break;
                    case 'ActRoundPrice':
                        if ($data[ 'Price' ] == 'Purchase'){
                            $rsProd = CCatalogProduct::GetList(
                                array (), array (
                                            'ID' => $arProduct[ 'ID' ]
                                        )
                            );
                            if ($arProd = $rsProd->Fetch()){
                                $PRICE = ($arProd[ "PURCHASING_PRICE" ] ? $arProd[ "PURCHASING_PRICE" ] : 0);

                                $rozriad = (!empty($data[ 'Discharge' ]) ? $data[ 'Discharge' ] : 0);

                                if ($data[ 'Where' ] == "before"){
                                    $len = $this->getLengthFloat($PRICE);

                                    if ($rozriad > $len){
                                        $rozriad = $len;
                                    }

                                    $roundlen = pow(10, $rozriad);

                                    $PRICE = ceil($PRICE * $roundlen) / $roundlen;
                                }else{
                                    $roundlen = pow(10, $rozriad);
                                    $len = $this->getLengthInt($PRICE);

                                    if ($roundlen > $len){
                                        $roundlen = $len;
                                    }
                                    $PRICE = ceil($PRICE / $roundlen) * $roundlen;
                                }

                                CCatalogProduct::Update(
                                    $arProduct[ 'ID' ], array (
                                                          "PURCHASING_PRICE" => $PRICE,
                                                      )
                                );
                            }
                        }else{
                            $filter = array (
                                "PRODUCT_ID" => $arProduct[ 'ID' ]
                            );
                            if ($data[ 'Price' ] != 'All'){
                                $tmp = explode('_', $data[ 'Price' ]);
                                $filter[ 'CATALOG_GROUP_ID' ] = $tmp[ 1 ];
                            }
                            $db_res = CPrice::GetList(
                                array (), $filter
                            );
                            while ($ar_res = $db_res->Fetch()){
                                $PRICE = $ar_res[ 'PRICE' ];

                                $rozriad = (!empty($data[ 'Discharge' ]) ? $data[ 'Discharge' ] : 0);

                                if ($data[ 'Where' ] == "before"){
                                    $len = $this->getLengthFloat($PRICE);

                                    if ($rozriad > $len){
                                        $rozriad = $len;
                                    }

                                    $roundlen = pow(10, $rozriad);

                                    $PRICE = ceil($PRICE * $roundlen) / $roundlen;
                                }else{
                                    $roundlen = pow(10, $rozriad);
                                    $len = $this->getLengthInt($PRICE);

                                    if ($roundlen > $len){
                                        $roundlen = $len;
                                    }
                                    $PRICE = ceil($PRICE / $roundlen) * $roundlen;
                                }

                                CPrice::Update($ar_res[ 'ID' ], array ('PRICE' => $PRICE));
                            }
                        }
                        break;
                    case 'ActSaleCatalogGrp':

                        $arProps = array ();
                        $arFields = array ();
                        $arProdFields = array ();
                        $arPrice = array ();
                        $purchasePrice = 0;

                        $fields = $step[ 'FIELDS' ];

                        if (count($fields)){
                            foreach ($fields as $k => $f){

                                if ($f == "SECTION_ID"){
                                    $f = "IBLOCK_SECTION_ID";
                                }

                                if (empty($arProduct[ $f ])){
                                    continue;
                                }

                                $arFields[ $k ] = $arProduct[ $f ];
                                unset($fields[ $k ]);
                            }
                        }

                        $mxResult = CCatalogSku::GetProductInfo(
                            $arProduct[ 'ID' ]
                        );
                        if (is_array($mxResult)){

                            //id отцовского елемента
                            $rsParent = CIBlockElement::GetList(
                                array (), array (
                                            'ID' => $mxResult[ 'ID' ]
                                        )
                            );
                            if ($ob = $rsParent->GetNextElement()){
                                $arParent = $ob->GetFields();
                                $arParent[ 'PROPERTIES' ] = $ob->GetProperties();
                                if (count($step[ 'PROPS' ][ $arParent[ 'IBLOCK_ID' ] ])){
                                    foreach ($arParent[ 'PROPERTIES' ] as $prop){
                                        if (isset($step[ 'PROPS' ][ $arParent[ 'IBLOCK_ID' ] ][ $prop[ 'ID' ] ])){
                                            $arProps[ $arParent[ 'IBLOCK_ID' ] ][ $prop[ 'ID' ] ] = $prop[ 'VALUE' ];
                                        }
                                    }
                                }
                                if (count($fields)){
                                    foreach ($fields as $k => $f){

                                        if ($f == "SECTION_ID"){
                                            $f = "IBLOCK_SECTION_ID";
                                        }

                                        if (empty($arParent[ $f ])){
                                            continue;
                                        }

                                        $arFields[ $k ] = $arParent[ $f ];
                                        unset($fields[ $k ]);
                                    }
                                }
                            }
                        }


                        if (count($step[ 'PROPS' ][ $arProduct[ 'IBLOCK_ID' ] ])){
                            foreach ($arProduct[ 'PROPERTIES' ] as $prop){
                                if (isset($step[ 'PROPS' ][ $arProduct[ 'IBLOCK_ID' ] ][ $prop[ 'ID' ] ])){
                                    $arProps[ $arProduct[ 'IBLOCK_ID' ] ][ $prop[ 'ID' ] ] = (!empty($prop[ 'VALUE' ]) ? $prop[ 'VALUE' ] : 0);
                                }
                            }

                            foreach ($step[ 'PROPS' ] as $ibID => $props){
                                if ( !isset($arProps[ $ibID ])){
                                    foreach ($props as $pID => $prop){
                                        $arProps[ $ibID ][ $pID ] = 0;
                                    }
                                }
                            }
                        }

                        if (count($fields)){
                            foreach ($fields as $k => $f){
                                $arFields[ $f ] = 0;
                                unset($fields[ $k ]);
                            }
                        }

                        if (count($step[ 'PROD_FIELDS' ])){
                            $ar_res = CCatalogProduct::GetByID($arProduct[ 'ID' ]);
                            if (count($ar_res)){
                                foreach ($step[ 'PROD_FIELDS' ] as $pf){
                                    $arProdFields[ $pf ] = (isset($ar_res[ $pf ]) ? $ar_res[ $pf ] : 0);
                                }
                            }
                        }
                        if (count($step[ 'PRICE' ])){
                            $db_res = CPrice::GetList(
                                array (), array (
                                            'CATALOG_GROUP_ID' => $step[ 'PRICE' ],
                                            "PRODUCT_ID"       => $arProduct[ 'ID' ]
                                        )
                            );
                            while ($ar_res = $db_res->Fetch()){
                                $arPrice[ $ar_res[ 'CATALOG_GROUP_ID' ] ] = $ar_res;
                            }
                        }
                        if (count($step[ 'PURCHASE_PRICE' ])){
                            $rsProd = CCatalogProduct::GetList(
                                array (), array (
                                            'ID' => $arProduct[ 'ID' ]
                                        )
                            );
                            if ($arProd = $rsProd->Fetch()){
                                $purchaseCurrency = (!empty($arProd[ "PURCHASING_CURRENCY" ]) ? $arProd[ "PURCHASING_CURRENCY" ] : CCurrency::GetBaseCurrency());
                                $purchasePrice = ($arProd[ "PURCHASING_PRICE" ] ? $arProd[ "PURCHASING_PRICE" ] : 0);
                            }
                        }


                        $formula = $step[ 'FORMULA' ];
                        foreach ($step[ 'FIELDS' ] as $field){
                            $formula = str_replace("{" . $field . "}", var_export($arFields[ $field ], 1), $formula);
                        }
                        foreach ($step[ 'PROPS' ] as $ibId => $props){
                            foreach ($props as $prop){
                                $formula = str_replace("{PROPERTY_" . $ibId . "_" . $prop . "}", str_replace(',', '.', var_export($arProps[ $ibId ][ $prop ], 1)), $formula);
                            }
                        }
                        foreach ($step[ 'PROD_FIELDS' ] as $pf){
                            $formula = str_replace("{CATALOG_" . $pf . "}", var_export($arProdFields[ $pf ], 1), $formula);
                        }


                        $f = $formula;

                        $filter = array (
                            "PRODUCT_ID" => $arProduct[ 'ID' ]
                        );
                        $is_price = strpos($data[ 'What' ], 'Price');
                        if ($is_price !== false && $data[ 'What' ] != 'Price_Purchase'){
                            if ($data[ 'What' ] != 'Price_All'){
                                $tmp = explode('_', $data[ 'What' ]);
                                $filter[ 'CATALOG_GROUP_ID' ] = $tmp[ 1 ];
                            }
                            $db_res = CPrice::GetList(
                                array (), $filter
                            );
                            $usedPrices = array ();
                            $baseCurrency = CCurrency::GetBaseCurrency();
                            while ($ar_res = $db_res->Fetch()){
                                $formula = $f;
                                foreach ($step[ 'PRICE' ] as $p){
                                    $new_price = (isset($arPrice[ $p ][ 'PRICE' ]) ? $arPrice[ $p ][ 'PRICE' ] : 0);
                                    $currency = (isset($arPrice[ $p ][ 'CURRENCY' ]) ? $arPrice[ $p ][ 'CURRENCY' ] : $baseCurrency);
                                    if ($ar_res[ 'CURRENCY' ] != $currency){
                                        $new_price = CCurrencyRates::ConvertCurrency($new_price, $currency, $ar_res[ 'CURRENCY' ]);
                                    }

                                    $formula = str_replace("{CATALOG_PRICE_" . $p . "}", var_export($new_price, 1), $formula);
                                }

                                $pnew_price = $purchasePrice;

                                if ($ar_res[ 'CURRENCY' ] != $purchaseCurrency){
                                    $pnew_price = CCurrencyRates::ConvertCurrency($purchasePrice, $purchaseCurrency, $ar_res[ 'CURRENCY' ]);
                                }

                                $formula = str_replace("{PURCHASE_PRICE}", var_export($pnew_price, 1), $formula);

                                $formula
                                    = 'use Dwstroy\Pricechanger\Functions\Fabric;
                                    use Bitrix\Main\Loader;
                                    Loader::includeModule("dwstroy.pricechanger");
                                    $x = ' . $formula . '; return $x;';

                                $x = eval($formula);

                                CPrice::Update($ar_res[ 'ID' ], array ('PRICE' => $x));
                                $usedPrices[ ] = $ar_res[ 'CATALOG_GROUP_ID' ];
                            }

                            $priceFilter = array ();
                            if ($data[ 'What' ] != 'Price_All'){
                                $priceFilter[ 'ID' ] = $filter[ 'CATALOG_GROUP_ID' ];
                            }
                            $dbPriceType = CCatalogGroup::GetList(
                                array ("SORT" => "ASC"), $priceFilter
                            );


                            while ($arPriceType = $dbPriceType->Fetch()){
                                if (in_array($arPriceType[ 'ID' ], $usedPrices)){
                                    continue;
                                }

                                                         $formula = $f;
                                foreach ($step[ 'PRICE' ] as $p){
                                    $new_price = (isset($arPrice[ $p ][ 'PRICE' ]) ? $arPrice[ $p ][ 'PRICE' ] : 0);
                                    $currency = (isset($arPrice[ $p ][ 'CURRENCY' ]) ? $arPrice[ $p ][ 'CURRENCY' ] : $baseCurrency);
                                    if ($baseCurrency != $currency){
                                        $new_price = CCurrencyRates::ConvertCurrency($new_price, $currency, $baseCurrency);
                                    }

                                    $formula = str_replace("{CATALOG_PRICE_" . $p . "}", var_export($new_price, 1), $formula);
                                }

                                $formula
                                    = 'use Dwstroy\Pricechanger\Functions\Fabric;
                                    use Bitrix\Main\Loader;
                                    Loader::includeModule("dwstroy.pricechanger");
                                    $x = ' . $formula . '; return $x;';

                                $x = eval($formula);
                                $res = CPrice::Add(
                                    Array (
                                        "PRODUCT_ID"       => $arProduct[ 'ID' ],
                                        "CATALOG_GROUP_ID" => $arPriceType[ 'ID' ],
                                        "PRICE"            => $x,
                                        "CURRENCY"         => CCurrency::GetBaseCurrency(),
                                    )
                                );
                            }
                        }else{
                            $formula = $f;

                            if ($data[ 'What' ] == 'Price_Purchase'){

                                $formula = str_replace("{PURCHASE_PRICE}", var_export($purchasePrice, 1), $formula);

                                if (empty($purchaseCurrency)){
                                    $purchaseCurrency = CCurrency::GetBaseCurrency();
                                }

                                foreach ($step[ 'PRICE' ] as $p){
                                    $new_price = (isset($arPrice[ $p ][ 'PRICE' ]) ? $arPrice[ $p ][ 'PRICE' ] : 0);

                                    if ($arPrice[ $p ][ 'CURRENCY' ] != $purchaseCurrency){
                                        $new_price = CCurrencyRates::ConvertCurrency($new_price, $arPrice[ $p ][ 'CURRENCY' ], $purchaseCurrency);
                                    }

                                    $formula = str_replace("{CATALOG_PRICE_" . $p . "}", var_export($new_price, 1), $formula);
                                }
                            }else{
                                foreach ($step[ 'PRICE' ] as $p){
                                    $new_price = (isset($arPrice[ $p ][ 'PRICE' ]) ? $arPrice[ $p ][ 'PRICE' ] : 0);
                                    $formula = str_replace("{CATALOG_PRICE_" . $p . "}", var_export($new_price, 1), $formula);
                                }
                            }


                            $formula
                                = 'use Dwstroy\Pricechanger\Functions\Fabric;
                                    use Bitrix\Main\Loader;
                                    Loader::includeModule("dwstroy.pricechanger");
                                    $x = ' . $formula . '; return $x;';
                            $x = eval($formula);

                            if ($data[ 'What' ] == 'Price_Purchase'){
                                CCatalogProduct::Update(
                                    $arProduct[ 'ID' ], array (
                                                          "PURCHASING_PRICE"    => $x,
                                                          "PURCHASING_CURRENCY" => $purchaseCurrency,
                                                      )
                                );
                            }elseif ($data[ 'What' ] != 'MEASURE_RATIO'){
                                CCatalogProduct::Update(
                                    $arProduct[ 'ID' ], array (
                                                          $data[ 'What' ] => $x
                                                      )
                                );
                            }else{
                                $rsRatio = CCatalogMeasureRatio::getList(
                                    array (), array (
                                                'PRODUCT_ID' => $arProduct[ 'ID' ]
                                            )
                                );
                                if ($arRatio = $rsRatio->Fetch()){
                                    $r = new CCatalogMeasureRatio();
                                    $r->update($arRatio[ 'ID' ], array ('RATIO' => $x));
                                }else{
                                    $r = new CCatalogMeasureRatio();
                                    $r->add(
                                        array (
                                            'RATIO'      => $x,
                                            'PRODUCT_ID' => $arProduct[ 'ID' ]
                                        )
                                    );
                                }
                            }
                        }
                        break;
                }
            }
        }
    }
}