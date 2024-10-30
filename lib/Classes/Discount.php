<?php
/**
 * Класс для работы с правилами корзины
 */

namespace Cadesign\NataliApi;

use Bitrix\Main\Engine\Controller;
use Cadesign\NataliApi\CategoryList;
use Cadesign\NataliApi\ProductList;
use Cadesign\NataliApi\Helper;
use Cadesign\NataliApi\Props;

class Discount
{

    /**
     * Метод для добавления нового правила
     * @param $percent
     * @throws \Bitrix\Main\ArgumentException
     * @throws \Bitrix\Main\LoaderException
     * @throws \Bitrix\Main\ObjectPropertyException
     * @throws \Bitrix\Main\SystemException
     */
    public static function add($percent)
    {
        if($percent > 0)
        {

            \Bitrix\Main\Loader::IncludeModule("catalog");
            \Bitrix\Main\Loader::IncludeModule("iblock");
            \Bitrix\Main\Loader::IncludeModule("sale");


            $propId = Props::addServiceProp();
            $iblockId = Main::CatalogIblockId();

            global $APPLICATION;
            $unixStart = strtotime(date("d.m.Y H:i:s"));
            $unixEnd = $unixStart + 43200; //12 часов
            $xcount = 0;
            $discountValue = $percent; //Размер случайной скидки от 1 до 10 процентов

            $condClassStr = "CondIBProp:" . $iblockId . ":" . $propId;


            $Actions["CLASS_ID"] = "CondGroup";
            $Actions["DATA"]["All"] = "AND";
            $Actions["CLASS_ID"] = "CondGroup";
            $Actions["CHILDREN"][0]["CLASS_ID"] = "ActSaleBsktGrp";
            $Actions["CHILDREN"][0]["DATA"]["Type"] = "Extra";
            $Actions["CHILDREN"][0]["DATA"]["Value"] = $discountValue;
            $Actions["CHILDREN"][0]["DATA"]["Unit"] = "Perc";
            $Actions["CHILDREN"][0]["DATA"]["All"] = "OR";

            $Actions["CHILDREN"][0]["DATA"]["Max"] = "10";
            $Actions["CHILDREN"][0]["DATA"]["True"] = "True";


            $Conditions = [
                "CLASS_ID" => "CondGroup",
                "DATA" => ["All" => "OR", "True" => "True"],
                "CHILDREN" => [["CLASS_ID" => $condClassStr, "DATA" => ["logic" => "Equal", "value" => "Y"]]]
            ];


            //Массив для создания правила
            $arFields = [
                "LID" => "s1",
                "NAME" => "Natali price up " . $discountValue,
                "CURRENCY" => "RUB",
                "ACTIVE" => "Y",
                "USER_GROUPS" => [1],
                "CONDITIONS" => $Conditions,
                'ACTIONS' => $Actions
            ];

            $ID = \CSaleDiscount::Add($arFields); //Создаем правило корзины
            $res = $ID > 0;
            if($res)
            {
                Helper::Log(GetMessage("CADESIGN_NATALI_ADDED_BASKET_RULE"));
            }
            else
            {
                $ex = $APPLICATION->GetException();
                Helper::Log(GetMessage("CADESIGN_NATALI_ERROR") . $ex->GetString());
            }


        }
    }


}