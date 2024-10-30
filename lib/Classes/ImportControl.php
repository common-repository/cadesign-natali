<?php

namespace Cadesign\NataliApi;

use Cadesign\Natali\Helper;

/**
 * Класс для управления импортом
 * @package Cadesign\NataliApi
 */
class ImportControl
{
    /**
     * Перед началом импорта нужно знать все элементы
     */

    public static $readyCategoryArr = [];

    /**
     * Метод вызывается перед импортом через ajax шлюз
     * @return bool
     */
    public static function doBeforeImport()
    {
        set_time_limit(0);

        $categoryArr = self::getArrNeedSections();
//        \CADesign\Natali\Storage::main()->getByID('READY_CATS');

        self::$readyCategoryArr = \CADesign\Natali\Storage::main()->getByID('READY_CATS');

        if ($nextCategory = self::getNextSection($categoryArr))
        {
            //Получить список item'ов которые будут импортированы
            $arItems = self::getElementsBySection($nextCategory);

            //Сохранить item которые будут импортироваться в таблицу
            if ($arItems)
            {
                self::saveDataBefore($arItems);
            }
            \CADesign\Natali\Storage::main()->set('READY_CATS', self::$readyCategoryArr);

            return true;
        }
        else
        {
            return false;
        }
    }

    /**
     * Собираем один массив всех элементов для импорта
     * TODO:Удалить метод если не потребуется
     * @return array
     * @deprecated
     */
    public function collectDataBefore()
    {
        $categoryList = self::getNeedSections();
        $arItems = [];
        foreach ($categoryList as $categoryItem => $categorySite)
        {
            $arItems = array_merge($arItems, self::getElementsBySection($categoryItem));
        }

        return $arItems;
    }

    /**
     * Сохраняем ID элементов, которые будем ипортировать
     * @param $arItems
     * @throws \Exception
     */
    protected static function saveDataBefore($arItems)
    {
        global $wpdb;
        //Получить все элементы удалить их
//        pr($arItems);
        foreach ($arItems as $itemId)
        {
            $sql = 'SELECT ID FROM ' . $wpdb->prefix . 'cadesign_natali_import WHERE NATALI_ID="' . (int)$itemId . '"';
            $row = $wpdb->get_row($wpdb->prepare($sql));
            if (!$row->ID)
            {
                $wpdb->insert($wpdb->prefix . "cadesign_natali_import", [
                    "NATALI_ID" => $itemId,
                    'LAST_MODIFY' => date("Y-m-d H:i:s"),
                    "BX_ID" => ""
                ]);
            }
        }
    }

    /**
     * Очистка таблицы перед новым импортом
     */
    public static function dataBeforeClean()
    {
        global $wpdb;

        $wpdb->query('TRUNCATE TABLE ' . $wpdb->prefix . 'cadesign_natali_import');
    }

    /**
     * Сравниваем категории из self::$readyCategoryArr
     * со всеми категориями $categoryArr, которые есть в таблице.
     * Получаем id следующей категории
     * Полученный id добавляем в массив  self::$readyCategoryArr
     *
     * @param $categoryArr массив категорий для импорта
     * @return mixed id категории или false
     */
    protected static function getNextSection($categoryArr)
    {
        foreach ($categoryArr as $categoryIdNatali)
        {
            $allCategories[] = $categoryIdNatali;
        }

        $arNextCats = array_diff($allCategories, self::$readyCategoryArr);
        $nextCat = array_shift($arNextCats);

        self::$readyCategoryArr[] = $nextCat;

        return $nextCat;
    }

    /**
     * Получить все элементы относящиеся к определенному разделу
     * @param $sectionId
     * @return array
     */
    protected static function getElementsBySection($sectionId)
    {
        $prodList = new ProductList();
        $arProds = $prodList->getProductList(false, $sectionId);
        $items = [];
        foreach ($arProds as $product)
        {
            $items[] = $product['productId'];
        }

        return $items;
    }

    /**
     * Получить массив всех выбранных категорий и их подкатегорий по иерархии
     * @return array
     * @throws \Bitrix\Main\ArgumentException
     */
    public static function getNeedSections()
    {
        //Собрать в массив все категории из бд
        $categoryList = [];
        global $wpdb;

        $result = $wpdb->get_results('SELECT * FROM ' . $wpdb->prefix . 'cadesign_natali_bindings WHERE BINDING_TYPE="sections"');
        $arr = [];
        foreach ($result as $row)
        {
            $categoryList[$row->FETCHED_ID] = $row->TARGET_ID;
        }

        foreach ($categoryList as $categoryId => $sectionId)
        {
            //Получить  подкатегории
            $childs = CategoryList::getCategoryChilds($categoryId);
            if ($childs)
            {
                foreach ($childs as $child)
                {
                    $categoryList[$child] = $sectionId;
                }
            }
        }

        return $categoryList;
    }

