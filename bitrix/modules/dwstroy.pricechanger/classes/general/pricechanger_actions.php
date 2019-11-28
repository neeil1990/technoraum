<?
use Bitrix\Main\Loader,
    Bitrix\Main\Localization\Loc,
    Bitrix\Sale;

Loc::loadMessages(__FILE__);

if (!Loader::includeModule('catalog'))
    return;

class AMSaleActionCtrl extends AMGlobalCondCtrl
{
    public static function GetClassName()
    {
        return __CLASS__;
    }

    public static function GetConditionShow($arParams)
    {
        if (!isset($arParams['ID']))
            return false;
        if ($arParams['ID'] != static::GetControlID())
            return false;
        $arControl = array(
            'ID' => $arParams['ID'],
            'ATOMS' => static::GetAtomsEx(false, true),
        );

        return static::CheckAtoms($arParams['DATA'], $arParams, $arControl, true);
    }

    public static function Parse($arOneCondition)
    {
        if (!isset($arOneCondition['controlId']))
            return false;
        if ($arOneCondition['controlId'] != static::GetControlID())
            return false;
        $arControl = array(
            'ID' => $arOneCondition['controlId'],
            'ATOMS' => static::GetAtomsEx(false, true),
        );

        return static::CheckAtoms($arOneCondition, $arOneCondition, $arControl, false);
    }
}

class AMSaleActionCtrlComplex extends AMGlobalCondCtrlComplex
{
    public static function GetClassName()
    {
        return __CLASS__;
    }
}

class AMSaleActionCtrlGroup extends AMGlobalCondCtrlGroup
{
    public static function GetClassName()
    {
        return __CLASS__;
    }

    public static function GetShowIn($arControls)
    {
        $arControls = array();
        return $arControls;
    }

    public static function GetControlShow($arParams)
    {
        $arResult = array(
            'controlId' => static::GetControlID(),
            'group' => true,
            'label' => '',
            'defaultText' => '',
            'showIn' => static::GetShowIn($arParams['SHOW_IN_GROUPS']),
            'control' => array(
                Loc::getMessage('BT_SALE_ACT_GROUP_GLOBAL_PREFIX')
            )
        );

        return $arResult;
    }

    public static function GetConditionShow($arParams)
    {
        return array(
            'id' => $arParams['COND_NUM'],
            'controlId' => static::GetControlID(),
            'values' => array()
        );
    }

    public static function Parse($arOneCondition)
    {
        return array(
            'All' => 'AND'
        );
    }

}

class AMSaleActionCtrlAction extends AMGlobalCondCtrlGroup
{
    public static function GetClassName()
    {
        return __CLASS__;
    }

    public static function GetConditionShow($arParams)
    {
        if (!isset($arParams['ID']))
            return false;
        if ($arParams['ID'] != static::GetControlID())
            return false;
        $arControl = array(
            'ID' => $arParams['ID'],
            'ATOMS' => static::GetAtomsEx(false, true)
        );

        return static::CheckAtoms($arParams['DATA'], $arParams, $arControl, true);
    }

    public static function Parse($arOneCondition)
    {
        if (!isset($arOneCondition['controlId']))
            return false;
        if ($arOneCondition['controlId'] != static::GetControlID())
            return false;
        $arControl = array(
            'ID' => $arOneCondition['controlId'],
            'ATOMS' => static::GetAtomsEx(false, true)
        );

        return static::CheckAtoms($arOneCondition, $arOneCondition, $arControl, false);
    }

    public static function GetVisual()
    {
        return array(
            'controls' => array(
                'All'
            ),
            'values' => array(
                array(
                    'All' => 'AND'
                ),
                array(
                    'All' => 'OR'
                )
            ),
            'logic' => array(
                array(
                    'style' => 'condition-logic-and',
                    'message' => Loc::getMessage('BT_SALE_ACT_GROUP_LOGIC_AND')
                ),
                array(
                    'style' => 'condition-logic-or',
                    'message' => Loc::getMessage('BT_SALE_ACT_GROUP_LOGIC_OR')
                )
            )
        );
    }
}

class AMSaleActionCtrlConvertCurrency extends AMSaleActionCtrl
{
    public static function GetClassName()
    {
        return __CLASS__;
    }

    public static function GetControlID()
    {
        return 'ActConvertCurrency';
    }

    public static function GetControlShow($arParams)
    {
        $arAtoms = static::GetAtomsEx(false, false);
        $arResult = array(
            'controlId' => static::GetControlID(),
            'group' => false,
            'label' => Loc::getMessage('BT_SALE_ACT_CURRENCY_CONVERT_LABEL'),
            'defaultText' => Loc::getMessage('BT_SALE_ACT_CURRENCY_CONVERT_DEF_TEXT'),
            'showIn' => static::GetShowIn($arParams['SHOW_IN_GROUPS']),
            'control' => array(
                Loc::getMessage('BT_SALE_ACT_CURRENCY_CONVERT_GROUP_PRODUCT_PREFIX'),
                $arAtoms['Price'],
                $arAtoms['Currency']
            )
        );

        return $arResult;
    }

    public static function GetAtoms()
    {
        return static::GetAtomsEx(false, false);
    }

    public static function GetAtomsEx($strControlID = false, $boolEx = false)
    {

        $arPriceTypes = array(
            'All' => Loc::getMessage('PRICE_CHANGER_ALL_PRICE'),
            'Purchase' => Loc::getMessage('PRICE_CHANGER_PURCHASE_PRICE')
        );

        $dbPriceType = CCatalogGroup::GetList(
            array ("SORT" => "ASC"), array ()
        );
        while ($arPriceType = $dbPriceType->Fetch()){
            $arPriceTypes['Price_'.$arPriceType['ID']] = $arPriceType['NAME_LANG'];
        }

        Loader::includeModule('currency');
        $arCurrencies = array();
        $def = '';
        $lcur = CCurrency::GetList(($by="name"), ($order1="asc"), LANGUAGE_ID);
        while($lcur_res = $lcur->Fetch())
        {
            if( $lcur_res['BASE'] == "Y" )
                $def = $lcur_res['CURRENCY'];
            $arCurrencies[$lcur_res['CURRENCY']] = $lcur_res['FULL_NAME'];
        }

        $boolEx = (true === $boolEx ? true : false);
        $arAtomList = array(
            'Price' => array(
                'JS' => array(
                    'id' => 'Price',
                    'name' => 'extra_price',
                    'type' => 'select',
                    'values' => $arPriceTypes,
                    'defaultText' => Loc::getMessage('BT_SALE_ACT_CURRENCY_CONVERT_SELECT_TYPE_DEF'),
                    'defaultValue' => 'All',
                    'first_option' => '...'
                ),
                'ATOM' => array(
                    'ID' => 'Price',
                    'FIELD_TYPE' => 'string',
                    'FIELD_LENGTH' => 255,
                    'MULTIPLE' => 'N',
                    'VALIDATE' => 'list'
                )
            ),
            'Currency' => array(
                'JS' => array(
                    'id' => 'Currency',
                    'name' => 'extra',
                    'type' => 'select',
                    'values' => $arCurrencies,
                    'defaultText' => Loc::getMessage('BT_SALE_ACT_CURRENCY_CONVERT_SELECT_TYPE_DEF'),
                    'defaultValue' => $def,
                    'first_option' => '...'
                ),
                'ATOM' => array(
                    'ID' => 'Currency',
                    'FIELD_TYPE' => 'string',
                    'FIELD_LENGTH' => 255,
                    'MULTIPLE' => 'N',
                    'VALIDATE' => 'list'
                )
            ),
        );

        if (!$boolEx)
        {
            foreach ($arAtomList as &$arOneAtom)
            {
                $arOneAtom = $arOneAtom['JS'];
            }
            if (isset($arOneAtom))
                unset($arOneAtom);
        }

        return $arAtomList;
    }

    public static function GetShowIn($arControls)
    {
        return array(AMSaleActionCtrlGroup::GetControlID());
    }
}

class AMSaleActionCtrlProdVat extends AMSaleActionCtrl
{
    public static function GetClassName()
    {
        return __CLASS__;
    }

    public static function GetControlID()
    {
        return 'ActProdVat';
    }

    public static function GetControlShow($arParams)
    {
        $arAtoms = static::GetAtomsEx(false, false);
        $arResult = array(
            'controlId' => static::GetControlID(),
            'group' => false,
            'label' => Loc::getMessage('BT_SALE_ACT_PROD_VAT_LABEL'),
            'defaultText' => Loc::getMessage('BT_SALE_ACT_PROD_VAT_DEF_TEXT'),
            'showIn' => static::GetShowIn($arParams['SHOW_IN_GROUPS']),
            'control' => array(
                Loc::getMessage('BT_SALE_ACT_PROD_VAT_GROUP_PRODUCT_PREFIX'),
                $arAtoms['Vat'],
            )
        );

        return $arResult;
    }

