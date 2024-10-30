<?php

namespace Cadesign\NataliApi;

defined('B_PROLOG_INCLUDED') and (B_PROLOG_INCLUDED === true) or die();

use Bitrix\Main\Entity\BooleanField;
use Bitrix\Main\Entity\DataManager;
use Bitrix\Main\Entity\IntegerField;
use Bitrix\Main\Entity\TextField;
use Bitrix\Main\Entity\DatetimeField;
use Bitrix\Main\Localization\Loc;
use Bitrix\Main\Type\DateTime;

Loc::loadMessages(__FILE__);

/**
 * Таблица для сохранения состояния импорта
 *
 * @package Cadesign\NataliApi
 */
class ImportTable extends DataManager
{

    /**
     * @return string
     */
    public static function getTableName()
    {
        return 'cadesign_natali_import';
    }

    /**
     * @return array
     * @throws \Exception
     */
    public static function getMap()
    {
        return array(
            new IntegerField('ID', [
                'autocomplete' => true,
                'primary' => true,
            ]),
            new TextField('NATALI_ID', [
                'required' => true,
            ]),
            new TextField('BX_ID', [
            ]),
            new DatetimeField('LAST_MODIFY', [
                    'required' => true,
                    'default_value' => static function ()
                    {
                        return new DateTime();
                    },
                ]
            )
        );

    }

}