    /**
     * Получить массив необходимых для импорта разделов
     * @return array
     * @throws \Bitrix\Main\ArgumentException
     */
    protected static function getArrNeedSections()
    {
        $categoryList = [];
        global $wpdb;

        $arr = $wpdb->get_results("SELECT * FROM `" . $wpdb->prefix . "cadesign_natali_bindings` WHERE `BINDING_TYPE` = 'sections'");

        foreach ($arr as $row)
        {
            $categoryList[] = $row->FETCHED_ID;
        }

        return $categoryList;
    }

    /**
     * Количество разделов, которое выбрано, без учета подразделов
     * @return mixed
     * @throws \Bitrix\Main\ArgumentException
     */
    public static function sectionsCount()
    {
        global $wpdb;

        $result = $wpdb->get_results("SELECT COUNT(*) CNT FROM " . $wpdb->prefix . "cadesign_natali_bindings WHERE BINDING_TYPE='sections'");

        return $result[0]->CNT;
    }

    /**
     * Количество элементов, которое выбрано для импорта
     * @return mixed
     * @throws \Bitrix\Main\ArgumentException
     */
    public static function elementsCount()
    {
        global $wpdb;

        $result = $wpdb->get_results("SELECT COUNT(*) CNT FROM " . $wpdb->prefix . "cadesign_natali_import");

        return $result[0]->CNT;
    }

    /**
     * Количество элементов, которые уже импортированы
     * @return mixed
     * @throws \Bitrix\Main\ArgumentException
     */
    public static function elementsImportedCount()
    {
        global $wpdb;

        $result = $wpdb->get_results("SELECT COUNT(*) CNT FROM " . $wpdb->prefix . "cadesign_natali_import WHERE BX_ID>0");

        return $result[0]->CNT;
    }

    /**
     * Импортировать следующий эелмент
     * @return mixed
     * @throws \Bitrix\Main\ArgumentException
     * @throws \Bitrix\Main\Db\SqlQueryException
     */
    public static function importNextElement()
    {
        global $wpdb;

        $sql = "SELECT * FROM " . $wpdb->prefix . "cadesign_natali_import WHERE BX_ID='' LIMIT " . (int)$_REQUEST['offset'] . ', 1';
        $row = $wpdb->get_row($wpdb->prepare($sql), ARRAY_A);

        if ($row)
        {
            $elementId = $row['ID'];
            $nataliId = $row['NATALI_ID'];

            $prodItem = new ProductItem();
            $prodInfo = $prodItem->getProductInfo($nataliId);
			$date = date("Y-m-d H:i:s");

            if (!$prodInfo)
            {
                $wpdb->update(
                    $wpdb->prefix . "cadesign_natali_import",
                    [
                        'BX_ID' => 'failed',
                        'LAST_MODIFY' => $date
                    ],
                    [
                        "ID" => $elementId
                    ]
                );
				Helper::Log('Ошибка получения информации с сервера Натали. Id товара: ' . $elementId);
                return true;
            }
            else
            {
                $bxId = $prodItem->createProduct($prodInfo);
				if(is_array($bxId))
				{
					$bxId = implode(',', $bxId);
					$prodInfo['bxId'] = $bxId;
				}

                if ($bxId)
                {
                    $wpdb->update(
                        $wpdb->prefix . "cadesign_natali_import",
                        [
                            'BX_ID' => $bxId,
                            'LAST_MODIFY' => $date
                        ],
                        [
                            "ID" => $elementId
                        ]
                    );
                }

                return $prodInfo;
            }
        }
    }

    /**
     * Метод удаляет любой следующий товар, который был импортирован
     * @return bool
     */
    public static function removeNext()
    {
        $result = \Cadesign\Natali\Assoc::getNextForDelete();

        if($result['WP_ID'])
		{
			DeleteProduct::deleteById($result['WP_ID'], $result['NATALI_ID']);

			return true;
		}
        else
		{
			return false;
		}
    }
}