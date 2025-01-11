<?php

declare(strict_types=1);

namespace Project\Discounts\Ctrls;

use Bitrix\Sale\Discount\Actions;
use CSaleActionCtrlAction;
use CSaleActionCtrlGroup;
use Project\Helpers\Discounts\CtrlTrait;
use Project\Helpers\Discounts\GenerateHelper;

/**
 * Пример действия Правила работы с корзиной "Применить общую скидку на товары"
 * Структура в GetAtomsEx
 */
class DiscountCtrlAct extends CSaleActionCtrlAction
{
    use CtrlTrait;

    /**
     * Описывающая часть действия
     *
     * Здесь будет разбираться вывод элемента `GetControlShow`
     * Попробуйте раскомментировать другую строку с ключом `GetControlShow` и закомментировать предыдущую
     * и посмотреть как отличается выводимое действие в "Действиях и условиях" Правила работы с корзиной,
     * нажав "Добавить действие", найдя действие "[кастомная] Применить общую скидку на товары" в выпадающем списке
     *
     * Подробнее о действиях написано в комментариях над методами
     */
    public static function GetControlDescr(): array
    {
        return [
            'GetControlShow' => [static::class, 'GetControlShow'],
            //'GetControlShow' => [static::class, 'GetControlShowControlGroup'],
            //'GetControlShow' => [static::class, 'GetControlShowArray'],
            'SORT' => 500
        ] + parent::GetControlDescr();
    }

    /**
     * В каких родительских группах этот элемент вывести
     * В аргументе `$arControls` переданы все подключаемые контролы,
     * у которых в `GetControlDescr` задано `GROUP => Y`
     */
    public static function GetShowIn($arControls): array
    {
        return [CSaleActionCtrlGroup::GetControlID()];
    }

    /**
     * Вывод элемента, виды:
     *
     * Единичный элемент:
     * - Его можно выбрать в конструкторе скидки в выпадающем списке родительских элементов, указанных `GetShowIn`
     * - Указан `controlId` и массив `control`, не указан `controlgroup => true`
     * - Выводятся поля, вроде "применить скидку <поле скидки>, но не более <лимит>, если <выполнены> <все условия>"
     *
     * Группа:
     * - Не указан `controlId`, указан `controlgroup => true`
     * - В массиве `children` указаны контролы с `controlId`, может быть `group`, `control`
     * - По сути несколько единичных элементов, объединенных в группу в выпадающем списке родителей из `GetShowIn`
     *
     *  Массив элементов:
     *  - Тоже самое, что "Группа", но не объединены в группу в выпадающем списке
     *  - Возвращаемое значение содержит то же, что содержится в `children` из варианта "Группа"
     *
     * Примечания:
     * - Флажок `group => true` означает, что элемент может содержать вложенные контролы в выпадающем списке:
     *   - условия срабатывания или вложенные действия(такое не встречается в стандартных скидках)
     * - Массив `visual => []` (необяз.) - слева от вложенных условий добавляет приписку между ними "И, ИЛИ, И НЕ, ИЛИ НЕ" или то, что укажешь
     */
    public static function GetControlShow($arParams): array
    {
        $atoms = self::GetAtomsEx();

        return [
            'controlId' => self::GetControlID(), // id контрола, должен быть уникален
            'group' => true, // содержит ли вложенные контролы
            'label' => '[кастомная] Применить общую скидку на товары', // название
            'defaultText' => '[кастомная] Общая скидка на товары', // не понял, где выводится
            'showIn' => self::GetShowIn($arParams['SHOW_IN_GROUPS']), // копируем из Битрикса - для значения `showIn` вызывается вот так метод `GetShowIn`
            'control' => [ // массив выводимых подряд полей
                '[кастомная] применить общую скидку', // просто текст
                $atoms['Value'], // поле <input>
                self::getCurrency() // текст
            ],
            'mess' => [
                'SELECT_CONTROL' => 'Добавить условие', // первый выводимый текст выпадающего списка
                'ADD_CONTROL' => 'Добавить условие', // текст кнопки под полями, при нажатии переключается на выпадающий список
                'DELETE_CONTROL' => 'Удалить условие', // текст в атрибуте `title` при наведении на удаление условия ("крестик")
            ]
        ];
    }

