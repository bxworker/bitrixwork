<?php

namespace Burov\Handlers;

use Bitrix\Main\Loader;
use Bitrix\Sale\Location;
use Bitrix\Main\GroupTable;

class DiscountRuleCustom extends \CSaleCondCtrlComplex
{

    /**
     * @return string
     */
    public static function GetClassName()
    {
        return __CLASS__;
    }

    /**
     * @return array|string
     */
    public static function GetControlID()
    {
        return [
            'CondUserGroupId',
            'CondCityListId'
        ];
    }

    /**
     * @return array
     */
    public static function GetControlDescr()
    {
        $description = parent::GetControlDescr();
        $description['COMPLEX'] = 'N';
        $description['SORT'] = 1;
        return $description;
    }

    /**
     * @param $arControls
     * @return array
     */
    public static function GetShowIn($arControls)
    {
        if (!is_array($arControls))
            $arControls = array($arControls);
        return array_values(array_unique($arControls));
    }



    /**
     * Основная группа для стека кастомных правил
     * @param $arParams
     * @return array
     */
    public static function GetControlShow($arParams)
    {
        $arControls = static::GetControls();
        $arResult = array(
            'controlgroup' => true,
            'group' =>  true,
            'label' => 'Кастомизированные свойства',
            'showIn' => static::GetShowIn($arParams['SHOW_IN_GROUPS']),
            'children' => array()
        );
        foreach ($arControls as &$arOneControl)
        {
            $arResult['children'][] = array(
                'controlId' => $arOneControl['ID'],
                'group' => false,
                'label' => $arOneControl['LABEL'],
                'showIn' => static::GetShowIn($arParams['SHOW_IN_GROUPS']),
                'control' => array(
                    $arOneControl['PREFIX'],
                    static::GetLogicAtom($arOneControl['LOGIC']),
                    static::GetValueAtom($arOneControl['JS_VALUE'])
                )
            );
        }
        if (isset($arOneControl))
            unset($arOneControl);

        return $arResult;
    }

    /**
     * Создаем наши кастомные правила
     * @param bool $controlId
     * @return array|bool|mixed
     * @throws \Bitrix\Main\ArgumentException
     */
    public static function GetControls($controlId = false)
    {
        // формируем правила
        $controlList = array(
            'CondUserGroupId' => array(
                'ID' => 'CondUserGroupId',
                'FIELD' => 'USER_GROUP_ID',
                'FIELD_TYPE' => 'int',
                'MULTIPLE' => 'N',
                'GROUP' => 'N',
                'LABEL' => 'Группа пользователя',
                'PREFIX' => 'поле Группа пользователя',
                'LOGIC' => static::getLogic(array(BT_COND_LOGIC_EQ, BT_COND_LOGIC_NOT_EQ)),
                'JS_VALUE' => array(
                    'type' => 'select',
                    'values' => static::getGroupList(),
                    'multiple' => 'Y',
                ),
                'PHP_VALUE' => ''
            ),
            'CondCityListId' => array(
                'ID' => 'CondCityListId',
                'FIELD' => 'CITY_LIST_ID',
                'FIELD_TYPE' => 'int',
                'MULTIPLE' => 'N',
                'GROUP' => 'N',
                'LABEL' => 'Город в заказе',
                'PREFIX' => 'поле Город',
                'LOGIC' => static::getLogic(array(BT_COND_LOGIC_EQ, BT_COND_LOGIC_NOT_EQ)),
                'JS_VALUE' => array(
                    'type' => 'select',
                    'values' => static::getListCities(),
                    'multiple' => 'Y',
                ),
                'PHP_VALUE' => ''
            )
        );

        foreach ($controlList as &$control)
        {
            if (!isset($control['PARENT']))
                $control['PARENT'] = true;

            $control['MULTIPLE'] = 'N';
            $control['GROUP'] = 'N';
        }
        unset($control);

        if (false === $controlId)
        {
            return $controlList;
        }
        elseif (isset($controlList[$controlId]))
        {
            return $controlList[$controlId];
        }
        else
        {
            return false;
        }
    }

    /**
     * Обработка логики правил
     * @param $oneCondition
     * @param $params
     * @param $control
     * @param bool $subs
     * @return bool|mixed|string
     */
    public static function Generate($oneCondition, $params, $control, $subs = false)
    {
        $mxResult = '';
        if (is_string($control))
        {
            $control = static::GetControls($control);
        }
        $boolError = !is_array($control);

        $values = array();
        if (!$boolError)
        {
            $values = static::check($oneCondition, $params, $control, false);
            $boolError = (false === $values);
        }

        if (!$boolError)
        {
            $type = $oneCondition['logic'];
            $stringArray = 'array(' . implode(',', array_map('intval', $values['value'])) . ')';

            if($control['ID'] === 'CondUserGroupId')
            {
                $mxResult = static::getClassName() . "::checkUserGroup($stringArray, '{$type}')";
            }
            elseif ($control['ID'] === 'CondCityListId')
            {
                $mxResult = static::getClassName() . "::checkCityList({$params['ORDER']}, $stringArray, '{$type}')";
            }
        }

        return $mxResult;
    }



    /**
     * Проверка принадлежности пользователя к группе
     * @param array $order
     * @param array $values
     * @param $type
     * @return bool
     */
    public static function checkUserGroup(array $values, $type)
    {
        // получим группы текущего пользователя
        $USER = new \CUser();
        $arUserGroup = $USER->GetUserGroupArray();

        // сравним с разрешенными
        if(count($arUserGroup) > 0 && is_array($arUserGroup) && is_array($values))
        {
            foreach ($values as $val)
            {
                // если равенство
                if ($type === 'Equal')
                    return (in_array($val, $arUserGroup)) ? true : false;

                // если не равенство
                elseif($type === 'Not')
                    return (in_array($val, $arUserGroup)) ? false : true;
            }
        }
    }

    /**
     * Проверка принаждежности города к заказу
     * @param array $order
     * @param array $values
     * @param $type
     * @throws \Bitrix\Main\SystemException
     * @return bool
     */
    public static function checkCityList(array $order, array $values, $type)
    {
        // если есть местоположение и заначения для сравнения
        if($order['DELIVERY_LOCATION'] && is_array($values))
        {
            // найдем данные о местоположении по коду
            $arLocation = Location\LocationTable::getList([ 'filter' => ['=CODE' => $order['DELIVERY_LOCATION']] ])->fetch();

            // если найдено - сравним
            if($arLocation['ID'])
            {
                // если равенство
                if ($type === 'Equal')
                    return (in_array($arLocation['ID'], $values)) ? true : false;

                // если не равенство
                elseif($type === 'Not')
                    return (in_array($arLocation['ID'], $values)) ? false : true;

            }
        }
    }

    /**
     * Получаем список групп пользователей
     * @return array
     * @throws \Bitrix\Main\ArgumentException
     */
    protected static function getGroupList()
    {
        $userGroupList = [];

        $resGroup = GroupTable::getList(['select'=>['*']]);

        while($arGroup = $resGroup->fetch())
            $userGroupList[$arGroup['ID']] = $arGroup['NAME'];

        return $userGroupList;
    }

    /**
     * Получаем список городов
     * @return array
     */
    protected static function getListCities()
    {
        $cityList = [];
        $resCity = Location\LocationTable::getListFast(array(
            'filter' => ['=NAME.LANGUAGE_ID' => LANGUAGE_ID],
        ));

        while($arCity = $resCity->fetch())
            $cityList[$arCity['ID']] = $arCity['NAME'];

        return $cityList;
    }
}