    public static function GetAtoms()
    {
        return static::GetAtomsEx(false, false);
    }

    public static function GetAtomsEx($strControlID = false, $boolEx = false)
    {

        Loader::includeModule('catalog');
        $arVats = array(
            'vat_0' => Loc::getMessage('PRICE_PROD_VAT_NO')
        );


        $rsVats = CCatalogVat::GetListEx(
            array(
                'SORT' => 'ASC'
            ),
            array(
                'ACTIVE' => 'Y'
            )
        );
        while( $arVat = $rsVats->Fetch() ){
            $arVats['vat_'.$arVat['ID']] = $arVat['NAME'];
        }

        $boolEx = (true === $boolEx ? true : false);
        $arAtomList = array(
            'Vat' => array(
                'JS' => array(
                    'id' => 'Vat',
                    'name' => 'extra_vat',
                    'type' => 'select',
                    'values' => $arVats,
                    'defaultText' => Loc::getMessage('BT_SALE_ACT_PROD_VAT_SELECT_TYPE_DEF'),
                    'defaultValue' => 'vat_0',
                    'first_option' => '...'
                ),
                'ATOM' => array(
                    'ID' => 'Vat',
                    'FIELD_TYPE' => 'string',
                    'FIELD_LENGTH' => 255,
                    'MULTIPLE' => 'N',
                    'VALIDATE' => 'list'
                )
            )
        );

        if (!$boolEx)
        {
            foreach ($arAtomList as &$arOneAtom)
            {
                $arOneAtom = $arOneAtom['JS'];
            }
            if (isset($arOneAtom))
                unset($arOneAtom);
        }

        return $arAtomList;
    }

    public static function GetShowIn($arControls)
    {
        return array(AMSaleActionCtrlGroup::GetControlID());
    }
}

class AMSaleActionCtrlProdUnit extends AMSaleActionCtrl
{
    public static function GetClassName()
    {
        return __CLASS__;
    }

    public static function GetControlID()
    {
        return 'ActProdUnit';
    }

    public static function GetControlShow($arParams)
    {
        $arAtoms = static::GetAtomsEx(false, false);
        $arResult = array(
            'controlId' => static::GetControlID(),
            'group' => false,
            'label' => Loc::getMessage('BT_SALE_ACT_PROD_UNIT_LABEL'),
            'defaultText' => Loc::getMessage('BT_SALE_ACT_PROD_UNIT_DEF_TEXT'),
            'showIn' => static::GetShowIn($arParams['SHOW_IN_GROUPS']),
            'control' => array(
                Loc::getMessage('BT_SALE_ACT_PROD_UNIT_GROUP_PRODUCT_PREFIX'),
                $arAtoms['Unit'],
            )
        );

        return $arResult;
    }

    public static function GetAtoms()
    {
        return static::GetAtomsEx(false, false);
    }

    public static function GetAtomsEx($strControlID = false, $boolEx = false)
    {
        Loader::includeModule('catalog');
        $arMeasures = array(
        );

        $def = '';
        $dbResultList = CCatalogMeasure::getList();
        while($arMeasure = $dbResultList->Fetch()){
            $arMeasures[$arMeasure['ID']] = $arMeasure['MEASURE_TITLE'];
            if( $arMeasure['IS_DEFAULT'] == 'Y' )
                $def = $arMeasure['ID'];
        }

        $boolEx = (true === $boolEx ? true : false);
        $arAtomList = array(
            'Unit' => array(
                'JS' => array(
                    'id' => 'Unit',
                    'name' => 'extra_unit',
                    'type' => 'select',
                    'values' => $arMeasures,
                    'defaultText' => Loc::getMessage('BT_SALE_ACT_PROD_UNIT_SELECT_TYPE_DEF'),
                    'defaultValue' => $def,
                    'first_option' => '...'
                ),
                'ATOM' => array(
                    'ID' => 'Unit',
                    'FIELD_TYPE' => 'string',
                    'FIELD_LENGTH' => 255,
                    'MULTIPLE' => 'N',
                    'VALIDATE' => 'list'
                )
            )
        );

        if (!$boolEx)
        {
            foreach ($arAtomList as &$arOneAtom)
            {
                $arOneAtom = $arOneAtom['JS'];
            }
            if (isset($arOneAtom))
                unset($arOneAtom);
        }

        return $arAtomList;
    }

    public static function GetShowIn($arControls)
    {
        return array(AMSaleActionCtrlGroup::GetControlID());
    }
}

class AMSaleActionCtrlProdParams extends AMSaleActionCtrl
{
    public static function GetClassName()
    {
        return __CLASS__;
    }

    public static function GetControlID()
    {
        return 'ActProdParams';
    }

    public static function GetControlShow($arParams)
    {
        $arAtoms = static::GetAtomsEx(false, false);
        $arResult = array(
            'controlId' => static::GetControlID(),
            'group' => false,
            'label' => Loc::getMessage('BT_SALE_ACT_PROD_PARAMS_LABEL'),
            'defaultText' => Loc::getMessage('BT_SALE_ACT_PROD_PARAMS_DEF_TEXT'),
            'showIn' => static::GetShowIn($arParams['SHOW_IN_GROUPS']),
            'control' => array(
                Loc::getMessage('BT_SALE_ACT_PROD_PARAMS_GROUP_PRODUCT_PREFIX'),
                $arAtoms['Params'],
                $arAtoms['Dyn'],
            )
        );

        return $arResult;
    }

    public static function GetAtoms()
    {
        return static::GetAtomsEx(false, false);
    }

    public static function GetAtomsEx($strControlID = false, $boolEx = false)
    {
        $boolEx = (true === $boolEx ? true : false);
        $arAtomList = array(
            'Params' => array(
                'JS' => array(
                    'id' => 'Params',
                    'name' => 'extra_params',
                    'type' => 'select',
                    'values' =>array(
                        'All' => Loc::getMessage('BT_SALE_ACT_PROD_PARAMS_SELECT_TYPE_DEF'),
                        'QUANTITY_TRACE' => Loc::getMessage('BT_SALE_ACT_PROD_PARAMS_QUANTITY_TRACE'),
                        'CAN_BUY_ZERO' => Loc::getMessage('BT_SALE_ACT_PROD_PARAMS_CAN_BUY_ZERO'),
                        'NEGATIVE_AMOUNT_TRACE' => Loc::getMessage('BT_SALE_ACT_PROD_PARAMS_NEGATIVE_AMOUNT_TRACE'),
                        'SUBSCRIBE' => Loc::getMessage('BT_SALE_ACT_PROD_PARAMS_SUBSCRIBE'),
                    ),
                    'defaultText' => Loc::getMessage('BT_SALE_ACT_PROD_PARAMS_SELECT_TYPE_DEF'),
                    'defaultValue' => 'All',
                    'first_option' => '...'
                ),
                'ATOM' => array(
                    'ID' => 'Params',
                    'FIELD_TYPE' => 'string',
                    'FIELD_LENGTH' => 255,
                    'MULTIPLE' => 'N',
                    'VALIDATE' => 'list'
                )
            ),
            'Dyn' => array(
                'JS' => array(
                    'id' => 'Dyn',
                    'name' => 'extra_dyn',
                    'type' => 'select',
                    'values' =>array(
                        'D' => Loc::getMessage('BT_SALE_ACT_PROD_DYN_D'),
                        'Y' => Loc::getMessage('BT_SALE_ACT_PROD_DYN_Y'),
                        'N' => Loc::getMessage('BT_SALE_ACT_PROD_DYN_N'),
                    ),
                    'defaultText' => Loc::getMessage('BT_SALE_ACT_PROD_DYN_D'),
                    'defaultValue' => 'D',
                    'first_option' => '...'
                ),
                'ATOM' => array(
                    'ID' => 'Dyn',
                    'FIELD_TYPE' => 'string',
                    'FIELD_LENGTH' => 255,
                    'MULTIPLE' => 'N',
                    'VALIDATE' => 'list'
                )
            )
        );

        if (!$boolEx)
        {
            foreach ($arAtomList as &$arOneAtom)
            {
                $arOneAtom = $arOneAtom['JS'];
            }
            if (isset($arOneAtom))
                unset($arOneAtom);
        }

        return $arAtomList;
    }

