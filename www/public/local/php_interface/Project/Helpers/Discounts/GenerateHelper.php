<?php

declare(strict_types=1);

namespace Project\Helpers\Discounts;

use Bitrix\Sale\Discount\Actions;
use CSaleActionCtrlBasketGroup;

final class GenerateHelper
{
    protected static array $resolveUnitMap = [
        CSaleActionCtrlBasketGroup::VALUE_UNIT_PERCENT => Actions::VALUE_TYPE_PERCENT,
        CSaleActionCtrlBasketGroup::VALUE_UNIT_CURRENCY => Actions::VALUE_TYPE_FIX,
        CSaleActionCtrlBasketGroup::VALUE_UNIT_SUMM => Actions::VALUE_TYPE_SUMM,
    ];

    /**
     * Скидка в процентах
     */
    public const PERCENT_DISCOUNT = Actions::VALUE_TYPE_PERCENT;

    /**
     * Скидка на каждый товар
     */
    public const DISCOUNT_FOR_EACH_ITEM = Actions::VALUE_TYPE_FIX;

    /**
     * Скидка распределенная между товарами
     */
    public const TOTAL_DISCOUNT_SPLIT_BETWEEN_ITEMS = Actions::VALUE_TYPE_SUMM;

    /**
     * Значение скидки: скидка
     */
    public const VALUE_DISCOUNT = CSaleActionCtrlBasketGroup::ACTION_TYPE_DISCOUNT;

    /**
     * Значение скидки: наценка
     */
    public const VALUE_EXTRA = CSaleActionCtrlBasketGroup::ACTION_TYPE_EXTRA;

    /**
     * Значение скидки: фиксированная стоимость
     */
    public const VALUE_FIXED = CSaleActionCtrlBasketGroup::ACTION_TYPE_CLOSEOUT;

    public static function actionParamForGenerator(string $type, mixed $amount, string $unit): false|array
    {
        if ($type === self::VALUE_DISCOUNT) {
            return self::discount($amount, $unit);
        }

        if ($type === self::VALUE_EXTRA) {
            return self::extra($amount, $unit);
        }

        if ($type === self::VALUE_FIXED) {
            return self::fixed($amount, $unit);
        }

        return false;
    }

    /**
     * Скидка
     */
    public static function discount(mixed $amount, string $unit): array
    {
        return self::applyToBasketActionParam(-(float)$amount, $unit);
    }

    /**
     * Наценка
     */
    public static function extra(mixed $amount, string $unit): array
    {
        return self::applyToBasketActionParam((float)$amount, $unit);
    }

    /**
     * Выставить фиксированную стоимость
     */
    public static function fixed(mixed $amount, string $unit): false|array
    {
        if (!self::validCloseoutUnit($unit)) {
            return false;
        }

        return self::applyToBasketActionParam((float)$amount, Actions::VALUE_TYPE_CLOSEOUT);
    }

    public static function generatorResult(
        string $valueType,
        mixed $value,
        string $discountUnit,
        array $arParams,
        array $arSubs
    ): false|array {
        $actionParam = GenerateHelper::actionParamForGenerator(
            $valueType,
            $value,
            $discountUnit
        );

        if (!$actionParam) {
            return false;
        }

        $result = [
            'COND' => self::generateCodeApplyToBasket(
                $actionParam,
                $arParams,
                $arSubs
            )
        ];

        if ($discountUnit === self::TOTAL_DISCOUNT_SPLIT_BETWEEN_ITEMS) {
            $result['OVERWRITE_CONTROL'] = ['EXECUTE_MODULE' => 'sale'];
        }

        return $result;
    }

    public static function generateCodeApplyToBasket(
        array $actionParam,
        array $arParams,
        array $arSubs
    ): string {
        $method = '\Bitrix\Sale\Discount\Actions::applyToBasket';
        $firstPart = $method . '(' . $arParams['ORDER'] . ', ' . var_export($actionParam, true) . ', ';

        if (empty($arSubs)) {
            return $firstPart . '"");';
        }

        $filter = '$saleact' . $arParams['FUNC_ID'];
        $commandLine = implode(' && ', $arSubs);

        $mxResult = $filter . '=function($row){';
        $mxResult .= 'return (' . $commandLine . ');';
        $mxResult .= '};';
        $mxResult .= $firstPart . $filter . ');';

        return $mxResult;
    }

    public static function applyToBasketActionParam(float $amount, string $unit): false|array
    {
        return [
            'VALUE' => $amount,
            'UNIT' => $unit,
            'LIMIT_VALUE' => 0,
        ];
    }

    public static function validCloseoutUnit(string $unit): bool
    {
        return $unit === Actions::VALUE_TYPE_FIX;
    }

    public static function resolveUnit($unit, ?array $map = null): ?string {
        return ($map ?? self::$resolveUnitMap)[$unit] ?? null;
    }

    public static function validCondition(mixed $arOneCondition, false|array $atoms): bool
    {
        foreach ($atoms as $atom) {
            if (!isset($arOneCondition[$atom['id']])) {
                return false;
            }
        }

        return true;
    }

    public static function validSubs(mixed $arSubs): bool
    {
        return isset($arSubs) && is_array($arSubs);
    }
}
