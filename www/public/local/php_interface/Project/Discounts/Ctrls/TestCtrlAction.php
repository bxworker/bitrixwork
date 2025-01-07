<?php

declare(strict_types=1);

namespace Project\Discounts\Ctrls;

use CSaleActionCtrlAction;
use Project\Helpers\Discounts\CtrlTrait;

final class TestCtrlAction extends CSaleActionCtrlAction
{
    use CtrlTrait;

    public static function GetControlDescr(): array
    {
        return ['SORT' => 500] + parent::GetControlDescr();
    }
}
