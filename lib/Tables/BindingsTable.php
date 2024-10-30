<?php

namespace Cadesign\NataliApi;

defined('B_PROLOG_INCLUDED') and (B_PROLOG_INCLUDED === true) or die();

use Bitrix\Main\Entity\BooleanField;
use Bitrix\Main\Entity\DataManager;
use Bitrix\Main\Entity\IntegerField;
use Bitrix\Main\Entity\TextField;
use Bitrix\Main\Localization\Loc;
use Bitrix\Main\Entity\Entity\ExpressionField;

Loc::loadMessages(__FILE__);

/**
 * Таблица для сохранения настроек импорта
 *
 * @package Cadesign\NataliApi
 */
class BindingsTable extends DataManager
{

    /**
     * @return string
     */
    public static function getTableName()
    {
        return 'cadesign_natali_bindings';
    }

    /**
     * @return array
     * @throws \Exception
     */
    public static function getMap()
    {
        return [
            new IntegerField('ID', [
                'autocomplete' => true,
                'primary' => true,
            ]),
            new TextField('BINDING_TYPE', [
                'required' => true,
            ]),
            new TextField('TARGET_ID', [
            ]),
            new TextField('FETCHED_ID', [
            ])

        ];
    }

}
