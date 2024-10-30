<?php
/**
 * Миграция для создания таблиц:
 *  cad_brand_reference
 *  cad_label_reference
 *
 * TODO: Добавить метод для удаления таблиц, поправить поле ID или убрать его
 * TODO: Добавить работу с недостающими таблицами
 */

namespace Cadesign\NataliApi\Migrate;

use Bitrix\Highloadblock as HL;
use Bitrix\Main\DB\SqlExpression;
use Bitrix\Main\Entity;
use Bitrix\Main\UserField\Types\DateTimeType;
use Cadesign\NataliApi\Helper;
use Cadesign\NataliApi\Main;

class CreatePropTables
{

    /**
     * Создание таблиц HL блока
     * @param $name
     * @param $table
     * @return string|void
     * @throws \Bitrix\Main\ArgumentException
     * @throws \Bitrix\Main\ObjectPropertyException
     * @throws \Bitrix\Main\SystemException
     */
    public function AddHl($name, $table)
    {

        $name = ucfirst($name);

        \Bitrix\Main\Loader::includeModule('highloadblock');


        $hlblock = HL\HighloadBlockTable::getList(['filter' => ['TABLE_NAME' => $table]])->fetch();

        if ($hlblock) {
            Helper::log( 'HL-'.GetMessage("CADESIGN_NATALI_BLOCK_IS_ISSET") . $name );
            $HLBId = $hlblock['ID'];
        } else {
            $hlblock = HL\HighloadBlockTable::add([
                'NAME' => $name,
                'TABLE_NAME' => $table,
            ]);

            if (!$hlblock->isSuccess()) {
                Helper::log(  GetMessage("CADESIGN_NATALI_HL_CREATION_ERROR") . $name . '' . $hlblock->getErrorMessages() );

                return;
            } else {
                $HLBId = $hlblock->getId();
                Helper::log( 'HL-'.GetMessage("CADESIGN_NATALI_BLOCK_CREATED") . $HLBId );
            }
        }
        return (string) $HLBId;

    }

    /**
     * Добавление столбцов для таблиц hl блоков
     * @param array $props
     */
    public function AddPropsHl($props = [])
    {
        global $APPLICATION;
        $oUserTypeEntity = new \CUserTypeEntity();
        foreach ($props as $prop){

            $iUserFieldId = $oUserTypeEntity->Add($prop);
            if (!$iUserFieldId) {
                Helper::log( GetMessage("CADESIGN_NATALI_ERROR_FIELD_CREATION") . $prop['ENTITY_ID'] );
                $err = $APPLICATION->GetException();
                Helper::log( $err . PHP_EOL);
            } else {
                Helper::log(GetMessage("CADESIGN_NATALI_FIELD") . $prop['ENTITY_ID'] . ' '.GetMessage("CADESIGN_NATALI_ADDED") );
            }
        }

    }