    public static function GetShowIn($arControls)
    {
        return array(AMSaleActionCtrlGroup::GetControlID());
    }
}

class AMSaleActionCtrlChangeCurrency extends AMSaleActionCtrl
{
    public static function GetClassName()
    {
        return __CLASS__;
    }

    public static function GetControlID()
    {
        return 'ActChangeCurrency';
    }

    public static function GetControlShow($arParams)
    {
        $arAtoms = static::GetAtomsEx(false, false);
        $arResult = array(
            'controlId' => static::GetControlID(),
            'group' => false,
            'label' => Loc::getMessage('BT_SALE_ACT_CURRENCY_CHANGE_LABEL'),
            'defaultText' => Loc::getMessage('BT_SALE_ACT_CURRENCY_CHANGE_DEF_TEXT'),
            'showIn' => static::GetShowIn($arParams['SHOW_IN_GROUPS']),
            'control' => array(
                Loc::getMessage('BT_SALE_ACT_CURRENCY_CHANGE_GROUP_PRODUCT_PREFIX'),
                $arAtoms['Price'],
                $arAtoms['Currency']
            )
        );

        return $arResult;
    }

    public static function GetAtoms()
    {
        return static::GetAtomsEx(false, false);
    }

    public static function GetAtomsEx($strControlID = false, $boolEx = false)
    {

        $arPriceTypes = array(
            'All' => Loc::getMessage('PRICE_CHANGER_ALL_PRICE'),
            'Purchase' => Loc::getMessage('PRICE_CHANGER_PURCHASE_PRICE')
        );

        $dbPriceType = CCatalogGroup::GetList(
            array ("SORT" => "ASC"), array ()
        );
        while ($arPriceType = $dbPriceType->Fetch()){
            $arPriceTypes['Price_'.$arPriceType['ID']] = $arPriceType['NAME_LANG'];
        }

        Loader::includeModule('currency');
        $arCurrencies = array();
        $def = '';
        $lcur = CCurrency::GetList(($by="name"), ($order1="asc"), LANGUAGE_ID);
        while($lcur_res = $lcur->Fetch())
        {
            if( $lcur_res['BASE'] == "Y" )
                $def = $lcur_res['CURRENCY'];
            $arCurrencies[$lcur_res['CURRENCY']] = $lcur_res['FULL_NAME'];
        }

        $boolEx = (true === $boolEx ? true : false);
        $arAtomList = array(
            'Price' => array(
                'JS' => array(
                    'id' => 'Price',
                    'name' => 'extra_price',
                    'type' => 'select',
                    'values' => $arPriceTypes,
                    'defaultText' => Loc::getMessage('BT_SALE_ACT_CURRENCY_CHANGE_SELECT_TYPE_DEF'),
                    'defaultValue' => 'All',
                    'first_option' => '...'
                ),
                'ATOM' => array(
                    'ID' => 'Price',
                    'FIELD_TYPE' => 'string',
                    'FIELD_LENGTH' => 255,
                    'MULTIPLE' => 'N',
                    'VALIDATE' => 'list'
                )
            ),
            'Currency' => array(
                'JS' => array(
                    'id' => 'Currency',
                    'name' => 'extra',
                    'type' => 'select',
                    'values' => $arCurrencies,
                    'defaultText' => Loc::getMessage('BT_SALE_ACT_CURRENCY_CHANGE_SELECT_TYPE_DEF'),
                    'defaultValue' => $def,
                    'first_option' => '...'
                ),
                'ATOM' => array(
                    'ID' => 'Currency',
                    'FIELD_TYPE' => 'string',
                    'FIELD_LENGTH' => 255,
                    'MULTIPLE' => 'N',
                    'VALIDATE' => 'list'
                )
            ),
        );

        if (!$boolEx)
        {
            foreach ($arAtomList as &$arOneAtom)
            {
                $arOneAtom = $arOneAtom['JS'];
            }
            if (isset($arOneAtom))
                unset($arOneAtom);
        }

        return $arAtomList;
    }

    public static function GetShowIn($arControls)
    {
        return array(AMSaleActionCtrlGroup::GetControlID());
    }

}

class AMSaleActionCtrlRoundPrice extends AMSaleActionCtrl
{
    public static function GetClassName()
    {
        return __CLASS__;
    }

    public static function GetControlID()
    {
        return 'ActRoundPrice';
    }

    public static function GetControlShow($arParams)
    {
        $arAtoms = static::GetAtomsEx(false, false);
        $arResult = array(
            'controlId' => static::GetControlID(),
            'group' => false,
            'label' => Loc::getMessage('BT_SALE_ACT_ROUND_PRICE_LABEL'),
            'defaultText' => Loc::getMessage('BT_SALE_ACT_ROUND_PRICE_DEF_TEXT'),
            'showIn' => static::GetShowIn($arParams['SHOW_IN_GROUPS']),
            'control' => array(
                Loc::getMessage('BT_SALE_ACT_ROUND_PRICE_GROUP_PRODUCT_PREFIX'),
                $arAtoms['Price'],
                $arAtoms['Where'],
                $arAtoms['Discharge']
            )
        );

        return $arResult;
    }

    public static function GetAtoms()
    {
        return static::GetAtomsEx(false, false);
    }

    public static function GetAtomsEx($strControlID = false, $boolEx = false)
    {

        $arPriceTypes = array(
            'All' => Loc::getMessage('PRICE_CHANGER_ALL_PRICE'),
            'Purchase' => Loc::getMessage('PRICE_CHANGER_PURCHASE_PRICE')
        );

        $dbPriceType = CCatalogGroup::GetList(
            array ("SORT" => "ASC"), array ()
        );
        while ($arPriceType = $dbPriceType->Fetch()){
            $arPriceTypes['Price_'.$arPriceType['ID']] = $arPriceType['NAME_LANG'];
        }

        $boolEx = (true === $boolEx ? true : false);
        $arAtomList = array(
            'Price' => array(
                'JS' => array(
                    'id' => 'Price',
                    'name' => 'extra_price',
                    'type' => 'select',
                    'values' => $arPriceTypes,
                    'defaultText' => Loc::getMessage('BT_SALE_ACT_ROUND_PRICE_SELECT_TYPE_DEF'),
                    'defaultValue' => 'All',
                    'first_option' => '...'
                ),
                'ATOM' => array(
                    'ID' => 'Price',
                    'FIELD_TYPE' => 'string',
                    'FIELD_LENGTH' => 255,
                    'MULTIPLE' => 'N',
                    'VALIDATE' => 'list'
                )
            ),
            'Where' => array(
                'JS' => array(
                    'id' => 'Where',
                    'name' => 'extra_where',
                    'type' => 'select',
                    'values' => array(
                        'before' => Loc::getMessage('BT_SALE_ACT_ROUND_PRICE_SELECT_WHERE_BEFORE'),
                        'after' => Loc::getMessage('BT_SALE_ACT_ROUND_PRICE_SELECT_WHERE_AFTER')
                    ),
                    'defaultText' => Loc::getMessage('BT_SALE_ACT_ROUND_PRICE_SELECT_WHERE_BEFORE'),
                    'defaultValue' => 'before',
                    'first_option' => '...'
                ),
                'ATOM' => array(
                    'ID' => 'Where',
                    'FIELD_TYPE' => 'string',
                    'FIELD_LENGTH' => 255,
                    'MULTIPLE' => 'N',
                    'VALIDATE' => 'list'
                )
            ),
            'Discharge' => array(
                'JS' => array(
                    'id' => 'Discharge',
                    'name' => 'extra_discharge',
                    'type' => 'input',
                    'defaultText' => Loc::getMessage('BT_SALE_ACT_ROUND_PRICE_SELECT_DISCHARGE_DEF'),
                    'defaultValue' => '',
                ),
                'ATOM' => array(
                    'ID' => 'Discharge',
                    'FIELD_TYPE' => 'int',
                    'FIELD_LENGTH' => 255,
                    'MULTIPLE' => 'N',
                    'VALIDATE' => ''
                )
            )
        );

        if (!$boolEx)
        {
            foreach ($arAtomList as &$arOneAtom)
            {
                $arOneAtom = $arOneAtom['JS'];
            }
            if (isset($arOneAtom))
                unset($arOneAtom);
        }

        return $arAtomList;
    }

    public static function GetShowIn($arControls)
    {
        return array(AMSaleActionCtrlGroup::GetControlID());
    }

}

class AMSaleActionCtrlProduct extends AMSaleActionCtrl
{
    public static function GetClassName()
    {
        return __CLASS__;
    }

    public static function GetControlID()
    {
        return 'ActChangePrice';
    }

