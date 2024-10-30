<?php

namespace Cadesign\NataliApi;

use Bitrix\Main\Type\DateTime;
use Cadesign\Natali\Assoc;
use Cadesign\Natali\Helper;
use Cadesign\NataliApi\BindingsTable;
use Cadesign\NataliApi\ProductList;
use Cadesign\NataliApi\ProductItem;
use Cadesign\NataliApi\UpdatesTable;
use Bitrix\Main\Entity;
use \Cadesign\NataliApi\UpdateAgent;

/**
 * Класс для управления обновлением
 * @package Cadesign\NataliApi
 */
class UpdateControl
{
    public static $arChanges;
    private const TABLE = 'cadesign_natali_updates';

    /**
     * Получить обновления по api
     */
    public static function getApiData()
    {
        if (!self::$arChanges)
        {
            $urlPath = '/product/changes';
            $arChangesObj = new Api($urlPath);

            self::$arChanges = $arChangesObj->fetchArray();
        }

        return self::$arChanges;
    }

    /**
     * Сопоставить таблицу с данными из апи
     * @throws \Bitrix\Main\ArgumentException
     * @throws \Bitrix\Main\ObjectException
     */
    public static function syncTable()
    {
        $syncData = self::getApiData();
        global $wpdb;

        $allCounter = 0;

        foreach ($syncData as $synType => $arItems)
        {
            foreach ($arItems as $item)
            {
                $lastModify = $item['last_modify'];

                $sql = 'SELECT ID, LAST_DATE FROM ' . $wpdb->prefix . self::TABLE .
                    ' WHERE NATALI_ID="' . (int)$item['product_id'] . '" AND TYPE="' . esc_sql($synType) . '"';
                $row = $wpdb->get_row($wpdb->prepare($sql));

                if (!$row->ID)
                {
                    $status = 'new';
                    $allCounter++;

                    //Добавить если с данного товара нет в таблице
                    $wpdb->insert($wpdb->prefix . self::TABLE, [
                        "NATALI_ID" => (int)$item['product_id'],
                        "LAST_DATE" => $lastModify,
                        "TYPE" => esc_sql($synType),
                        "STATUS" => $status
                    ]);
                }
                else
                {
                    if ($lastModify != $row->LAST_DATE)
                    {
                        $wpdb->update($wpdb->prefix . self::TABLE, [
                            "STATUS" => "new",
                            "TYPE" => esc_sql($synType),
                            "LAST_DATE" => $lastModify
                        ], ['ID' => $row->ID]);
                    }
                }
            }
        }

        return $allCounter;
    }

    /**
     * Обработать следующий элемент в таблице в статусе 'new'
     * и далее переставить его в другой статус
     * @return bool|void
     * @throws \Bitrix\Main\ArgumentException
     */
    public static function updateNext()
    {
        //Ищем в таблице элементы для обновления
        $arElement = [];
        global $wpdb;
        $sql = 'SELECT * FROM ' . $wpdb->prefix . self::TABLE . ' WHERE STATUS="new"  LIMIT ' . (int)$_REQUEST['offset'] . ', 1';
        $row = $wpdb->get_row($wpdb->prepare($sql));
        if ($row->ID)
        {
            if ($row->TYPE == 'published')
            {
                //Создаем новый элемент
                if (self::createNew($row->ID, $row->NATALI_ID))
                {
                    return true;
                }
            }
            else
            {
                //Поискать такой элемент в инфоблоке
                $wpId = Assoc::getWpID($row->NATALI_ID);
                $noElement = true;
                if ($wpId)
                {
                    $noElement = false;
                    $updateItem = $row;
                    if ($updateItem->TYPE == 'modify')
                    {
                        self::editElement($row->ID, $row->NATALI_ID, $wpId);

                        self::setStatus($row->ID, 'edit');
                    }
                    elseif ($updateItem->TYPE == 'unpublished')
                    {
                        self::delElement($row->ID, $wpId);
                        DeleteProduct::deleteById($wpId, $row->NATALI_ID);

                        self::setStatus($row->ID, 'del');
                    }
                }

                if ($noElement)
                {
                    //Исключить элемент т.к. далее его нет смысла обрабатывать
					Helper::Log('Товар не соответствует выбранным категориям ' . $row->NATALI_ID );
                    self::setStatus($row->ID, 'skip_no_element');
                }
            }

            return true;
        }
        else
        {
            return false;
        }

        return false;
    }

    public static function createNew($tableId, $prodId)
    {
        $prodItem = new ProductItem();
        $prodInfo = $prodItem->getProductInfo($prodId);
        //Проверить на соответствие категории, прежде чем делать дальше

        if (CategoryList::compareImportCats($prodInfo['categoriesIds']))
        {
            $bxId = $prodItem->createProduct($prodInfo);

            self::setStatus($tableId, 'add');
        }
        else
        {
			Helper::Log('Товар не соответствует выбранным категориям ' . $prodId );
            self::setStatus($tableId, 'skip_create_new');
        }

        return true;
    }

    public static function editElement($tableId, $prodId, $elementId = false)
    {
        self::setStatus($tableId, "edit");
        $prodItem = new ProductItem();
        $prodInfo = $prodItem->getProductInfo($prodId);
        if ($prodInfo)
        {
            $bxId = $prodItem->createProduct($prodInfo);
        }
        else
        {
			Helper::Log('Не получены данные о товаре с сервера Натали ' . $prodId );
            self::setStatus($tableId, "skip_update");
        }

        return true;
    }

    public static function delElement($tableId, $elementId)
    {
        self::setStatus($tableId, "del");
		Helper::Log('Товар удален ' . $elementId );

        return true;
    }

    /**
     * Сколько обновлений планируется выполнить
     * @return int[]
     * @throws \Bitrix\Main\ArgumentException
     */
    public static function getSyncData()
    {
        $response = ['published' => 0, 'modify' => 0, 'unpublished' => 0];
        $result = UpdatesTable::getList([
            'select' => ['ID', 'NATALI_ID', 'TYPE'],
            'filter' => [
                'STATUS' => 'new'
            ],
        ]);
        while ($row = $result->fetch())
        {
            $response[$row['TYPE']]++;
        }

        return $response;
    }

    public static function getChangeCount()
    {
        global $wpdb;
        $sql = 'SELECT count(*) as CNT FROM ' . $wpdb->prefix . self::TABLE . ' WHERE STATUS="new"';
        $row = $wpdb->get_row($wpdb->prepare($sql));

        return $row->CNT;
    }

    public static function setStatus($id, $status)
    {
        global $wpdb;

        $wpdb->update(
            $wpdb->prefix . self::TABLE,
            ["STATUS" => $status],
            ['ID' => $id]
        );
    }
}