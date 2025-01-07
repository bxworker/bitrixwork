<?php

declare(strict_types=1);

namespace Project\Discounts\Ctrls;

use Bitrix\Sale\Discount\Actions;
use CSaleActionCtrlAction;
use CSaleActionCtrlGroup;
use Project\Helpers\Discounts\CtrlTrait;

final class TestCtrlAction extends CSaleActionCtrlAction
{
    use CtrlTrait;

    public static function GetControlDescr(): array
    {
        return ['SORT' => 500] + parent::GetControlDescr();
    }

    public static function GetShowIn($arControls): array
    {
        return [CSaleActionCtrlGroup::GetControlID()];
    }

    public static function GetControlShow($arParams): array
    {
        $atoms = self::GetAtomsEx();

        return [
            'controlId' => self::GetControlID(),
            'group' => true,
            'label' => '[кастом] Применить скидку на товары ',
            'defaultText' => '[кастом] Скидка на товары',
            'showIn' => self::GetShowIn($arParams['SHOW_IN_GROUPS']),
            'control' => [
                '[кастом] применить скидку',
                $atoms['Value'],
                self::getCurrency()
            ],
            'mess' => array(
                'SELECT_CONTROL' => 'Добавить условие',
                'ADD_CONTROL' => 'Добавить условие',
                'DELETE_CONTROL' => 'Удалить условие',
            )
        ];
    }

    public static function GetAtomsEx($strControlID = false, $boolEx = false): false|array
    {
        $atoms = [
            'Value' => [
                'JS' => [
                    'id' => 'Value',
                    'name' => 'Value',
                    'type' => 'input',
                ],
                'ATOM' => [
                    'ID' => 'Value',
                    'FIELD_TYPE' => 'double',
                    'MULTIPLE' => 'N',
                    'VALIDATE' => '',
                ],
            ],
        ];

        return self::GetAtomsExFinishing($atoms, $boolEx);
    }

    public static function Generate($arOneCondition, $arParams, $arControl, $arSubs = false): false|array
    {
        foreach (self::GetAtomsEx() as $atom) {
            if (!isset($arOneCondition[$atom['id']])) {
                return false;
            }
        }

        if (!isset($arSubs) || !is_array($arSubs)) {
            return false;
        }

        $unit = Actions::VALUE_TYPE_SUMM;

        $arOneCondition['Value'] = (float)$arOneCondition['Value'];
        $discountParams = [
            'VALUE' => -$arOneCondition['Value'],
            'UNIT' => $unit,
            'LIMIT_VALUE' => 0,
        ];

        if (!empty($arSubs)) {
            $filter = '$saleact'.$arParams['FUNC_ID'];
            $commandLine = implode(' && ', $arSubs);

            $mxResult = $filter . '=function($row){';
            $mxResult .= 'return (' . $commandLine . ');';
            $mxResult .= '};';
            $mxResult .= '\Bitrix\Sale\Discount\Actions::applyToBasket(' . $arParams['ORDER'] . ', ' . var_export($discountParams, true) . ', ' . $filter . ');';
        } else {
            $mxResult = '\Bitrix\Sale\Discount\Actions::applyToBasket(' . $arParams['ORDER'] . ', ' . var_export($discountParams, true) . ', "");';
        }

        $result = [
            'COND' => $mxResult,
        ];

        $result['OVERWRITE_CONTROL'] = ['EXECUTE_MODULE' => 'sale'];

        return $result;
    }
}