    /**
     * @param $HLBId
     * @return array[]
     */
    public function propsLabels($HLBId)
    {

        return [
            [
                'ENTITY_ID' => 'HLBLOCK_' . $HLBId,
                'FIELD_NAME' => 'UF_XML_ID',
                'USER_TYPE_ID' => 'string',
                'XML_ID' => 'XML_ID',
                'SORT' => 400,
                'MULTIPLE' => 'N',
                'MANDATORY' => 'N',
                'SHOW_FILTER' => 'N',
                'SHOW_IN_LIST' => 'Y',
                'EDIT_IN_LIST' => 'Y',
                'IS_SEARCHABLE' => 'N',
                'SETTINGS' => [
                    'DEFAULT_VALUE' => '',
                    'SIZE' => '80',
                    'ROWS' => '1',
                ],
                'EDIT_FORM_LABEL' => [
                    'ru' => GetMessage("CADESIGN_NATALI_EXTERNAL_ID"),
                    'en' => 'Id',
                ],
                'LIST_COLUMN_LABEL' => [
                    'ru' => GetMessage("CADESIGN_NATALI_EXTERNAL_ID"),
                    'en' => 'Id',
                ],
                'LIST_FILTER_LABEL' => [
                    'ru' => GetMessage("CADESIGN_NATALI_EXTERNAL_ID"),
                    'en' => 'Id',
                ]
            ],
            [
                'ENTITY_ID' => 'HLBLOCK_' . $HLBId,
                'FIELD_NAME' => 'UF_IMAGE',
                'USER_TYPE_ID' => 'string',
                'XML_ID' => 'XML_IMAGE',
                'SORT' => 400,
                'MULTIPLE' => 'N',
                'MANDATORY' => 'N',
                'SHOW_FILTER' => 'N',
                'SHOW_IN_LIST' => 'Y',
                'EDIT_IN_LIST' => 'Y',
                'IS_SEARCHABLE' => 'N',
                'SETTINGS' => [
                    'DEFAULT_VALUE' => '',
                    'SIZE' => '80',
                    'ROWS' => '1',
                ],
                'EDIT_FORM_LABEL' => [
                    'ru' => GetMessage("CADESIGN_NATALI_IMAGE"),
                    'en' => 'image',
                ],
                'LIST_COLUMN_LABEL' => [
                    'ru' => GetMessage("CADESIGN_NATALI_IMAGE"),
                    'en' => 'image',
                ],
                'LIST_FILTER_LABEL' => [
                    'ru' => GetMessage("CADESIGN_NATALI_IMAGE"),
                    'en' => 'image',
                ]
            ],
            [
                'ENTITY_ID' => 'HLBLOCK_' . $HLBId,
                'FIELD_NAME' => 'UF_NAME',
                'USER_TYPE_ID' => 'string',
                'XML_ID' => 'XML_NAME',
                'SORT' => 400,
                'MULTIPLE' => 'N',
                'MANDATORY' => 'N',
                'SHOW_FILTER' => 'N',
                'SHOW_IN_LIST' => 'Y',
                'EDIT_IN_LIST' => 'Y',
                'IS_SEARCHABLE' => 'N',
                'SETTINGS' => [
                    'DEFAULT_VALUE' => '',
                    'SIZE' => '80',
                    'ROWS' => '1',
                ],
                'EDIT_FORM_LABEL' => [
                    'ru' => GetMessage("CADESIGN_NATALI_F_TITLE"),
                    'en' => 'TITLE',
                ],
                'LIST_COLUMN_LABEL' => [
                    'ru' => GetMessage("CADESIGN_NATALI_F_TITLE"),
                    'en' => 'TITLE',
                ],
                'LIST_FILTER_LABEL' => [
                    'ru' => GetMessage("CADESIGN_NATALI_F_TITLE"),
                    'en' => 'TITLE',
                ]
            ],
            [
                'ENTITY_ID' => 'HLBLOCK_' . $HLBId,
                'FIELD_NAME' => 'UF_SHORTTITLE',
                'USER_TYPE_ID' => 'string',
                'XML_ID' => 'XML_SHORTTITLE',
                'SORT' => 400,
                'MULTIPLE' => 'N',
                'MANDATORY' => 'N',
                'SHOW_FILTER' => 'N',
                'SHOW_IN_LIST' => 'Y',
                'EDIT_IN_LIST' => 'Y',
                'IS_SEARCHABLE' => 'N',
                'SETTINGS' => [
                    'DEFAULT_VALUE' => '',
                    'SIZE' => '80',
                    'ROWS' => '1',
                ],
                'EDIT_FORM_LABEL' => [
                    'ru' => GetMessage("CADESIGN_NATALI_F_SHORT_TITLE"),
                    'en' => 'SHORT TITLE',
                ],
                'LIST_COLUMN_LABEL' => [
                    'ru' => GetMessage("CADESIGN_NATALI_F_SHORT_TITLE"),
                    'en' => 'SHORT TITLE',
                ],
                'LIST_FILTER_LABEL' => [
                    'ru' => GetMessage("CADESIGN_NATALI_F_SHORT_TITLE"),
                    'en' => 'SHORT TITLE',
                ]
            ],

        ];
    }

