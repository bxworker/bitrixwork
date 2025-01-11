<?php

declare(strict_types=1);

namespace Project\Discounts\Ctrls;

use Bitrix\Sale\Discount\Actions;
use CSaleActionCtrlAction;
use CSaleActionCtrlGroup;
use Project\Helpers\Discounts\CtrlTrait;
use Project\Helpers\Discounts\GenerateHelper;

final class DiscountCtrlAct extends CSaleActionCtrlAction
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
            'label' => '[кастомная] Применить общую скидку на товары ',
            'defaultText' => '[кастомная] Общая скидка на товары',
            'showIn' => self::GetShowIn($arParams['SHOW_IN_GROUPS']),
            'control' => [
                '[кастомная] применить общую скидку',
                $atoms['Value'],
                self::getCurrency()
            ],
            'mess' => [
                'SELECT_CONTROL' => 'Добавить условие',
                'ADD_CONTROL' => 'Добавить условие',
                'DELETE_CONTROL' => 'Удалить условие',
            ]
        ];
    }

    public static function GetAtomsEx($strControlID = false, $boolEx = false): false|array
    {
        return self::GetAtomsExFinishing(
            [
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
            ],
            $boolEx
        );
    }

    public static function Generate($arOneCondition, $arParams, $arControl, $arSubs = false): false|array
    {
        if (
            GenerateHelper::validCondition($arOneCondition, self::GetAtomsEx())
            && GenerateHelper::validSubs($arSubs)
        ) {
            return GenerateHelper::generatorResult(
                GenerateHelper::VALUE_DISCOUNT,
                $arOneCondition['Value'],
                GenerateHelper::TOTAL_DISCOUNT_SPLIT_BETWEEN_ITEMS,
                $arParams,
                $arSubs
            );
        }

        return false;
    }
}