    /**
     * Добавляет приписку к вложенным условиям
     */
    public static function GetControlShowControlGroup($arParams): array
    {
        $atoms = self::GetAtomsEx();
        $visual = [
            'id' => 'Prefix',
            'name' => 'Prefix',
            'type' => 'select',
            'values' => [
                '500' => 'приписка "На пятьсот"',
                '1000' => 'приписка "На тысячу"',
                '1500' => 'приписка "На полторы"',
            ],
            'defaultPrefix' => 'какая приписка',
            'defaultValue' => '500',
        ];

        return [
            'controlgroup' => true,
            'group' => false,
            'label' => '[кастомная] Применить общую скидку на товары',
            'defaultText' => '[кастомная] Общая скидка на товары',
            'showIn' => self::GetShowIn($arParams['SHOW_IN_GROUPS']),
            'children' => [
                [
                    'controlId' => self::GetControlID(),
                    'label' => '[кастомная] Применить общую скидку на товары',
                    'defaultText' => '[кастомная] Общая скидка на товары',
                    'group' => true,
                    'showIn' => self::GetShowIn($arParams['SHOW_IN_GROUPS']),
                    'visual' => self::GetVisual(),
                    'control' => [
                        '[кастомная] применить общую скидку',
                        $atoms['Value'],
                        self::getCurrency(),
                        $visual,
                        'добавьте больше одного вложенного условия, чтобы увидеть',
                    ],
                    'mess' => [
                        'SELECT_CONTROL' => 'Добавить условие',
                        'ADD_CONTROL' => 'Добавить условие',
                        'DELETE_CONTROL' => 'Удалить условие',
                    ]
                ],
            ],
        ];
    }

    /**
     * Выведет второе действие находится в выпадающем списке первого действия
     */
    public static function GetControlShowArray($arParams): array
    {
        $atoms = self::GetAtomsEx();

        return [
            [
                'controlId' => self::GetControlID(),
                'label' => '[кастомная] Применить общую скидку на товары',
                'defaultText' => '[кастомная] Общая скидка на товары',
                'group' => true,
                'showIn' => self::GetShowIn($arParams['SHOW_IN_GROUPS']),
                'control' => [
                    '[кастомная] применить общую скидку',
                    $atoms['Value'],
                    self::getCurrency()
                ],
                'mess' => [
                    'SELECT_CONTROL' => 'Добавить условие',
                    'ADD_CONTROL' => 'Добавить условие',
                    'DELETE_CONTROL' => 'Удалить условие',
                ]
            ],
            [
                'controlId' => self::GetControlID().'2',
                'label' => '[кастомная] Применить общую скидку на товары',
                'defaultText' => '[кастомная] Общая скидка на товары',
                'group' => true,
                'showIn' => [self::GetControlID()],
                'control' => [
                    '[кастомная] применить общую скидку',
                    $atoms['Value'],
                    self::getCurrency()
                ],
                'mess' => [
                    'SELECT_CONTROL' => 'Добавить условие',
                    'ADD_CONTROL' => 'Добавить условие',
                    'DELETE_CONTROL' => 'Удалить условие',
                ]
            ]
        ];
    }

    public static function GetVisual(): array
    {
        return [
            'controls' => [ // перечисление контролов, от которых зависит что выведется с левого сбоку между условиями
                'Prefix',
            ],
            'values' => [ // Возможные комбинации значений полей этих контролов
                [
                    'Prefix' => '500',
                ],
                [
                    'Prefix' => '1000',
                ],
                [
                    'Prefix' => '1500',
                ]
            ],
            'logic' => [ // css-класс в `style` и текст в `message` в плашке для комбинации значений. Соответствие по порядковому номеру
                [
                    'style' => 'condition-logic-and',
                    'message' => 'На пятьсот',
                ],
                [
                    'style' => 'condition-logic-and',
                    'message' => 'На тысячу',
                ],
                [
                    'style' => 'condition-logic-or',
                    'message' => 'На полторы',
                ],
            ]
        ];
    }

