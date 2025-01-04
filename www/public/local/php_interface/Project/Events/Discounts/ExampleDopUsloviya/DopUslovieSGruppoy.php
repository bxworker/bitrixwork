<?php

declare(strict_types=1);

namespace Project\Events\Discounts\ExampleDopUsloviya;

use CSaleCondCtrl;
use Project\Helpers\Discounts\ControlTrait;

class DopUslovieSGruppoy extends CSaleCondCtrl
{
    use ControlTrait;

    public static function GetControlID()
    {
        $base = static::GetControlBase();

        return [
            $base . '1',
            $base . '2',
        ];
    }

    public static function GetControlDescr()
    {
        $result = [];

        for ($i = 1; $i <= 2; $i++) {
            $result[] = [
                'ID' => static::GetControlBase() . $i,
                'GetControlShow' => [__CLASS__, 'GetControlShow'],
                'GetConditionShow' => [__CLASS__, 'GetConditionShow'],
                'Parse' => [__CLASS__, 'Parse'],
                'Generate' => [__CLASS__, 'Generate'],
                'ApplyValues' => [__CLASS__, 'ApplyValues'],
                //'InitParams' => [__CLASS__, 'InitParams'],
            ];
        }

        return $result;
    }

    public static function GetControlShow($arParams): array
    {
        $result = array(
            'controlId' => static::GetControlID(),
            'label' => static::GetControlBase(),
            'showIn' => static::getShowIn($arParams['SHOW_IN_GROUPS']),
        );

        return $result;
    }
}
