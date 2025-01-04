<?php

declare(strict_types=1);

namespace Project\Helpers\Discounts;

use Project\Helpers\Helpers;

trait ControlTrait
{
    public static function GetControlID(): string
    {
        return static::GetControlBase();
    }

    protected static function GetControlBase(): string
    {
        return Helpers::rootNamespacePart(__CLASS__) .
            Helpers::shortClassName(__CLASS__);
    }
}