    public static function GetControlShow($arParams)
    {
        $arAtoms = static::GetAtomsEx(false, false);
        $arResult = array(
            'controlId' => static::GetControlID(),
            'group' => false,
            'label' => Loc::getMessage('BT_SALE_ACT_PRICE_CHANGE_LABEL'),
            'defaultText' => Loc::getMessage('BT_SALE_ACT_PRICE_CHANGE_DEF_TEXT'),
            'showIn' => static::GetShowIn($arParams['SHOW_IN_GROUPS']),
            'control' => array(
                Loc::getMessage('BT_SALE_ACT_PRICE_CHANGE_GROUP_PRODUCT_PREFIX'),
                $arAtoms['Price'],
                $arAtoms['Type'],
                $arAtoms['Value'],
                $arAtoms['Unit']
            )
        );

        return $arResult;
    }

    public static function GetAtoms()
    {
        return static::GetAtomsEx(false, false);
    }

    public static function GetAtomsEx($strControlID = false, $boolEx = false)
    {
        $boolEx = (true === $boolEx ? true : false);

        $arPriceTypes = array(
            'All' => Loc::getMessage('PRICE_CHANGER_ALL_PRICE'),
            'Purchase' => Loc::getMessage('PRICE_CHANGER_PURCHASE_PRICE')
        );

        $dbPriceType = CCatalogGroup::GetList(
            array ("SORT" => "ASC"), array ()
        );
        while ($arPriceType = $dbPriceType->Fetch()){
            $arPriceTypes['Price_'.$arPriceType['ID']] = $arPriceType['NAME_LANG'];
        }

        Loader::includeModule('currency');
        $arCurrencies = array();
        $def = '';
        $lcur = CCurrency::GetList(($by="name"), ($order1="asc"), LANGUAGE_ID);
        while($lcur_res = $lcur->Fetch())
        {
            if( $lcur_res['BASE'] == "Y" )
                $def = $lcur_res['CURRENCY'];
            $arCurrencies[$lcur_res['CURRENCY']] = $lcur_res['FULL_NAME'];
        }

        $arAtomList = array(
            'Price' => array(
                'JS' => array(
                    'id' => 'Price',
                    'name' => 'extra_price',
                    'type' => 'select',
                    'values' => $arPriceTypes,
                    'defaultText' => Loc::getMessage('BT_SALE_ACT_PRICE_CHANGE_SELECT_TYPE_DEF'),
                    'defaultValue' => 'All',
                    'first_option' => '...'
                ),
                'ATOM' => array(
                    'ID' => 'Price',
                    'FIELD_TYPE' => 'string',
                    'FIELD_LENGTH' => 255,
                    'MULTIPLE' => 'N',
                    'VALIDATE' => 'list'
                )
            ),
            'Type' => array(
                'JS' => array(
                    'id' => 'Type',
                    'name' => 'extra',
                    'type' => 'select',
                    'values' => array(
                        'Discount' => Loc::getMessage('BT_SALE_ACT_PRICE_CHANGE_SELECT_TYPE_DISCOUNT'),
                        'Extra' => Loc::getMessage('BT_SALE_ACT_PRICE_CHANGE_SELECT_TYPE_EXTRA'),
                    ),
                    'defaultText' => Loc::getMessage('BT_SALE_ACT_PRICE_CHANGE_SELECT_TYPE_DEF'),
                    'defaultValue' => 'Discount',
                    'first_option' => '...'
                ),
                'ATOM' => array(
                    'ID' => 'Type',
                    'FIELD_TYPE' => 'string',
                    'FIELD_LENGTH' => 255,
                    'MULTIPLE' => 'N',
                    'VALIDATE' => 'list'
                )
            ),
            'Value' => array(
                'JS' => array(
                    'id' => 'Value',
                    'name' => 'extra_size',
                    'type' => 'input'
                ),
                'ATOM' => array(
                    'ID' => 'Value',
                    'FIELD_TYPE' => 'double',
                    'MULTIPLE' => 'N',
                    'VALIDATE' => ''
                )
            ),
            'Unit' => array(
                'JS' => array(
                    'id' => 'Unit',
                    'name' => 'extra_unit',
                    'type' => 'select',
                    'values' =>
                        array_merge(
                            array(
                                'Perc' => Loc::getMessage('BT_SALE_ACT_PRICE_CHANGE_SELECT_PERCENT'),
                            ),
                            $arCurrencies),
                    'defaultText' => Loc::getMessage('BT_SALE_ACT_PRICE_CHANGE_SELECT_UNIT_DEF'),
                    'defaultValue' => 'Perc',
                    'first_option' => '...'
                ),
                'ATOM' => array(
                    'ID' => 'Unit',
                    'FIELD_TYPE' => 'string',
                    'FIELD_LENGTH' => 255,
                    'MULTIPLE' => 'N',
                    'VALIDATE' => ''
                )
            )
        );

        if (!$boolEx)
        {
            foreach ($arAtomList as &$arOneAtom)
            {
                $arOneAtom = $arOneAtom['JS'];
            }
            if (isset($arOneAtom))
                unset($arOneAtom);
        }

        return $arAtomList;
    }

    public static function GetShowIn($arControls)
    {
        return array(AMSaleActionCtrlGroup::GetControlID());
    }

}




class AMSaleActionCtrlCatalogGroup extends AMSaleActionCtrlAction
{
    public static function GetClassName()
    {
        return __CLASS__;
    }

    public static function GetControlID()
    {
        return 'ActSaleCatalogGrp';
    }

    public static function GetControlShow($arParams)
    {
        $arAtoms = static::GetAtomsEx(false, false);
        $boolCurrency = false;
        if (static::$boolInit)
        {
            if (isset(static::$arInitParams['CURRENCY']))
            {
                $arAtoms['Unit']['values']['CurEach'] = str_replace('#CUR#', static::$arInitParams['CURRENCY'], $arAtoms['Unit']['values']['CurEach']);
                $arAtoms['Unit']['values']['CurAll'] = str_replace('#CUR#', static::$arInitParams['CURRENCY'], $arAtoms['Unit']['values']['CurAll']);
                $boolCurrency = true;
            }
            elseif (isset(static::$arInitParams['SITE_ID']))
            {
                $strCurrency = CSaleLang::GetLangCurrency(static::$arInitParams['SITE_ID']);
                if (!empty($strCurrency))
                {
                    $arAtoms['Unit']['values']['CurEach'] = str_replace('#CUR#', $strCurrency, $arAtoms['Unit']['values']['CurEach']);
                    $arAtoms['Unit']['values']['CurAll'] = str_replace('#CUR#', $strCurrency, $arAtoms['Unit']['values']['CurAll']);
                    $boolCurrency = true;
                }
            }
        }
        if (!$boolCurrency)
        {
            unset($arAtoms['Unit']['values']['CurEach']);
            unset($arAtoms['Unit']['values']['CurAll']);
        }
        return array(
            'controlId' => static::GetControlID(),
            'group' => true,
            'label' => Loc::getMessage('BT_SALE_ACT_CUSTOM_CALC_LABEL'),
            'defaultText' => Loc::getMessage('BT_SALE_ACT_CUSTOM_CALC_DEF_TEXT'),
            'showIn' => static::GetShowIn($arParams['SHOW_IN_GROUPS']),
            'visual' => static::GetVisual(),
            'control' => array(
                Loc::getMessage('BT_SALE_ACT_CUSTOM_CALC_PREFIX'),
                $arAtoms['What']
            ),
            'mess' => array(
                'ADD_CONTROL' => Loc::getMessage('BT_SALE_SUBACT_ADD_CONTROL'),
                'SELECT_CONTROL' => Loc::getMessage('BT_SALE_SUBACT_SELECT_CONTROL')
            )
        );
    }

    public static function GetShowIn($arControls)
    {
        return array(AMSaleActionCtrlGroup::GetControlID());
    }

    public static function GetAtoms()
    {
        return static::GetAtomsEx(false, false);
    }

