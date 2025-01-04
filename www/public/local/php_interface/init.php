<?php

use Bitrix\Main\EventManager;
use Bitrix\Main\Loader;
use Project\Events\Discounts\ExampleDopUsloviya\DopUslovieBezGruppi;
use Project\Events\Discounts\ExampleDopUsloviya\DopUslovieSGruppoy;

include __DIR__ . '/../../../vendor/autoload.php';

Loader::includeModule('sale');

$eventManager = EventManager::getInstance();
$eventManager->addEventHandler('sale', 'OnCondSaleControlBuildList', [DopUslovieBezGruppi::class, 'GetControlDescr']);
$eventManager->addEventHandler('sale', 'OnCondSaleControlBuildList', [DopUslovieSGruppoy::class, 'GetControlDescr']);
