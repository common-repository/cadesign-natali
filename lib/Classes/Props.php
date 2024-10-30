<?php

namespace Cadesign\NataliApi;

use Cadesign\NataliApi\Api;
use Cadesign\NataliApi\BindingsTable;
use Cadesign\NataliApi\CategoryList;
use Cadesign\NataliApi\Main;
use Cadesign\NataliApi\Brands;
use Cadesign\NataliApi\Migrate;
use Bitrix\Highloadblock as HL;

class Props
{
    /**
     * Выдает все поля которые мы импортируем, название_поля_натали => параметры поля для импорта
     * S:directory - тип справочник
     * @return array
     */
    public static function listing()
    {
        $MESS["CADESIGN_NATALI_LABEL"] = "Лейбл";
        $MESS["CADESIGN_NATALI_SOSTAV"] = "Состав";
        $MESS["CADESIGN_NATALI_MATERIALS"] = "Материалы";
        $MESS["CADESIGN_NATALI_VIDEO"] = "Видео";
        $MESS["CADESIGN_NATALI_BRAND"] = "Бренд";
        $MESS["CADESIGN_NATALI_SIZE_MIN"] = "Размер мин";
        $MESS["CADESIGN_NATALI_SIZE_MAX"] = "Размер макс";
        $MESS["CADESIGN_NATALI_SIZE_TABLE"] = "Таблица размеров (JSON)";
        $MESS["CADESIGN_NATALI_ADDITIONAL_PHOTO"] = "Доп.фото";
        $MESS["CADESIGN_NATALI_THIS_PRODUCT_HAS_NO_COLOR_SELECTION"] = "Данный товар по цвету не подбирается";
        $MESS["CADESIGN_NATALI_COLOR"] = "Цвет";
        $MESS["CADESIGN_NATALI_SIZE"] = "Размер";
        $MESS["CADESIGN_NATALI_SIZE_AS_LIST"] = "Размеры (список)";
        $MESS["CADESIGN_NATALI_ARTICUL"] = "Артикул";
        $MESS["CADESIGN_NATALI_PROP_SUCCESS_ADDED"] = "Свойство \"%s\" успешно добавлено ";
        $MESS["CADESIGN_NATALI_CANT_CREATE_PROP"] = "Не удалось создать свойство \"%s\". Сообщение об ошибке: \"%s\" ";
        $MESS["CADESIGN_NATALI_IS_NATALI"] = "Принадлежит Natali";

        return [
            'labels' => [
                'NAME' => $MESS["CADESIGN_NATALI_LABEL"],
                'TYPE' => 'S:directory',
                'TABLE' => 'cad_label_reference',
                'MULTIPLE' => true
            ],
            'composition' => ['NAME' => $MESS["CADESIGN_NATALI_SOSTAV"], 'TYPE' => 'S',],
            'images' => ['NAME' => $MESS["CADESIGN_NATALI_ADDITIONAL_PHOTO"], 'TYPE' => 'S',],
            'materials' => [
                'NAME' => $MESS["CADESIGN_NATALI_MATERIALS"],
                'TYPE' => 'S:directory',
                'MULTIPLE' => true,
                'TABLE' => 'cad_materials'
            ],
            'videos' => ['NAME' => $MESS["CADESIGN_NATALI_VIDEO"], 'TYPE' => 'S', 'MULTIPLE' => true],
            'brands' => [
                'NAME' => $MESS["CADESIGN_NATALI_BRAND"],
                'TYPE' => 'S:directory',
                'TABLE' => 'cad_brand_reference'
            ],
            'minSize' => ['NAME' => $MESS["CADESIGN_NATALI_SIZE_MIN"], 'TYPE' => 'S',],
            'maxSize' => ['NAME' => $MESS["CADESIGN_NATALI_SIZE_MAX"], 'TYPE' => 'S',],
            'garments' => ['NAME' => $MESS["CADESIGN_NATALI_SIZE_TABLE"], 'TYPE' => 'S',],
            'noColorSelect' => [
                'NAME' => $MESS["CADESIGN_NATALI_THIS_PRODUCT_HAS_NO_COLOR_SELECTION"],
                'TYPE' => 'L:C',
            ],
        ];
    }