    public static function GetAtomsEx($strControlID = false, $boolEx = false)
    {
        $boolEx = (true === $boolEx ? true : false);

        $arPriceTypes = array(
            'Price_All' => Loc::getMessage('PRICE_CHANGER_ALL_PRICE'),
            'Price_Purchase' => Loc::getMessage('PRICE_CHANGER_PURCHASE_PRICE')
        );

        $dbPriceType = CCatalogGroup::GetList(
            array ("SORT" => "ASC"), array ()
        );
        while ($arPriceType = $dbPriceType->Fetch()){
            $arPriceTypes['Price_'.$arPriceType['ID']] = $arPriceType['NAME_LANG'];
        }
        $arPriceTypes['QUANTITY'] = Loc::getMessage('PRICE_CHANGER_QUANTITY');
        $arPriceTypes['QUANTITY_RESERVED'] = Loc::getMessage('PRICE_CHANGER_QUANTITY_RESERVED');
        $arPriceTypes['MEASURE_RATIO'] = Loc::getMessage('PRICE_CHANGER_MEASURE_RATIO');
        $arPriceTypes['WEIGHT'] = Loc::getMessage('PRICE_CHANGER_WEIGHT');
        $arPriceTypes['LENGTH'] = Loc::getMessage('PRICE_CHANGER_LENGTH');
        $arPriceTypes['WIDTH'] = Loc::getMessage('PRICE_CHANGER_WIDTH');
        $arPriceTypes['HEIGHT'] = Loc::getMessage('PRICE_CHANGER_HEIGHT');
        $arAtomList = array(
            'What' => array(
                'JS' => array(
                    'id' => 'What',
                    'name' => 'extra',
                    'type' => 'select',
                    'values' => $arPriceTypes,
                    'defaultText' => Loc::getMessage('BT_SALE_ACT_CUSTOM_CALC_SELECT_TYPE_DEF'),
                    'defaultValue' => 'Price_All',
                    'first_option' => '...'
                ),
                'ATOM' => array(
                    'ID' => 'What',
                    'FIELD_TYPE' => 'string',
                    'FIELD_LENGTH' => 255,
                    'MULTIPLE' => 'N',
                    'VALIDATE' => 'list'
                )
            )
        );

        if (!$boolEx)
        {
            foreach ($arAtomList as &$arOneAtom)
            {
                $arOneAtom = $arOneAtom['JS'];
            }
            if (isset($arOneAtom))
                unset($arOneAtom);
        }

        return $arAtomList;
    }

}

class AMSaleActionCtrlSubGroup extends AMGlobalCondCtrlGroup
{
    public static function GetClassName()
    {
        return __CLASS__;
    }

    public static function GetControlID()
    {
        return 'ActSaleSubGrp';
    }

    public static function GetAtoms(){
        return array (
            'function'  => array (
                'id'           => 'function',
                'name'         => 'function',
                'type'         => 'input',
                'defaultText'  => Loc::getMessage('BT_CLOBAL_COND_GROUP_TEXT_DEF'),
                'first_option' => '...'
            ),
            'logic' => array (
                'id'           => 'logic',
                'name'         => 'logic',
                'type'         => 'select',
                'values'       => array (
                    'Plus'  => Loc::getMessage('BT_COND_LOGIC_PLUS_LABEL'),
                    'Minus' => Loc::getMessage('BT_COND_LOGIC_MINUS_LABEL'),
                    'Multiply' => Loc::getMessage('BT_COND_LOGIC_MULTIPLY_LABEL'),
                    'Devided' => Loc::getMessage('BT_COND_LOGIC_DIVIDE_LABEL'),
                ),
                'defaultText'  => Loc::getMessage('OP_DEF_TEXT'),
                'defaultValue' => Loc::getMessage('OP_DEF_VAL'),
                'first_option' => '...'
            )
        );

    }

    public static function GetControlShow($arParams){
        return array (
            'controlId'   => static::GetControlID(),
            'group'       => true,
            'label'       => Loc::getMessage('BT_CLOBAL_COND_GROUP_S'),
            'defaultText' => Loc::getMessage('BT_CLOBAL_COND_GROUP_DEF_TEXT'),
            'showIn'      => static::GetShowIn($arParams[ 'SHOW_IN_GROUPS' ]),
            'visual'      => static::GetVisual(),
            'control'     => array_values(static::GetAtoms())
        );
    }

    public static function GetShowIn($arControls)
    {
        $arControls = array(AMSaleActionCtrlCatalogGroup::GetControlID(), AMSaleActionCtrlSubGroup::GetControlID());
        return $arControls;
    }
}

class AMSaleActionCondCtrlCatalogFields extends AMSaleActionCtrlComplex
{
    public static function GetClassName()
    {
        return __CLASS__;
    }

    public static function GetValueAtom($arValue){
        if (empty($arValue) || !isset($arValue[ 'type' ])){
            $arResult = array (
                'type' => 'input'
            );
        }else{
            $arResult = $arValue;
        }
        $arResult[ 'id' ] = 'function';
        $arResult[ 'name' ] = 'function';

        $arResult['defaultText'] =  Loc::getMessage('BX_PRICE_CHANGER_F');
        return $arResult;
    }


    public static function GetControlShow($arParams)
    {
        $arControls = static::GetControls();

        $arResult = array ();
        $intCount = -1;
        foreach ($arControls as &$arOneControl)
        {

            if (isset($arOneControl[ 'SEP' ]) && 'Y' == $arOneControl[ 'SEP' ]){
                $intCount++;
                $arResult[ $intCount ] = array (
                    'controlgroup' => true,
                    'group'        => false,
                    'label'        => $arOneControl[ 'SEP_LABEL' ],
                    'showIn'       => static::GetShowIn($arParams[ 'SHOW_IN_GROUPS' ]),
                    'children'     => array ()
                );
            }
            if( $arOneControl[ 'ID' ] != 'CustomValue' )
            {
                $controls = array (
                    static::GetValueAtom($arOneControl['JS_VALUE']),
                    array(
                        'id' => 'prefix',
                        'type' => 'prefix',
                        'text' => '( '.$arOneControl['PREFIX']." )"
                    ),
                    static::GetLogicAtom($arOneControl['LOGIC']),
                );
            }else{
                $controls = array (
                    static::GetValueAtom(
                        array(
                            'type' => 'input'
                        )
                    ),
                    array(
                        'id' => 'value',
                        'name' => 'value',
                        'type' => 'input',
                        'defaultText' =>  Loc::getMessage('BX_NUMBER')
                    ),
                    static::GetLogicAtom(static::GetLogic(array(BT_COND_LOGIC_PLUS, BT_COND_LOGIC_MINUS, BT_COND_LOGIC_MULTIPLY, BT_COND_LOGIC_DIVIDE))),
                );
            }

            $arResult[ $intCount ][ 'children' ][ ] = array (
                'controlId' => $arOneControl[ 'ID' ],
                'group'     => false,
                'label'     => $arOneControl[ 'LABEL' ],
                'showIn'    => static::GetShowIn($arParams[ 'SHOW_IN_GROUPS' ]),
                'control'   => $controls
            );
        }
        if (isset($arOneControl))
            unset($arOneControl);

        return $arResult;
    }

