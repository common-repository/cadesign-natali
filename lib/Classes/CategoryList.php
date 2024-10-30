<?php

namespace Cadesign\NataliApi;

use Cadesign\NataliApi\Api;
use Cadesign\NataliApi\Helper;
use Cadesign\NataliApi\Main;

class CategoryList
{
    private const paramReplace = ["replace_space" => "-", "replace_other" => "-"];
    protected static $categoryTree;
    protected static $categoryList;

    /**
     * Получить дерево категорий
     * @param false $parentId
     * @param int $depth
     * @param false $type
     * @return array|mixed
     */
    public static function getCategoryTree($parentId = false, int $depth = 0, $type = false, $sectionSet = [])
    {
        $urlPath = '/category/list';

        if ($parentId)
        {
            $urlPath .= '?parentId=' . $parentId;
        }

        $obSections = new Api($urlPath);
        $arSections = $obSections->fetchArray();

        $categories = $arSections['data']['categories'];

        foreach ($categories as &$category)
        {
            if ($category["categoryId"] > 1000)
            {
                continue;
            }

            $category['DEPTH'] = $depth;
            $category['set'] = $sectionSet;

            self::$categoryList[] = $category;
            if ($category['hasSubcategories'])
            {
                $newSectionSet = $sectionSet;
                $newSectionSet[] = $category['title'];

                $newDepth = $depth + 1;
                $category["subcategories"] = self::getCategoryTree($category["categoryId"], $newDepth, false,
                    $newSectionSet);
            }
        }

        if ($type == 'list')
        {
            return self::$categoryList;
        }

        return $categories;
    }

    /**
     * Получим все подкатегории
     * @param $parentId
     * @return array
     */
    public static function getCategoryChilds($parentId)
    {
        $childs = [];
        $urlPath = '/category/list';
        if ($parentId)
        {
            $urlPath .= '?parentId=' . $parentId;
        }
        $obSections = new Api($urlPath);
        $res = $obSections->fetchArray();
        $arSections = $res['data']['categories'];

        if (is_array($arSections))
        {
            foreach ($arSections as $category)
            {
                $childs[] = $category['categoryId'];
                if ($category['hasSubcategories'])
                {
                    $additionalChilds = self::getCategoryChilds($category["categoryId"]);
                    $childs = array_merge($childs, $additionalChilds);
                }
            }
        }

        return $childs;
    }

    /**
     * Конвертирует разделы в натали в разделы битрикса
     * @param array $arXmlID
     * @return array
     */
    public static function convertBindings($arCategories)
    {
        global $wpdb;
        $arCats = [];
        foreach ($arCategories as $cat)
            $arCats[] = esc_sql($cat);

        $result = $wpdb->get_results('SELECT * FROM ' . $wpdb->prefix .
            'cadesign_natali_bindings WHERE BINDING_TYPE="sections" AND FETCHED_ID in (' .
            implode(', ', $arCats) . ')');
        $arr = [];
        foreach ($result as $row)
        {
            $arr[] = ['id' => (string)$row->TARGET_ID];
        }

        return $arr;
    }

    /**
     * Проверяем массив категорий полученный из добавленного товара
     * Если одна из его категорий соответствует тем, которые мы импортируем,
     * то мы возращаем true
     *
     * @param $arCategories
     * @return bool
     * @throws \Bitrix\Main\ArgumentException
     */
    public static function compareImportCats($arCategories)
    {
        $needCategories = ImportControl::getNeedSections();
        if (is_array($arCategories) && is_array($needCategories))
        {
            foreach ($arCategories as $category)
            {
                foreach ($needCategories as $needCategory => $targetCategory)
                {
                    if ($category == $needCategory)
                    {
                        return true;
                    }
                }
            }
        }

        return false;
    }

}