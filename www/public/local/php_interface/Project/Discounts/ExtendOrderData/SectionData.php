<?php

namespace Project\Discounts\ExtendOrderData;

use CIBlockSection;

class SectionData
{
    use ExtendOrderDataTrait;

    protected static function getFields(array $productIds, array $entityData): false|array
    {
        if (
            !($fields = $entityData['fields'])
            || !($map = self::prepareIblockSections($productIds))
        ) {
            return false;
        }

        $select = array_merge(['ID'], array_keys($fields));

        foreach ($map as $sectionId => $elementIds) {
            $iterator = CIBlockSection::GetNavChain(0, $sectionId, $select);

            foreach ($fields as $alias) {
                foreach ($elementIds as $productId) {
                    $fieldsData[$productId][$entityData['entity']][$alias] = [];
                }
            }

            while ($elemData = $iterator->fetch()) {
                foreach ($fields as $key => $alias) {
                    foreach ($elementIds as $productId) {
                        $fieldsData[$productId][$entityData['entity']][$alias][] = $elemData[$key];
                    }
                }
            }
        }

        return $fieldsData;
    }

    protected static function prepareEntity(array $entities): false|array
    {
        return static::prepareEntityBase($entities, 'iblock', 'IBLOCK_SECTION');
    }
}