    public static function GetControls($strControlID = false)
    {
        $arControlList = array(
            'CondIBElement'        => array (
                'ID'         => 'CondIBElement',
                'FIELD'      => 'ID',
                'FIELD_TYPE' => 'int',
                'LABEL'      => Loc::getMessage('BT_MOD_CATALOG_COND_CMP_IBLOCK_ELEMENT_ID_LABEL_ACT'),
                'PREFIX'     => Loc::getMessage('BT_MOD_CATALOG_COND_CMP_IBLOCK_ELEMENT_ID_PREFIX_ACT'),
                'LOGIC'      => static::GetLogic(array(BT_COND_LOGIC_PLUS, BT_COND_LOGIC_MINUS, BT_COND_LOGIC_MULTIPLY, BT_COND_LOGIC_DIVIDE)),
                'SEP'           => 'Y',
                'SEP_LABEL'     => Loc::getMessage('BT_MOD_SALE_ACT_CUSTOM_CALC_FIELDS_LABEL_CATALOG'),
                'JS_VALUE' => array(
                    'type' => 'input'
                )
            ),
            'CondIBIBlock'         => array (
                'ID'         => 'CondIBIBlock',
                'FIELD'      => 'IBLOCK_ID',
                'FIELD_TYPE' => 'int',
                'LABEL'      => Loc::getMessage('BT_MOD_CATALOG_COND_CMP_IBLOCK_IBLOCK_ID_LABEL_ACT'),
                'PREFIX'     => Loc::getMessage('BT_MOD_CATALOG_COND_CMP_IBLOCK_IBLOCK_ID_PREFIX_ACT'),
                'LOGIC'      => static::GetLogic(array(BT_COND_LOGIC_PLUS, BT_COND_LOGIC_MINUS, BT_COND_LOGIC_MULTIPLY, BT_COND_LOGIC_DIVIDE)),
                'JS_VALUE' => array(
                    'type' => 'input'
                ),
            ),
            'CondIBSection'        => array (
                'ID'         => 'CondIBSection',
                'PARENT'     => false,
                'FIELD'      => 'SECTION_ID',
                'FIELD_TYPE' => 'int',
                'LABEL'      => Loc::getMessage('BT_MOD_CATALOG_COND_CMP_IBLOCK_SECTION_ID_LABEL_ACT'),
                'PREFIX'     => Loc::getMessage('BT_MOD_CATALOG_COND_CMP_IBLOCK_SECTION_ID_PREFIX_ACT'),
                'LOGIC'      => static::GetLogic(array(BT_COND_LOGIC_PLUS, BT_COND_LOGIC_MINUS, BT_COND_LOGIC_MULTIPLY, BT_COND_LOGIC_DIVIDE)),
                'JS_VALUE' => array(
                    'type' => 'input'
                )
            ),
            'CondIBSort'           => array (
                'ID'         => 'CondIBSort',
                'FIELD'      => 'SORT',
                'FIELD_TYPE' => 'int',
                'LABEL'      => Loc::getMessage('BT_MOD_CATALOG_COND_CMP_IBLOCK_SORT_LABEL_ACT'),
                'PREFIX'     => Loc::getMessage('BT_MOD_CATALOG_COND_CMP_IBLOCK_SORT_PREFIX_ACT'),
                'LOGIC'      => static::GetLogic(array(BT_COND_LOGIC_PLUS, BT_COND_LOGIC_MINUS, BT_COND_LOGIC_MULTIPLY, BT_COND_LOGIC_DIVIDE)),
                'JS_VALUE'   => array (
                    'type' => 'input'
                ),
                'PHP_VALUE'  => ''
            ),
            'CondIBCreatedBy'      => array (
                'ID'         => 'CondIBCreatedBy',
                'FIELD'      => 'CREATED_BY',
                'FIELD_TYPE' => 'int',
                'LABEL'      => Loc::getMessage('BT_MOD_CATALOG_COND_CMP_IBLOCK_CREATED_BY_LABEL_ACT'),
                'PREFIX'     => Loc::getMessage('BT_MOD_CATALOG_COND_CMP_IBLOCK_CREATED_BY_PREFIX_ACT'),
                'LOGIC'      => static::GetLogic(array(BT_COND_LOGIC_PLUS, BT_COND_LOGIC_MINUS, BT_COND_LOGIC_MULTIPLY, BT_COND_LOGIC_DIVIDE)),
                'JS_VALUE'   => array (
                    'type' => 'input'
                ),
                'PHP_VALUE'  => array (
                    'VALIDATE' => 'user'
                )
            ),
            'CondIBModifiedBy'     => array (
                'ID'         => 'CondIBModifiedBy',
                'FIELD'      => 'MODIFIED_BY',
                'FIELD_TYPE' => 'int',
                'LABEL'      => Loc::getMessage('BT_MOD_CATALOG_COND_CMP_IBLOCK_MODIFIED_BY_LABEL_ACT'),
                'PREFIX'     => Loc::getMessage('BT_MOD_CATALOG_COND_CMP_IBLOCK_MODIFIED_BY_PREFIX_ACT'),
                'LOGIC'      => static::GetLogic(array(BT_COND_LOGIC_PLUS, BT_COND_LOGIC_MINUS, BT_COND_LOGIC_MULTIPLY, BT_COND_LOGIC_DIVIDE)),
                'JS_VALUE'   => array (
                    'type' => 'input'
                ),
                'PHP_VALUE'  => array (
                    'VALIDATE' => 'user'
                )
            ),
            'CondCatQuantity'      => array (
                'ID'            => 'CondCatQuantity',
                'PARENT'        => false,
                'MODULE_ENTITY' => 'catalog',
                'ENTITY'        => 'PRODUCT',
                'FIELD'         => 'CATALOG_QUANTITY',
                'FIELD_TABLE'   => 'QUANTITY',
                'FIELD_TYPE'    => 'double',
                'LABEL'         => Loc::getMessage('BT_MOD_CATALOG_COND_CMP_CATALOG_QUANTITY_LABEL_ACT'),
                'PREFIX'        => Loc::getMessage('BT_MOD_CATALOG_COND_CMP_CATALOG_QUANTITY_PREFIX_ACT'),
                'LOGIC'         => static::GetLogic(array(BT_COND_LOGIC_PLUS, BT_COND_LOGIC_MINUS, BT_COND_LOGIC_MULTIPLY, BT_COND_LOGIC_DIVIDE)),
                'JS_VALUE'      => array (
                    'type' => 'input'
                )
            ),
            'CondCatWeight'        => array (
                'ID'            => 'CondCatWeight',
                'PARENT'        => false,
                'MODULE_ENTITY' => 'catalog',
                'ENTITY'        => 'PRODUCT',
                'FIELD'         => 'CATALOG_WEIGHT',
                'FIELD_TABLE'   => 'WEIGHT',
                'FIELD_TYPE'    => 'double',
                'LABEL'         => Loc::getMessage('BT_MOD_CATALOG_COND_CMP_CATALOG_WEIGHT_LABEL_ACT'),
                'PREFIX'        => Loc::getMessage('BT_MOD_CATALOG_COND_CMP_CATALOG_WEIGHT_PREFIX_ACT'),
                'LOGIC'         => static::GetLogic(array(BT_COND_LOGIC_PLUS, BT_COND_LOGIC_MINUS, BT_COND_LOGIC_MULTIPLY, BT_COND_LOGIC_DIVIDE)),
                'JS_VALUE'      => array (
                    'type' => 'input'
                ),
                'PHP_VALUE'     => ''
            ),
            'CondCatWidth'        => array (
                'ID'            => 'CondCatWidth',
                'PARENT'        => false,
                'MODULE_ENTITY' => 'catalog',
                'ENTITY'        => 'PRODUCT',
                'FIELD'         => 'CATALOG_WIDTH',
                'FIELD_TABLE'   => 'WEIGHT',
                'FIELD_TYPE'    => 'double',
                'LABEL'         => Loc::getMessage('BT_MOD_CATALOG_COND_CMP_CATALOG_WIDTH_LABEL_ACT'),
                'PREFIX'        => Loc::getMessage('BT_MOD_CATALOG_COND_CMP_CATALOG_WIDTH_PREFIX_ACT'),
                'LOGIC'         => static::GetLogic(array(BT_COND_LOGIC_PLUS, BT_COND_LOGIC_MINUS, BT_COND_LOGIC_MULTIPLY, BT_COND_LOGIC_DIVIDE)),
                'JS_VALUE'      => array (
                    'type' => 'input'
                ),
                'PHP_VALUE'     => ''
            ),
            'CondCatLength'        => array (
                'ID'            => 'CondCatLength',
                'PARENT'        => false,
                'MODULE_ENTITY' => 'catalog',
                'ENTITY'        => 'PRODUCT',
                'FIELD'         => 'CATALOG_LENGTH',
                'FIELD_TABLE'   => 'WEIGHT',
                'FIELD_TYPE'    => 'double',
                'LABEL'         => Loc::getMessage('BT_MOD_CATALOG_COND_CMP_CATALOG_LENGTH_LABEL_ACT'),
                'PREFIX'        => Loc::getMessage('BT_MOD_CATALOG_COND_CMP_CATALOG_LENGTH_PREFIX_ACT'),
                'LOGIC'         => static::GetLogic(array(BT_COND_LOGIC_PLUS, BT_COND_LOGIC_MINUS, BT_COND_LOGIC_MULTIPLY, BT_COND_LOGIC_DIVIDE)),
                'JS_VALUE'      => array (
                    'type' => 'input'
                ),
                'PHP_VALUE'     => ''
            ),
            'CondCatHeight'        => array (
                'ID'            => 'CondCatHeight',
                'PARENT'        => false,
                'MODULE_ENTITY' => 'catalog',
                'ENTITY'        => 'PRODUCT',
                'FIELD'         => 'CATALOG_HEIGHT',
                'FIELD_TABLE'   => 'WEIGHT',
                'FIELD_TYPE'    => 'double',
                'LABEL'         => Loc::getMessage('BT_MOD_CATALOG_COND_CMP_CATALOG_HEIGHT_LABEL_ACT'),
                'PREFIX'        => Loc::getMessage('BT_MOD_CATALOG_COND_CMP_CATALOG_HEIGHT_PREFIX_ACT'),
                'LOGIC'         => static::GetLogic(array(BT_COND_LOGIC_PLUS, BT_COND_LOGIC_MINUS, BT_COND_LOGIC_MULTIPLY, BT_COND_LOGIC_DIVIDE)),
                'JS_VALUE'      => array (
                    'type' => 'input'
                ),
                'PHP_VALUE'     => ''
            ),
            'CondCatVatID'         => array (
                'ID'            => 'CondCatVatID',
                'PARENT'        => false,
                'MODULE_ENTITY' => 'catalog',
                'ENTITY'        => 'PRODUCT',
                'FIELD'         => 'CATALOG_VAT_ID',
                'FIELD_TABLE'   => 'VAT_ID',
                'FIELD_TYPE'    => 'int',
                'LABEL'         => Loc::getMessage('BT_MOD_CATALOG_COND_CMP_CATALOG_VAT_ID_LABEL_ACT'),
                'PREFIX'        => Loc::getMessage('BT_MOD_CATALOG_COND_CMP_CATALOG_VAT_ID_PREFIX_ACT'),
                'LOGIC'         => static::GetLogic(array(BT_COND_LOGIC_PLUS, BT_COND_LOGIC_MINUS, BT_COND_LOGIC_MULTIPLY, BT_COND_LOGIC_DIVIDE)),
                'JS_VALUE'      => array (
                    'type'   => 'input',
                )
            ),
        );


        $arControlList['CondIBPrice:Purchase'] =  array (
                'ID'         => 'CondIBPrice:Purchase',
                'FIELD'      => 'CATALOG_PRICE_PURCHASE',
                'FIELD_TYPE' => 'int',
                'LABEL'      => Loc::getMessage('PRICE_CHANGER_PURCHASE_PRICE'),
                'PREFIX'     => Loc::getMessage('PRICE_CHANGER_PURCHASE_PRICE'),
                'LOGIC'      => static::GetLogic(array(BT_COND_LOGIC_PLUS, BT_COND_LOGIC_MINUS, BT_COND_LOGIC_MULTIPLY, BT_COND_LOGIC_DIVIDE)),
                'JS_VALUE'   => array (
                    'type' => 'input'
                ),
                'PHP_VALUE'  => ''
        );

        $dbPriceType = CCatalogGroup::GetList(
            array ("SORT" => "ASC"), array ()
        );

        while ($arPriceType = $dbPriceType->Fetch()){
            $arControlListPrice = array (
                'CondIBPrice:' . $arPriceType[ 'ID' ]       => array (
                    'ID'         => 'CondIBPrice:' . $arPriceType[ 'ID' ],
                    'FIELD'      => 'CATALOG_PRICE_' . $arPriceType[ 'ID' ],
                    'FIELD_TYPE' => 'int',
                    'LABEL'      => $arPriceType[ 'NAME_LANG' ],
                    'PREFIX'     => $arPriceType[ 'NAME_LANG' ],
                    'LOGIC'      => static::GetLogic(array(BT_COND_LOGIC_PLUS, BT_COND_LOGIC_MINUS, BT_COND_LOGIC_MULTIPLY, BT_COND_LOGIC_DIVIDE)),
                    'JS_VALUE'   => array (
                        'type' => 'input'
                    ),
                    'PHP_VALUE'  => ''
                ),
            );
            $arControlList = array_merge($arControlList, $arControlListPrice);
        }

        /**
         * Properties
         */

        $arIBlockList = array ();
        $rsIBlocks = CCatalog::GetList(
            array (), array (), false, false, array (
                        'IBLOCK_ID',
                        'PRODUCT_IBLOCK_ID'
                    )
        );
        while ($arIBlock = $rsIBlocks->Fetch()){
            $arIBlock[ 'IBLOCK_ID' ] = (int) $arIBlock[ 'IBLOCK_ID' ];
            $arIBlock[ 'PRODUCT_IBLOCK_ID' ] = (int) $arIBlock[ 'PRODUCT_IBLOCK_ID' ];
            if ($arIBlock[ 'IBLOCK_ID' ] > 0){
                $arIBlockList[ $arIBlock[ 'IBLOCK_ID' ] ] = true;
            }
            if ($arIBlock[ 'PRODUCT_IBLOCK_ID' ] > 0){
                $arIBlockList[ $arIBlock[ 'PRODUCT_IBLOCK_ID' ] ] = true;
            }
        }
        unset($arIBlock, $rsIBlocks);
        if ( !empty($arIBlockList)){
            $arIBlockList = array_keys($arIBlockList);
            sort($arIBlockList);
            foreach ($arIBlockList as &$intIBlockID){
                $strName = CIBlock::GetArrayByID($intIBlockID, 'NAME');
                if (false !== $strName){
                    $boolSep = true;
                    $rsProps = CIBlockProperty::GetList(
                        array (
                            'SORT' => 'ASC',
                            'NAME' => 'ASC'
                        ), array ('IBLOCK_ID' => $intIBlockID)
                    );
                    while ($arProp = $rsProps->Fetch()){
                        if ('CML2_LINK' == $arProp[ 'XML_ID' ] || 'F' == $arProp[ 'PROPERTY_TYPE' ] || $arProp[ 'USER_TYPE' ] == "DateTime"){
                            continue;
                        }
                        if ('L' == $arProp[ 'PROPERTY_TYPE' ]){
                            $arProp[ 'VALUES' ] = array ();
                            $rsPropEnums = CIBlockPropertyEnum::GetList(
                                array (
                                    'DEF'  => 'DESC',
                                    'SORT' => 'ASC'
                                ), array ('PROPERTY_ID' => $arProp[ 'ID' ])
                            );
                            while ($arPropEnum = $rsPropEnums->Fetch()){
                                $arProp[ 'VALUES' ][ ] = $arPropEnum;
                            }
                            if (empty($arProp[ 'VALUES' ])){
                                continue;
                            }
                        }

                        $strFieldType = '';
                        $arLogic = array ();
                        $arValue = array ();
                        $arPhpValue = '';

                        $boolUserType = false;

                        if ( !$boolUserType){
                            switch ($arProp[ 'PROPERTY_TYPE' ]){
                                case 'N':
                                    $strFieldType = 'double';
                                    $arLogic = static::GetLogic(array(BT_COND_LOGIC_PLUS, BT_COND_LOGIC_MINUS, BT_COND_LOGIC_MULTIPLY, BT_COND_LOGIC_DIVIDE));
                                    $arValue = array ('type' => 'input');
                                    break;
                                case 'S':
                                    $strFieldType = 'double';
                                    $arLogic = static::GetLogic(array(BT_COND_LOGIC_PLUS, BT_COND_LOGIC_MINUS, BT_COND_LOGIC_MULTIPLY, BT_COND_LOGIC_DIVIDE));
                                    $arValue = array ('type' => 'input');
                                    break;
                                case 'L':
                                    $strFieldType = 'int';
                                    $arLogic = static::GetLogic(array(BT_COND_LOGIC_PLUS, BT_COND_LOGIC_MINUS, BT_COND_LOGIC_MULTIPLY, BT_COND_LOGIC_DIVIDE));
                                    $arValue = array ('type' => 'input');
                                    break;
                                case 'E':
                                    $strFieldType = 'int';
                                    $arLogic = static::GetLogic(array(BT_COND_LOGIC_PLUS, BT_COND_LOGIC_MINUS, BT_COND_LOGIC_MULTIPLY, BT_COND_LOGIC_DIVIDE));
                                    $arValue = array ('type' => 'input');
                                    break;
                                case 'G':
                                    $strFieldType = 'int';
                                    $arLogic = static::GetLogic(array(BT_COND_LOGIC_PLUS, BT_COND_LOGIC_MINUS, BT_COND_LOGIC_MULTIPLY, BT_COND_LOGIC_DIVIDE));
                                    $arValue = array ('type' => 'input');
                                    break;
                            }
                        }
                        $arControlList[ 'CondIBProp:' . $intIBlockID . ':' . $arProp[ 'ID' ] ] = array (
                            'ID'            => 'CondIBProp:' . $intIBlockID . ':' . $arProp[ 'ID' ],
                            'PARENT'        => false,
                            'EXIST_HANDLER' => 'Y',
                            'MODULE_ID'     => 'catalog',
                            'MODULE_ENTITY' => 'iblock',
                            'ENTITY'        => 'ELEMENT_PROPERTY',
                            'IBLOCK_ID'     => $intIBlockID,
                            'FIELD'         => 'PROPERTY_' . $arProp[ 'ID' ] . '_VALUE',
                            'FIELD_TABLE'   => $intIBlockID . ':' . $arProp[ 'ID' ],
                            'FIELD_TYPE'    => $strFieldType,
                            'MULTIPLE'      => 'Y',
                            'GROUP'         => 'N',
                            'SEP'           => ($boolSep ? 'Y' : 'N'),
                            'SEP_LABEL'     => ($boolSep ? str_replace(
                                array (
                                    '#ID#',
                                    '#NAME#'
                                ), array (
                                    $intIBlockID,
                                    $strName
                                ), Loc::getMessage('BT_MOD_CATALOG_COND_CMP_CATALOG_PROP_LABEL')
                            ) : ''),
                            'LABEL'         => $arProp[ 'NAME' ],
                            'PREFIX'        => str_replace(
                                array (
                                    '#NAME#',
                                    '#IBLOCK_ID#',
                                    '#IBLOCK_NAME#'
                                ), array (
                                    $arProp[ 'NAME' ],
                                    $intIBlockID,
                                    $strName
                                ), Loc::getMessage('BT_MOD_CATALOG_COND_CMP_CATALOG_ONE_PROP_PREFIX')
                            ),
                            'LOGIC'         => $arLogic,
                            'JS_VALUE'      => $arValue,
                            'PHP_VALUE'     => $arPhpValue
                        );

                        $boolSep = false;
                    }
                }
            }
            if (isset($intIBlockID)){
                unset($intIBlockID);
            }
            unset($arIBlockList);
        }

        /**
         * end properties
         */

        $arControlList['CustomValue'] = array (
            'ID'         => 'CustomValue',
            'FIELD'      => 'CustomValue',
            'FIELD_TYPE' => 'int',
            'LABEL'      => GetMessage('CUSTOM_VAL_LABEL'),
            'LOGIC'      => static::GetLogic(array(BT_COND_LOGIC_PLUS, BT_COND_LOGIC_MINUS, BT_COND_LOGIC_MULTIPLY, BT_COND_LOGIC_DIVIDE)),
            'SEP'           => 'Y',
            'SEP_LABEL'     => GetMessage('CUSTOM_VAL_LABEL'),
            'JS_VALUE' => array(
                'type' => 'input'
            )
        );

        foreach ($arControlList as &$control)
        {
            $control['MODULE_ID'] = 'sale';
            $control['MODULE_ENTITY'] = 'sale';
            $control['ENTITY'] = 'BASKET';
            $control['MULTIPLE'] = 'N';
            $control['GROUP'] = 'N';
        }
        unset($control);

        if (false === $strControlID)
        {
            return $arControlList;
        }
        elseif (isset($arControlList[$strControlID]))
        {
            return $arControlList[$strControlID];
        }
        else
        {
            return false;
        }
    }