    /**
     * @param $HLBId
     * @return array[]
     */
    public function propsBrands($HLBId)
    {

        return [
            [
                'ENTITY_ID' => 'HLBLOCK_' . $HLBId,
                'FIELD_NAME' => 'UF_XML_ID',
                'USER_TYPE_ID' => 'string',
                'XML_ID' => 'XML_ID',
                'SORT' => 400,
                'MULTIPLE' => 'N',
                'MANDATORY' => 'N',
                'SHOW_FILTER' => 'N',
                'SHOW_IN_LIST' => 'Y',
                'EDIT_IN_LIST' => 'Y',
                'IS_SEARCHABLE' => 'N',
                'SETTINGS' => [
                    'DEFAULT_VALUE' => '',
                    'SIZE' => '80',
                    'ROWS' => '1',
                ],
                'EDIT_FORM_LABEL' => [
                    'ru' => GetMessage("CADESIGN_NATALI_EXTERNAL_ID"),
                    'en' => 'Id',
                ],
                'LIST_COLUMN_LABEL' => [
                    'ru' => GetMessage("CADESIGN_NATALI_EXTERNAL_ID"),
                    'en' => 'Id',
                ],
                'LIST_FILTER_LABEL' => [
                    'ru' => GetMessage("CADESIGN_NATALI_EXTERNAL_ID"),
                    'en' => 'Id',
                ]
            ],
            [
                'ENTITY_ID' => 'HLBLOCK_' . $HLBId,
                'FIELD_NAME' => 'UF_IMAGE',
                'USER_TYPE_ID' => 'string',
                'XML_ID' => 'UF_IMAGE',
                'SORT' => 400,
                'MULTIPLE' => 'N',
                'MANDATORY' => 'N',
                'SHOW_FILTER' => 'N',
                'SHOW_IN_LIST' => 'Y',
                'EDIT_IN_LIST' => 'Y',
                'IS_SEARCHABLE' => 'N',
                'SETTINGS' => [
                    'DEFAULT_VALUE' => '',
                    'SIZE' => '80',
                    'ROWS' => '1',
                ],
                'EDIT_FORM_LABEL' => [
                    'ru' => GetMessage("CADESIGN_NATALI_IMAGE"),
                    'en' => 'image',
                ],
                'LIST_COLUMN_LABEL' => [
                    'ru' => GetMessage("CADESIGN_NATALI_IMAGE"),
                    'en' => 'image',
                ],
                'LIST_FILTER_LABEL' => [
                    'ru' => GetMessage("CADESIGN_NATALI_IMAGE"),
                    'en' => 'image',
                ]
            ],
            [
                'ENTITY_ID' => 'HLBLOCK_' . $HLBId,
                'FIELD_NAME' => 'UF_NAME',
                'USER_TYPE_ID' => 'string',
                'XML_ID' => 'UF_NAME',
                'SORT' => 400,
                'MULTIPLE' => 'N',
                'MANDATORY' => 'N',
                'SHOW_FILTER' => 'N',
                'SHOW_IN_LIST' => 'Y',
                'EDIT_IN_LIST' => 'Y',
                'IS_SEARCHABLE' => 'N',
                'SETTINGS' => [
                    'DEFAULT_VALUE' => '',
                    'SIZE' => '80',
                    'ROWS' => '1',
                ],
                'EDIT_FORM_LABEL' => [
                    'ru' => GetMessage("CADESIGN_NATALI_F_TITLE"),
                    'en' => 'TITLE',
                ],
                'LIST_COLUMN_LABEL' => [
                    'ru' => GetMessage("CADESIGN_NATALI_F_TITLE"),
                    'en' => 'TITLE',
                ],
                'LIST_FILTER_LABEL' => [
                    'ru' => GetMessage("CADESIGN_NATALI_F_TITLE"),
                    'en' => 'TITLE',
                ]
            ],

        ];
    }


    /**
     * @param $HLBId
     * @return array[]
     */
    public function propsColor($HLBId)
    {

        return [
            [
                'ENTITY_ID' => 'HLBLOCK_' . $HLBId,
                'FIELD_NAME' => 'UF_XML_ID',
                'USER_TYPE_ID' => 'string',
                'XML_ID' => 'XML_ID',
                'SORT' => 400,
                'MULTIPLE' => 'N',
                'MANDATORY' => 'N',
                'SHOW_FILTER' => 'N',
                'SHOW_IN_LIST' => 'Y',
                'EDIT_IN_LIST' => 'Y',
                'IS_SEARCHABLE' => 'N',
                'SETTINGS' => [
                    'DEFAULT_VALUE' => '',
                    'SIZE' => '80',
                    'ROWS' => '1',
                ],
                'EDIT_FORM_LABEL' => [
                    'ru' => 'XML_ID',
                    'en' => 'XML_ID',
                ],
                'LIST_COLUMN_LABEL' => [
                    'ru' => 'XML_ID',
                    'en' => 'XML_ID',
                ],
                'LIST_FILTER_LABEL' => [
                    'ru' => 'XML_ID',
                    'en' => 'XML_ID',
                ]
            ],
            [
                'ENTITY_ID' => 'HLBLOCK_' . $HLBId,
                'FIELD_NAME' => 'UF_NAME',
                'USER_TYPE_ID' => 'string',
                'XML_ID' => 'UF_NAME',
                'SORT' => 400,
                'MULTIPLE' => 'N',
                'MANDATORY' => 'N',
                'SHOW_FILTER' => 'N',
                'SHOW_IN_LIST' => 'Y',
                'EDIT_IN_LIST' => 'Y',
                'IS_SEARCHABLE' => 'N',
                'SETTINGS' => [
                    'DEFAULT_VALUE' => '',
                    'SIZE' => '80',
                    'ROWS' => '1',
                ],
                'EDIT_FORM_LABEL' => [
                    'ru' => GetMessage("CADESIGN_NATALI_F_TITLE"),
                    'en' => 'TITLE',
                ],
                'LIST_COLUMN_LABEL' => [
                    'ru' => GetMessage("CADESIGN_NATALI_F_TITLE"),
                    'en' => 'TITLE',
                ],
                'LIST_FILTER_LABEL' => [
                    'ru' => GetMessage("CADESIGN_NATALI_F_TITLE"),
                    'en' => 'TITLE',
                ]
            ],

        ];
    }