    /**
     * Способы хранить структуру контрола:
     *
     * `GetAtomsEx` - валидация полученных данных делается методами: `CheckAtoms` <- `ValidateAtoms`
     *   - Вызывается в:
     *      - `GetAtoms` - для получения `JS` значений, `GetAtoms` иногда вызывается в `GetControlShow`
     *      - `GetControls` - для получения полей-атомов для контролов
     *      - `CetControlShow` - для передачи в `control`. Иногда и просто `GetAtomsEx` вызывается в `GetControlShow`
     *   - `CheckAtoms` вызывается в:
     *     - `GetConditionShow` - не разобрался для чего
     *     - `Parse` - не разобрался для чего
     *     - `Generate` - генерация скомпилированного кода из синтаксического дерева условий/действий
     *       собранного значения в конструкторе скидок. Код исполняется для корзины или единичного товара в каталоге
     *       для проверки условий срабатывания и применения скидок
     *     - Вызов родительской `CheckAtoms` внутри `CheckAtoms`
     *
     * `GetControls` - валидация полученных данных делается методами: `Check <- `CheckLogic`, `Validate`
     * Просто другая версия хранения структуры. Используется, в основном, для полей и характеристик инфоблока, Свойств инфоблока.
     * Там, где большие группы
     *   - Вызывается в:
     *     - `GetControlDescr` - в `CONTROLS`, когда `COMPLEX => Y`. Не разобрался для чего
     *     - `GetControlDescr` - возвращаемое значение содержит то же, что содержится в `CONTROLS` из варианта выше
     *     - `GetControlShow` - в `children` передается в `label` и пр.; `LOGIC` передается в `control` - подготовливая поля-атомы через доп. вызовы методов
     *       `LOGIC` сформирован в `GetControls` через `GetLogic` с переданными операциями сравнения. Например, для получения операций:
     *       Название <равен/не равен/содержит/не содержит> (`GetLogicAtom`) <чему> (`GetValueAtom`)
     *     - `GetControlShow` - для передачи `label` и пр. и заготовленных атомов, полученных через `GetAtomsEx` в `GetControls`
     *   - `Check` вызывается в:
     *     - `GetConditionShow`
     *     - `ApplyValues` - не разобрался для чего
     *     - `Generate`
     *
     * `GetAtoms` - Обычно в `GetAtoms` пишут 2 выпадающих списка
     * <все условия/одно из условий>, <выполнены/не выполнены>, и валидация по id'ам значений списков
     *   - Вызывается в:
     *     - `GetControlShow` для передачи в `control`
     *     - `GetConditionShow` - для простой валидации по имеющимся значениям списка `values`
     *     - `Generate` - то же, что выше
     */
    public static function GetAtomsEx($strControlID = false, $boolEx = false): false|array
    {
        return self::GetAtomsExFinishing(
            [
                'Value' => [
                    'JS' => [ // то, что передается в `GetControlShow`
                        'id' => 'Value', // id - то же, что и ключ
                        'name' => 'Value', // атрибут `name` поля
                        /**
                         * возможные значения и подробнее о том, какие ключи надо еще заполнить при других типах
                         * значений можно посмотреть поискав 'type' (с кавычками)
                         * в `bitrix/modules/catalog/general/catalog_cond.php`
                         */
                        'type' => 'input',
                    ],
                    'ATOM' => [ // вместе с JS, используется для валидации полученных данных
                        'ID' => 'Value',
                        /**
                         * Валидация типа данных
                         * Искать `$arOneAtom['ATOM']['FIELD_TYPE']`
                         * в `bitrix/modules/catalog/general/catalog_cond.php`
                         */
                        'FIELD_TYPE' => 'double',
                        'MULTIPLE' => 'N',
                        /**
                         * Доп. валидация
                         * Искать `['ATOM']['VALIDATE']`
                         * в `bitrix/modules/catalog/general/catalog_cond.php`
                         * внутри `ValidateAtoms`
                         */
                        'VALIDATE' => '',
                    ],
                ],
            ],
            $boolEx
        );
    }

    /**
     * Генерация скомпилированного кода
     *
     * По какой-то причине в Битрикс сделали формирование PHP-кода, формируемого
     * из синтаксического дерева условий/действий составленных в конструкторе скидок
     *
     * Довольно замысловатый метод, выполняет свою часть формирования PHP-кода и
     * в каком-то месте в его коде подставляется сформированный код вложенных контролов уровня ниже
     * в синтаксическом дереве
     */
    public static function Generate($arOneCondition, $arParams, $arControl, $arSubs = false): false|array
    {
        if (
            GenerateHelper::validCondition($arOneCondition, self::GetAtomsEx())
            && GenerateHelper::validSubs($arSubs)
        ) {
            return GenerateHelper::generatorResult(
                GenerateHelper::VALUE_DISCOUNT,
                $arOneCondition['Value'],
                GenerateHelper::TOTAL_DISCOUNT_SPLIT_BETWEEN_ITEMS,
                $arParams,
                $arSubs
            );
        }

        return false;
    }
}
