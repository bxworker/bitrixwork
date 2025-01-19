<?php

namespace Project\Discounts\ExtendOrderData;

use Bitrix\Iblock\ElementTable;
use Bitrix\Main\Event;
use Bitrix\Main\EventResult;
use CCatalogSku;

trait ExtendOrderDataTrait
{
    public static function extendOrderData(Event $event): EventResult
    {
        if (
            ($basket = self::validateOrder($event->getParameter('ORDER')))
            && ($entityData = self::prepareEntity($event->getParameter('ENTITY')))
            && ($productMap = self::prepareProductMap($basket))
            && ($fieldsData = self::getOfferAndProductFields(
                array_keys($productMap),
                $entityData
            ))
            && ($resultData = self::fillData($productMap, $fieldsData))
        ) {
            return new EventResult(EventResult::SUCCESS, $resultData, 'iblock');
        }

        return new EventResult(EventResult::ERROR);
    }

    protected static function getOfferAndProductFields(array $productIds, array $entityData): array
    {
        $fieldsData = self::getFields($productIds, $entityData);

        if (
            !($parentMap = self::prepareParentMap($productIds))
            || !($parentFieldData = self::getFields(array_keys($parentMap), $entityData))
        ) {
            return $fieldsData;
        }

        foreach ($parentFieldData as $parentId => $entities) {
            foreach ($entities as $entity => $aliases) {
                foreach ($aliases as $alias => $value) {
                    foreach ($parentMap[$parentId] as $productId) {
                        $fieldsData[$productId][$entity]['PARENT_' . $alias] = $value;
                    }
                }
            }
        }

        return $fieldsData;
    }

    protected static function prepareParentMap(array $productIds): array
    {
        $parentMap = [];
        $alreadyFound = [];

        foreach ($productIds as $id) {
            if ($parentId = $alreadyFound[$id]) {
                $parentMap[$parentId][] = $id;
                continue;
            }

            if (
                !($row = CCatalogSku::GetProductInfo($id))
                || !is_array($row)
                || empty($row)
            ) {
                continue;
            }

            $parentId = $row['ID'];
            $parentMap[$parentId] ??= [];
            $parentMap[$parentId][] = $id;
            $alreadyFound[$id] = $parentId;
        }

        return $parentMap;
    }

    protected static function basketFilter(array $basketItem): bool
    {
        return (
            (
                (isset($basketItem['MODULE']) && $basketItem['MODULE'] == 'catalog')
                || (isset($basketItem['MODULE_ID']) && $basketItem['MODULE_ID'] == 'catalog')
            )
            && (
                isset($basketItem['PRODUCT_ID'])
                && (int)$basketItem['PRODUCT_ID'] > 0
            )
        );
    }

    protected static function fillData(array $productMap, array $fieldsData): false|array
    {
        $resultData = [];

        foreach ($fieldsData as $productId => $fieldData) {
            foreach ($productMap[$productId] ?? [] as $basketCode) {
                $resultData['BASKET_ITEMS'][$basketCode] = $fieldData;
            }
        }

        return $resultData;
    }

    protected static function prepareIblockMap(array $productIds): array
    {
        $resultMap = [];

        $elementIterator = ElementTable::getList([
            'select' => ['ID', 'IBLOCK_ID'],
            'filter' => ['@ID' => $productIds],
        ]);

        while ($element = $elementIterator->fetch()) {
            $id = $element['IBLOCK_ID'];

            $resultMap[$id] ??= [];
            $resultMap[$id][] = $element['ID'];
        }

        return $resultMap;
    }

    protected static function prepareIblockSections(array $productIds): array
    {
        $resultMap = [];

        $elementIterator = ElementTable::getList([
            'select' => ['*'],
            'filter' => ['@ID' => $productIds],
        ]);

        while ($element = $elementIterator->fetch()) {
            if (!$element['IBLOCK_SECTION_ID']) {
                continue;
            }

            $id = $element['IBLOCK_SECTION_ID'];

            $resultMap[$id] ??= [];
            $resultMap[$id][] = $element['ID'];
        }

        return $resultMap;
    }

    protected static function prepareProductMap(array $basket): array
    {
        $productMap = [];

        foreach ($basket as $basketCode => $basketItem) {
            $productId = $basketItem['PRODUCT_ID'];

            $productMap[$productId] ??= [];
            $productMap[$productId][] = $basketCode;
        }

        return $productMap;
    }

    protected static function prepareEntityBase(array $entities, string $module, string $entity): false|array
    {
        if (
            !is_array($entities)
            || empty($entities)
            || empty($entities[$module])
        ) {
            return false;
        }

        $result = [
            'entity' => $entity,
            'fields' => [],
        ];

        if (!empty($entities[$module][$entity])) {
            foreach ($entities[$module][$entity] as $entity) {
                $result['fields'][$entity['FIELD_TABLE']] = $entity['FIELD_ENTITY'];
            }
        }

        return $result;
    }

    protected static function validateOrder($order): false|array
    {
        if (
            empty($order)
            || !is_array($order)
            || !isset($order['BASKET_ITEMS'])
            || !is_array($order['BASKET_ITEMS'])
            || !($basket = array_filter($order['BASKET_ITEMS'], [static::class, 'basketFilter']))
        ) {
            return false;
        }

        return $basket;
    }
}
