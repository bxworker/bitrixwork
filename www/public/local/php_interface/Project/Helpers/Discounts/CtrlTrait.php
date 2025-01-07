<?php

declare(strict_types=1);

namespace Project\Helpers\Discounts;

use Project\Helpers\Helpers;

trait CtrlTrait
{
    protected static string|array|null $cachedControlId = null;

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
