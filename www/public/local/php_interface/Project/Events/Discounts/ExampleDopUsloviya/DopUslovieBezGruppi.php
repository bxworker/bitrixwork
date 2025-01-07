<?php

declare(strict_types=1);

namespace Project\Events\Discounts\ExampleDopUsloviya;

use CSaleCondCtrl;
use Project\Helpers\Discounts\CtrlTrait;

class DopUslovieBezGruppi extends CSaleCondCtrl
{
    use CtrlTrait;

    public static function GetControlDescr(): array
    {
        return [
            'ID' => static::GetControlID(),
            'GetControlShow' => [__CLASS__, 'GetControlShow'],
            'GetConditionShow' => [__CLASS__, 'GetConditionShow'],
            'Parse' => [__CLASS__, 'Parse'],
            'Generate' => [__CLASS__, 'Generate'],
            'ApplyValues' => [__CLASS__, 'ApplyValues'],
            //'InitParams' => [__CLASS__, 'InitParams'],
        ];
    }

    public static function GetControlShow($arParams): array
    {
        $result = array(
            'controlId' => static::GetControlID(),
            'label' => static::GetControlBaseId(),
            'showIn' => static::getShowIn($arParams['SHOW_IN_GROUPS']),
        );

        return $result;
    }
}
