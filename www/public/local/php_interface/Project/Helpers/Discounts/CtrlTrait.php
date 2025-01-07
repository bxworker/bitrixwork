<?php

declare(strict_types=1);

namespace Project\Helpers\Discounts;

use Bitrix\Sale\Internals\SiteCurrencyTable;
use Project\Helpers\Helpers;

trait CtrlTrait
{
    protected static string|array|null $cachedControlId = null;

    public static function GetAtomsExFinishing(array $atoms, bool $boolEx): array
    {
        return $boolEx
            ? $atoms
            : array_map(fn($atom) => $atom['JS'], $atoms);
    }

    public static function getCurrency(): ?string
    {
        if (!static::$boolInit) {
            return null;
        }

        if (isset(static::$arInitParams['CURRENCY'])) {
            return static::$arInitParams['CURRENCY'] ?: null;
        }

        if (isset(static::$arInitParams['SITE_ID'])) {
            return SiteCurrencyTable::getSiteCurrency(static::$arInitParams['SITE_ID']) ?: null;
        }

        return null;
    }

    public static function GetControlID(): string
    {
        if (!static::$cachedControlId) {
            static::$cachedControlId = static::GetControlBaseId();
        }

        return static::$cachedControlId;
    }

    protected static function GetControlBaseId(): string
    {
        return Helpers::rootNamespacePart(static::class) .
            Helpers::shortClassName(static::class);
    }
}
