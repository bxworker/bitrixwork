<?php

namespace Project\Discounts\Ctrls;

use CCatalogCondCtrlIBlockFields;
use CSaleActionCtrlBasketGroup;

class CondCtrlAct extends CCatalogCondCtrlIBlockFields
{
    public static function GetControlDescr(): array
    {
        return ['SORT' => 1] + parent::GetControlDescr();
    }

    public static function GetControlID(): false|array
    {
        return [
            'CondIBIBlockCode',
            'CondIBSectionCode',
        ];
    }

    public static function GetShowIn($arControls)
    {
        return [
            DiscountCtrlAct::GetControlID(),
            CSaleActionCtrlBasketGroup::GetControlID()
        ];
    }

    public static function GetControlShow($arParams): false|array
    {
        $result = [
            'controlgroup' => true,
            'group' => false,
            'label' => 'Поля инфоблока и раздела',
            'showIn' => static::GetShowIn($arParams['SHOW_IN_GROUPS']),
            'children' => [],
        ];

        foreach (static::GetControls() as $control) {
            $result['children'][] = [
                'controlId' => $control['ID'],
                'group' => false,
                'label' => $control['LABEL'],
                'showIn' => static::GetShowIn($arParams['SHOW_IN_GROUPS']),
                'control' => [
                    [
                        'id' => 'prefix',
                        'type' => 'prefix',
                        'text' => $control['PREFIX'],
                    ],
                    static::GetLogicAtom($control['LOGIC']),
                    static::GetValueAtom($control['JS_VALUE']),
                ]
            ];
        }

        return $result;
    }

    public static function GetControls($strControlID = false): false|array
    {
        $controls = [
            'CondIBIBlockCode' => [
                'ID' => 'CondIBIBlockCode',
                'FIELD' => 'CODE',
                'FIELD_TYPE' => 'string',
                'FIELD_LENGTH' => 255,
                'LABEL' => 'Символьный код инфоблока',
                'PREFIX' => 'Символьный код инфоблока',
                'MODULE_ID' => 'iblock',
                'MODULE_ENTITY' => 'iblock',
                'ENTITY' => 'IBLOCK',
                'LOGIC' => static::GetLogic([BT_COND_LOGIC_EQ, BT_COND_LOGIC_NOT_EQ]),
                'JS_VALUE' => [
                    'type' => 'input'
                ],
                'PHP_VALUE' => ''
            ],
            'CondIBSectionCode' => [
                'ID' => 'CondIBSectionCode',
                'MULTIPLE' => 'Y',
                'FIELD' => 'CODE',
                'FIELD_TYPE' => 'string',
                'FIELD_LENGTH' => 255,
                'LABEL' => 'Символьный код раздела',
                'PREFIX' => 'Символьный код раздела',
                'MODULE_ID' => 'iblock',
                'MODULE_ENTITY' => 'section',
                'ENTITY' => 'IBLOCK_SECTION',
                'LOGIC' => static::GetLogic([BT_COND_LOGIC_EQ, BT_COND_LOGIC_NOT_EQ]),
                'JS_VALUE' => [
                    'type' => 'input'
                ],
                'PHP_VALUE' => ''
            ],
        ];

        foreach ($controls as &$control)
        {
            if (!isset($control['PARENT'])) {
                $control['PARENT'] = true;
            }

            $control['EXIST_HANDLER'] = 'Y';

            if (!isset($control['FIELD_TABLE'])) {
                $control['FIELD_TABLE'] = false;
            }

            if (!isset($control['MULTIPLE'])) {
                $control['MULTIPLE'] = 'N';
            }

            $control['GROUP'] = 'N';
            $control['ENTITY_ID'] = -1;
        }

        return static::searchControl($controls, $strControlID);
    }

    public static function Generate($arOneCondition, $arParams, $arControl, $arSubs = false): false|string
    {
        if (is_string($arControl)) {
            $arControl = static::GetControls($arControl);
        }

        if (!is_array($arControl)) {
            return false;
        }

        $arParams['FIELD'] = $arParams['BASKET_ROW'] . '[\'' . $arControl['ENTITY'] . '\']';

        return parent::Generate($arOneCondition, $arParams, $arControl, $arSubs);
    }
}
