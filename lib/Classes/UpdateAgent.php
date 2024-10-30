<?php

namespace Cadesign\NataliApi;

use Bitrix\Main\Type\DateTime;
use Cadesign\NataliApi\BindingsTable;
use Cadesign\NataliApi\ProductList;
use Cadesign\NataliApi\ProductItem;
use Cadesign\NataliApi\UpdatesTable;
use \Cadesign\NataliApi\UpdateControl;
use Bitrix\Main\Entity;

/**
 * Класс для управления агентами
 * @package Cadesign\NataliApi
 */
class UpdateAgent
{

    public const SYNC_METHOD_NAME = '\Cadesign\NataliApi\UpdateAgent::syncNatali();';
    public const UPDATE_METHOD_NAME = '\Cadesign\NataliApi\UpdateAgent::updateNatali();';

    /**
     * Создать агентов или изменить
     * @param $interval
     */
    public static function addOrUpdate($interval)
    {
        self::remove();
        $stmp = AddToTimeStamp(["SS" => 10], MakeTimeStamp(false, "DD.MM.YYYY HH:MI:SS"));
        $dateAgentSync = date("d.m.Y H:i:s", $stmp);

        $stmp = AddToTimeStamp(["SS" => 30], MakeTimeStamp(false, "DD.MM.YYYY HH:MI:SS"));
        $dateAgentUpdate = date("d.m.Y H:i:s", $stmp);

        \CAgent::AddAgent(self::SYNC_METHOD_NAME, "cadesign.natali", "N", $interval, false, 'Y', $dateAgentSync);
        \CAgent::AddAgent(self::UPDATE_METHOD_NAME, "cadesign.natali", "N", $interval, false, 'Y', $dateAgentUpdate);

    }


    public function remove()
    {
        \CAgent::RemoveModuleAgents("cadesign.natali");
    }

    /**
     * метод который запускает агент для синхронизации
     * @return string
     */
    public static function syncNatali()
    {
        \Cadesign\NataliApi\UpdateControl::syncTable();
        return  '\Cadesign\NataliApi\UpdateAgent::syncNatali();';
    }

    /**
     * метод который запускает агент  для обновления
     * @return string
     * @throws \Bitrix\Main\ArgumentException
     */
    public static function updateNatali()
    {
        if(\CModule::IncludeModule("iblock"))
        {
            while($update = \Cadesign\NataliApi\UpdateControl::updateNext())
            {
                //do nothing
            }
        }
        return '\Cadesign\NataliApi\UpdateAgent::updateNatali();';
    }

    /**
     * Проверить работают ли агенты
     * @return bool
     */
    public static function getState()
    {
        $res = \CAgent::GetList(["ACTIVE"], ["MODULE_ID" => 'cadesign.natali']);
        if ($row = $res->Fetch()) {
            return true;
        } else {
            return false;
        }

    }


}