    public static function offersListing()
    {
        return [
            'color' => [
                'NAME' => GetMessage("CADESIGN_NATALI_COLOR"),
                'TYPE' => 'S:directory',
                'TABLE' => 'cad_color_reference'
            ],
            'sizes' => [
                'NAME' => GetMessage("CADESIGN_NATALI_SIZE"),
                'TYPE' => 'S:directory',
                'TABLE' => 'cad_size_reference',
            ],
            'sizesList' => [
                'REAL_NAME' => 'sizes',
                'NAME' => GetMessage("CADESIGN_NATALI_SIZE_AS_LIST"),
                'TYPE' => 'L'
            ],
            'ean' => ['NAME' => GetMessage("CADESIGN_NATALI_ARTICUL"), 'TYPE' => 'S',],
        ];
    }

    /**
     * Создание нового свойства при импорте
     * @param $arProp 1 запись в виде массива из листинга (при этом должен обязательно быть заполнен "CODE"
     * @return string Код для нового созданного свойства
     * @throws \Bitrix\Main\ArgumentException
     * @throws \Bitrix\Main\ObjectPropertyException
     * @throws \Bitrix\Main\SystemException
     */
    public static function createIfNotExist($arProp, $iblockType = false, $isReturnPropId = false)
    {
        if (!$iblockType)
        {
            $iblockId = Main::CatalogIblockId();
            $iblockType = 'props';
        }
        elseif ($iblockType == 'offers')
        {
            $iblockId = Main::OffersIblockId();
        }

        $arProp['CODE'];
        $arProp['CODE_PREFIX'] = $arProp['CODE'] . '_NATALI';
        $newProp = [
            'NAME' => $arProp['NAME'],
            'CODE' => $arProp['CODE_PREFIX'],
            'SORT' => 500,
            'PROPERTY_TYPE' => 'S',
            'IS_REQUIRED' => 'N',
            'MULTIPLE' => $arProp['MULTIPLE'] ? 'Y' : 'N',
        ];

        if ($arProp['TYPE'] == 'S')
        {
            $newProp['PROPERTY_TYPE'] = 'S';
        }
        elseif ($arProp['TYPE'] == 'F')
        {
            $newProp['PROPERTY_TYPE'] = 'F';
        }
        elseif ($arProp['TYPE'] == 'S:directory')
        {
            //Выполняем создание HL инфоблок
            $migrate = new Migrate\CreatePropTables();
            $hlId = $migrate->AddHl($arProp['CODE'], $arProp['TABLE']);

            $newProp['PROPERTY_TYPE'] = 'S';
            $newProp['USER_TYPE'] = 'directory';
            $newProp['USER_TYPE_SETTINGS']['TABLE_NAME'] = $arProp['TABLE'];
            $newProp['USER_TYPE_SETTINGS']['MULTIPLE'] = $arProp['MULTIPLE'] ? 'Y' : 'N';
            //Заполняем для брендов
            if ($arProp['CODE'] == 'brands')
            {
                $migrate->AddPropsHl($migrate->propsBrands($hlId));
                $brands = new Brands();
                $brands->fill();
            }
            //Заполняем для лейблов
            if ($arProp['CODE'] == 'labels')
            {
                $migrate->AddPropsHl($migrate->propsLabels($hlId));
                $labels = new Labels();
                $labels->fill();
            }
            //Заполняем для цветов
            if ($arProp['CODE'] == 'color')
            {
                $migrate->AddPropsHl($migrate->propsColor($hlId));
                $colors = new Colors();
                $colors->fill();
            }
            //Заполняем для размеров
            if ($arProp['CODE'] == 'sizes')
            {
                $migrate->AddPropsHl($migrate->propsColor($hlId));
                $sizes = new Sizes();
                $sizes->fill();
            }
            //Заполняем для размеров
            if ($arProp['CODE'] == 'materials')
            {
                $migrate->AddPropsHl($migrate->propsMaterial($hlId));
            }
        }
        elseif ($arProp['TYPE'] == 'L')
        {
            $newProp['PROPERTY_TYPE'] = 'L';
        }
        elseif ($arProp['TYPE'] == 'L:C')
        {
            $newProp = [
                'NAME' => $arProp['NAME'],
                'CODE' => $arProp['CODE_PREFIX'],
                'SORT' => 500,
                'PROPERTY_TYPE' => 'L',
                'LIST_TYPE' => 'C',
                'IS_REQUIRED' => 'N',
                'WITH_DESCRIPTION' => 'N',
                'MULTIPLE' => $arProp['MULTIPLE'] ? 'Y' : 'N',
                "VALUES" => [
                    [
                        "VALUE" => "Y",
                        "DEF" => "N",
                        "XML_ID" => "Y",
                        "SORT" => "100"
                    ]
                ],
            ];
        }

        $iBlockProperty = new \CIBlockProperty;

        if ($delProp = $iBlockProperty->GetList([], [
            'CODE' => $arProp['CODE_PREFIX'],
            'IBLOCK_ID' => $iblockId
        ])->Fetch()
        )
        {
            $iBlockProperty->Delete($delProp['ID']);
        }

        $propertyId = $iBlockProperty->Add(
            array_merge(
                $newProp,
                [
                    'ACTIVE' => 'Y',
                    'IBLOCK_ID' => $iblockId,
                ]
            )
        );

        if ($propertyId)
        {
            Helper::log(sprintf(GetMessage("CADESIGN_NATALI_PROP_SUCCESS_ADDED"),
                $arProp['NAME']));

            //Обновим привязку в бд
            $result = BindingsTable::getList([
                'select' => ['ID', 'TARGET_ID', 'FETCHED_ID'],
                'filter' => ['BINDING_TYPE' => $iblockType, 'TARGET_ID' => $arProp['CODE']],
            ]);

            if ($row = $result->fetch())
            {
                BindingsTable::update($row['ID'], ['FETCHED_ID' => $arProp['CODE_PREFIX']]);

                if ($isReturnPropId)
                {
                    return $propertyId;
                }

                return $arProp['CODE_PREFIX'];
            }
        }
        else
        {
            Helper::log(sprintf(
                GetMessage("CADESIGN_NATALI_CANT_CREATE_PROP") . PHP_EOL,
                $arProp['NAME'],
                $iBlockProperty->LAST_ERROR
            ));
        }
    }