    public static function GetShowIn($arControls)
    {
        $arControls = array(AMSaleActionCtrlCatalogGroup::GetControlID(), AMSaleActionCtrlSubGroup::GetControlID());
        return $arControls;
    }
}


class AMSaleActionTree extends AMGlobalCondTree
{
    protected $arExecuteFunc = array();
    protected $executeModule = array();

    public function __construct()
    {
        parent::__construct();
    }

    public function __destruct()
    {
        parent::__destruct();
    }

    public function Generate($arConditions, $arParams)
    {
        $strFinal = '';
        $this->arExecuteFunc = array();
        $this->usedModules = array();
        $this->usedExtFiles = array();
        $this->usedEntity = array();
        $this->executeModule = array();
        if (!$this->boolError)
        {
            $strResult = '';
            if (!empty($arConditions) && is_array($arConditions))
            {
                $arParams['FUNC_ID'] = '';
                $arResult = $this->GenerateLevel($arConditions, $arParams, true);
                if (empty($arResult))
                {
                    $strResult = '';
                    $this->boolError = true;
                }
                else
                {
                    $strResult = current($arResult);
                }
            }
            else
            {
                $this->boolError = true;
            }
            if (!$this->boolError)
            {
                $strFinal = preg_replace("#;{2,}#",";", $strResult);
            }
            return $strFinal;
        }
        else
        {
            return '';
        }
    }