    /**
     * @param $HLBId
     * @return array[]
     */
    public function propsMaterial($HLBId)
    {

        return [
            [
                'ENTITY_ID' => 'HLBLOCK_' . $HLBId,
                'FIELD_NAME' => 'UF_XML_ID',
                'USER_TYPE_ID' => 'string',
                'XML_ID' => 'XML_ID',
                'SORT' => 400,
                'MULTIPLE' => 'N',
                'MANDATORY' => 'N',
                'SHOW_FILTER' => 'N',
                'SHOW_IN_LIST' => 'Y',
                'EDIT_IN_LIST' => 'Y',
                'IS_SEARCHABLE' => 'N',
                'SETTINGS' => [
                    'DEFAULT_VALUE' => '',
                    'SIZE' => '80',
                    'ROWS' => '1',
                ],
                'EDIT_FORM_LABEL' => [
                    'ru' => 'XML_ID',
                    'en' => 'XML_ID',
                ],
                'LIST_COLUMN_LABEL' => [
                    'ru' => 'XML_ID',
                    'en' => 'XML_ID',
                ],
                'LIST_FILTER_LABEL' => [
                    'ru' => 'XML_ID',
                    'en' => 'XML_ID',
                ]
            ],
            [
                'ENTITY_ID' => 'HLBLOCK_' . $HLBId,
                'FIELD_NAME' => 'UF_NAME',
                'USER_TYPE_ID' => 'string',
                'XML_ID' => 'UF_NAME',
                'SORT' => 400,
                'MULTIPLE' => 'N',
                'MANDATORY' => 'N',
                'SHOW_FILTER' => 'N',
                'SHOW_IN_LIST' => 'Y',
                'EDIT_IN_LIST' => 'Y',
                'IS_SEARCHABLE' => 'N',
                'SETTINGS' => [
                    'DEFAULT_VALUE' => '',
                    'SIZE' => '80',
                    'ROWS' => '1',
                ],
                'EDIT_FORM_LABEL' => [
                    'ru' => GetMessage("CADESIGN_NATALI_F_TITLE"),
                    'en' => 'TITLE',
                ],
                'LIST_COLUMN_LABEL' => [
                    'ru' => GetMessage("CADESIGN_NATALI_F_TITLE"),
                    'en' => 'TITLE',
                ],
                'LIST_FILTER_LABEL' => [
                    'ru' => GetMessage("CADESIGN_NATALI_F_TITLE"),
                    'en' => 'TITLE',
                ]
            ],
            [
                'ENTITY_ID' => 'HLBLOCK_' . $HLBId,
                'FIELD_NAME' => 'UF_DESCRIPTION',
                'USER_TYPE_ID' => 'string',
                'XML_ID' => 'XML_DESCRIPTION',
                'SORT' => 400,
                'MULTIPLE' => 'N',
                'MANDATORY' => 'N',
                'SHOW_FILTER' => 'N',
                'SHOW_IN_LIST' => 'Y',
                'EDIT_IN_LIST' => 'Y',
                'IS_SEARCHABLE' => 'N',
                'SETTINGS' => [
                    'DEFAULT_VALUE' => '',
                    'SIZE' => '80',
                    'ROWS' => '1',
                ],
                'EDIT_FORM_LABEL' => [
                    'ru' => GetMessage("CADESIGN_NATALI_F_DESC"),
                    'en' => 'description',
                ],
                'LIST_COLUMN_LABEL' => [
                    'ru' => GetMessage("CADESIGN_NATALI_F_DESC"),
                    'en' => 'description',
                ],
                'LIST_FILTER_LABEL' => [
                    'ru' => GetMessage("CADESIGN_NATALI_F_DESC"),
                    'en' => 'description',
                ]
            ],

        ];
    }


}