    /**
     * Создавает служебное свойство и возращает его ID
     * @return mixed
     * @throws \Bitrix\Main\ArgumentException
     * @throws \Bitrix\Main\ObjectPropertyException
     * @throws \Bitrix\Main\SystemException
     */
    public static function addServiceProp()
    {
        $arFilter = [
            'CODE' => 'IS_PARSED_NATALI'
        ];
        $res = \CIBlockProperty::GetList([], $arFilter);
        if ($field = $res->Fetch())
        {
            return $field["ID"];
        }
        else
        {
            $newProp = [
                'CODE' => 'IS_PARSED',
                'NAME' => GetMessage("CADESIGN_NATALI_IS_NATALI"),
                'TYPE' => 'S'
            ];
            $propId = self::createIfNotExist($newProp, false, true);

            return $propId;
        }
    }

    /**
     * Установим значение списка
     * @param $propertyCode
     * @param $listValue
     * @param $iblockId
     * @return false|int|mixed
     * @throws \Exception
     */
    public static function getListValue($propertyCode, $listValue, $iblockId)
    {
        $property_enums = \CIBlockPropertyEnum::GetList(["DEF" => "DESC", "SORT" => "ASC"],
            ["CODE" => $propertyCode]);
        while ($enum_field = $property_enums->GetNext())
        {
            if ($enum_field['VALUE'] == $listValue)
            {
                $valueId = $enum_field['ID'];
            }
        }

        //Если значения для свойства нет, то создадим новое
        if (!$valueId)
        {
            $property = \CIBlockProperty::GetList(
                [],
                [
                    'IBLOCK_ID' => $iblockId,
                    'CODE' => $propertyCode
                ]
            )->Fetch();
            $ibpenum = new \CIBlockPropertyEnum();
            $valueId = $ibpenum->Add([
                'PROPERTY_ID' => $property['ID'],
                'VALUE' => $listValue,
            ]);
            if ((int)$valueId < 0)
            {
                throw new \Exception('Unable to add a value');
            }
        }

        return $valueId;
    }

}

?>