<?php

namespace Cadesign\NataliApi;

defined('B_PROLOG_INCLUDED') and (B_PROLOG_INCLUDED === true) or die();


use Bitrix\Main\Entity\DataManager;
use Bitrix\Main\Entity\IntegerField;
use Bitrix\Main\Entity\TextField;
use Bitrix\Main\Entity\DatetimeField;
use Bitrix\Main\Localization\Loc;


Loc::loadMessages(__FILE__);

/**
 * Таблица для обновления импортированных элементов
 *
 * @package Cadesign\NataliApi
 */
class UpdatesTable extends DataManager
{

    /**
     * @return string
     */
    public static function getTableName()
    {
        return 'cadesign_natali_updates';
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
            new TextField('STATUS', [
            ]),
            new TextField('TYPE', [
            ]),
            new DatetimeField('LAST_DATE', [

                ]
            )
        );

    }

}
