<?php

use Bitrix\Main\EventManager;
use Bitrix\Main\Loader;
use Project\Discounts\Ctrls\CondCtrlAct;
use Project\Discounts\Ctrls\DiscountCtrlAct;
use Project\Discounts\ExtendOrderData\IblockData;
use Project\Discounts\ExtendOrderData\SectionData;

include __DIR__ . '/../../../vendor/autoload.php';

Loader::includeModule('sale');

$eventManager = EventManager::getInstance();

(static function(): void {
    $eventManager = EventManager::getInstance();

    $eventManager->addEventHandler('sale', 'OnCondSaleActionsControlBuildList', [DiscountCtrlAct::class, 'GetControlDescr']);
    $eventManager->addEventHandler('sale', 'OnCondSaleActionsControlBuildList', [CondCtrlAct::class, 'GetControlDescr']);
    $eventManager->addEventHandler('sale', 'onExtendOrderData', [IblockData::class, 'extendOrderData']);
    $eventManager->addEventHandler('sale', 'onExtendOrderData', [SectionData::class, 'extendOrderData']);
})();
