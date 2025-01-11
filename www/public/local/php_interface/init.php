<?php

use Bitrix\Main\EventManager;
use Bitrix\Main\Loader;
use Project\Discounts\Ctrls\DiscountCtrlAct;

include __DIR__ . '/../../../vendor/autoload.php';

Loader::includeModule('sale');

$eventManager = EventManager::getInstance();

(static function(): void {
    $eventManager = EventManager::getInstance();

    $eventManager->addEventHandler('sale', 'OnCondSaleActionsControlBuildList', [DiscountCtrlAct::class, 'GetControlDescr']);
})();
