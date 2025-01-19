<?php

namespace Project\Discounts\ExtendOrderData;

use Bitrix\Iblock\IblockTable;

class IblockData
{
    use ExtendOrderDataTrait;

    protected static function getFields(array $productIds, array $entityData): false|array
    {
        if (
            !($fields = $entityData['fields'])
            || !($map = self::prepareIblockMap($productIds))
        ) {
            return false;
        }

        $iterator = IblockTable::getList([
            'select' => array_merge(['ID'], array_keys($fields)),
            'filter' => ['@ID' => array_keys($map)],
        ]);

        $fieldsData = [];

        while ($elemData = $iterator->fetch()) {
            $id = $elemData['ID'];

            foreach ($fields as $key => $alias) {
                foreach ($map[$id] as $productId) {
                    $fieldsData[$productId][$entityData['entity']][$alias] = $elemData[$key];
                }
            }
        }

        return $fieldsData;
    }

    protected static function prepareEntity(array $entities): false|array
    {
        return static::prepareEntityBase($entities, 'iblock', 'IBLOCK');
    }
}