    public function GenerateLevel(&$arLevel, $arParams, $boolFirst = false)
    {
        $arResult = array();
        $boolFirst = ($boolFirst === true);
        if (empty($arLevel) || !is_array($arLevel))
        {
            return $arResult;
        }
        if (!isset($arParams['FUNC_ID']))
        {
            $arParams['FUNC_ID'] = '';
        }
        $intRowNum = 0;
        if ($boolFirst)
        {
            $arParams['ROW_NUM'] = $intRowNum;
            if (!empty($arLevel['CLASS_ID']))
            {
                if (isset($this->arControlList[$arLevel['CLASS_ID']]))
                {
                    $arOneControl = $this->arControlList[$arLevel['CLASS_ID']];
                    $strEval = false;
                    if ('Y' == $arOneControl['GROUP'])
                    {
                        $arSubParams = $arParams;
                        $arSubParams['FUNC_ID'] .= '_'.$intRowNum;
                        $arSubEval = $this->GenerateLevel($arLevel['CHILDREN'], $arSubParams);
                        if (false === $arSubEval || !is_array($arSubEval))
                            return false;
                        $arGroupParams = $arParams;
                        $arGroupParams['FUNC_ID'] .= '_'.$intRowNum;
                        $mxEval = call_user_func_array($arOneControl['Generate'],
                                                       array($arLevel['DATA'], $arGroupParams, $arLevel['CLASS_ID'], $arSubEval)
                        );
                        if (is_array($mxEval))
                        {
                            if (isset($mxEval['FUNC']))
                            {
                                $this->arExecuteFunc[] = $mxEval['FUNC'];
                            }
                            $strEval = (isset($mxEval['COND']) ? $mxEval['COND'] : false);
                        }
                        else
                        {
                            $strEval = $mxEval;
                        }
                    }
                    else
                    {
                        $strEval = call_user_func_array($arOneControl['Generate'],
                                                        array($arLevel['DATA'], $arParams, $arLevel['CLASS_ID'])
                        );
                    }
                    if (false === $strEval || !is_string($strEval) || 'false' === $strEval)
                    {
                        return false;
                    }
                    $arResult[] = $strEval;
                    $this->fillUsedData($arOneControl);
                }
            }
            $intRowNum++;
        }
        else
        {
            foreach ($arLevel as &$arOneCondition)
            {
                $arParams['ROW_NUM'] = $intRowNum;
                if (!empty($arOneCondition['CLASS_ID']))
                {
                    if (isset($this->arControlList[$arOneCondition['CLASS_ID']]))
                    {
                        $arOneControl = $this->arControlList[$arOneCondition['CLASS_ID']];
                        $strEval = false;
                        if ('Y' == $arOneControl['GROUP'])
                        {
                            $arSubParams = $arParams;
                            $arSubParams['FUNC_ID'] .= '_'.$intRowNum;
                            $arSubEval = $this->GenerateLevel($arOneCondition['CHILDREN'], $arSubParams);
                            if (false === $arSubEval || !is_array($arSubEval))
                                return false;
                            $arGroupParams = $arParams;
                            $arGroupParams['FUNC_ID'] .= '_'.$intRowNum;
                            $mxEval = call_user_func_array($arOneControl['Generate'],
                                                           array($arOneCondition['DATA'], $arGroupParams, $arOneCondition['CLASS_ID'], $arSubEval)
                            );
                            if (is_array($mxEval))
                            {
                                if (isset($mxEval['FUNC']))
                                {
                                    $this->arExecuteFunc[] = $mxEval['FUNC'];
                                }
                                $strEval = (isset($mxEval['COND']) ? $mxEval['COND'] : false);
                            }
                            else
                            {
                                $strEval = $mxEval;
                            }
                        }
                        else
                        {
                            $strEval = call_user_func_array($arOneControl['Generate'],
                                                            array($arOneCondition['DATA'], $arParams, $arOneCondition['CLASS_ID'])
                            );
                        }
                        if (false === $strEval || !is_string($strEval) || 'false' === $strEval)
                        {
                            return false;
                        }
                        $arResult[] = $strEval;
                        $this->fillUsedData($arOneControl);
                    }
                }
                $intRowNum++;
            }
            if (isset($arOneCondition))
                unset($arOneCondition);
        }

        if (!empty($arResult))
        {
            foreach ($arResult as $key => $value)
            {
                if ('' == $value || '()' == $value)
                    unset($arResult[$key]);
            }
        }
        if (!empty($arResult))
            $arResult = array_values($arResult);

        return $arResult;
    }

    public function GetExecuteModule()
    {
        return (!empty($this->executeModule) ? array_keys($this->executeModule) : array());
    }

    protected function fillUsedData(&$control)
    {
        parent::fillUsedData($control);
        if (!empty($control['EXECUTE_MODULE']))
            $this->executeModule[$control['EXECUTE_MODULE']] = true;
    }
}
?>