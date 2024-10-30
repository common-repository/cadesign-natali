<?php

namespace Cadesign\Natali;

use Cadesign\NataliApi\BindingsTable;
use Cadesign\NataliApi\ProductList;

class Helper
{
    /**
     * Добавить или удалить элемент списка
     * @param $table
     * @param $item
     * @throws \Bitrix\Main\Db\SqlQueryException
     */
    public static $loadedData;

    public static function addOrUpdate($table, $item)
    {
        $connection = \Bitrix\Main\Application::getConnection();
        $sqlHelper = $connection->getSqlHelper();
        $sql = "SELECT * FROM " . $table . " WHERE UF_XML_ID ='" . esc_sql($item["UF_XML_ID"]) . "'";
        $record = $connection->query($sql)->fetch();
        if ($record["ID"])
        {
            $sql = "UPDATE " . $table . " SET UF_NAME='" . $item["UF_NAME"] . "', UF_DESCRIPTION='" . $item["UF_DESCRIPTION"] . "' WHERE ID=" . $record["ID"];
            $connection->query($sql);
        }
        else
        {
            $connection->add($table, $item);
        }
    }

    /**
     * Метод для логирования
     * @param $message
     */
    public static function Log($message)
    {
		$logger = wc_get_logger();
		$context = array( 'source' => 'cadesign-natali' );
		$logger->info(current_time('Y-m-d H:i:s') . '. ' . $message, $context);
    }

    /**
     * Чистка массива от пустых значений
     * @param $input
     * @return array
     */
    public static function array_filter_recursive($input)
    {
        foreach ($input as &$value)
        {
            if (is_array($value))
            {
                $value = self::array_filter_recursive($value);
            }
        }

        return array_filter($input);
    }

    /**
     * Метод который вытаскивает дерево разделов из инфоблока по ID
     * @param $IBLOCK_ID
     * @return array
     */
    public static function getIblockSectionTree()
    {
        $taxonomy = 'product_cat';
        $orderby = 'name';
        $show_count = 0;
        $pad_counts = 0;
        $hierarchical = 1;
        $title = '';
        $empty = 0;
        $shop_category = [
            'taxonomy' => $taxonomy,

        ];
        $arSections = [];

        $sectionsSite = get_categories($shop_category);
        foreach ($sectionsSite as $cat)
        {
            $arSections[] = [
                "ID" => $cat->term_id,
                "DEPTH_LEVEL " => $cat->category_count,
                "NAME " => $cat->name,
                "DESC" => "",
            ];
        }

        return (array)$arSections;
    }

    /**
     * Метод для перезаписи биндингов
     * @param array $bindingsArr
     * @param string $type
     * @throws \Bitrix\Main\ArgumentException
     */
    public static function rewriteBindings(array $bindingsArr, string $type, $flip = false)
    {
        global $wpdb;
        $wpdb->get_results("DELETE FROM `" . $wpdb->prefix . "cadesign_natali_bindings` WHERE `BINDING_TYPE` = '" . esc_sql($type) . "'");

        foreach ($bindingsArr as $targetId => $fetchedId)
        {
            if ($flip)
            {
                $targetIdFlip = $fetchedId;
                $fetchedIdFlip = $targetId;
                $targetId = $targetIdFlip;
                $fetchedId = $fetchedIdFlip;
            }
            if ($type == 'sections')
            {
                if ($targetId && $fetchedId)
                {
                    $result = $wpdb->get_results("SELECT ID FROM `" . $wpdb->prefix . "cadesign_natali_bindings` " .
                        "WHERE `BINDING_TYPE` = '" . esc_sql($type) . "' AND `TARGET_ID` ='" . (int)$targetId .
                        "' AND `FETCHED_ID`='" . (int)$fetchedId . "'");

                    if (!$result)
                    {
                        $wpdb->insert($wpdb->prefix . "cadesign_natali_bindings", [
                            "BINDING_TYPE" => esc_sql($type),
                            "TARGET_ID" => (int)$targetId,
                            "FETCHED_ID" => (int)$fetchedId
                        ]);
                    }

                    //Добавить подразделы в таблицу биндингов
                    $childs = \Cadesign\NataliApi\CategoryList::getCategoryChilds($fetchedId);
                    foreach ($childs as $item)
                    {
                        $result = $wpdb->get_results("SELECT ID FROM `" . $wpdb->prefix . "cadesign_natali_bindings` " .
                            "WHERE `BINDING_TYPE` = '" . esc_sql($type) . "' AND `TARGET_ID` ='" . (int)$targetId .
                            "' AND `FETCHED_ID`='" . (int)$item . "'");

                        if (!$result)
                        {
                            $wpdb->insert($wpdb->prefix . "cadesign_natali_bindings", [
                                "BINDING_TYPE" => esc_sql($type),
                                "TARGET_ID" => (int)$targetId,
                                "FETCHED_ID" => (int)$item
                            ]);
                        }
                    }
                }
            }
            else
            {
                $wpdb->insert($wpdb->prefix . "cadesign_natali_bindings", [
                    "BINDING_TYPE" => esc_sql($type),
                    "TARGET_ID" => esc_sql($targetId),
                    "FETCHED_ID" => esc_sql($fetchedId)
                ]);
            }
        }
    }

    /**
     * Получить массив привязки цен сайта к ценам натали
     * @return array
     * @throws \Bitrix\Main\ArgumentException
     */
    public static function priceBindings()
    {
        $arr = [];
        global $wpdb;

        $result = $wpdb->get_results("SELECT * FROM `" . $wpdb->prefix . "cadesign_natali_bindings` " .
            "WHERE `BINDING_TYPE` = 'price'");

        foreach ($result as $row)
        {
            $arr[$row->FETCHED_ID] = $row->TARGET_ID;
        }

        return $arr;
    }

    /**
     * Получить название типа
     * @param string $type
     * @param false $isMultiple
     * @return string
     */
    public static function propTypeName(string $type, $isMultiple = false)
    {
        switch ($type)
        {
            case 'S':
                $typeName = GetMessage("CADESIGN_NATALI_STRING");
                break;
            case 'S:directory':
                $typeName = GetMessage("CADESIGN_NATALI_SPRAVOCNIK");
                break;
            case 'L:C':
                $typeName = 'Checkbox';
                break;
            case 'L':
                $typeName = GetMessage("CADESIGN_NATALI_LIST");
                break;
            case 'F':
                $typeName = GetMessage("CADESIGN_NATALI_FILE");
                break;
            default:
                $typeName = GetMessage("CADESIGN_NATALI_STRING");
                break;
        }
        if ($isMultiple)
        {
            $typeName .= ' (' . GetMessage("CADESIGN_NATALI_MULTIPLE");
        }

        return $typeName;
    }

    /**
     * Получить все привязки свойств из BindingsTable
     * @return array
     * @throws \Bitrix\Main\ArgumentException
     */
    public static function propsBindings()
    {
        if (!self::$loadedData['propsBindings'])
        {
            $arr = [];
            $result = BindingsTable::getList([
                'select' => ['ID', 'TARGET_ID', 'FETCHED_ID'],
                'filter' => ['BINDING_TYPE' => 'props'],
            ]);
            while ($row = $result->fetch())
            {
                $arr[$row['TARGET_ID']] = $row['FETCHED_ID'];
            }
            self::$loadedData['propsBindings'] = $arr;
        }

        return self::$loadedData['propsBindings'];
    }

    /**
     * Получить все привязки свойств офферсов из BindingsTable
     * @return array
     * @throws \Bitrix\Main\ArgumentException
     */
    public static function offersPropsBindings()
    {
        if (!self::$loadedData['offersPropsBindings'])
        {
            $arr = [];
            $result = BindingsTable::getList([
                'select' => ['ID', 'TARGET_ID', 'FETCHED_ID'],
                'filter' => ['BINDING_TYPE' => 'offers'],
            ]);
            while ($row = $result->fetch())
            {
                $arr[$row['TARGET_ID']] = $row['FETCHED_ID'];
            }
            self::$loadedData['offersPropsBindings'] = $arr;
        }

        return self::$loadedData['offersPropsBindings'];
    }

    /**
     * Получить все привязки разделов из BindingsTable
     * @return array
     * @throws \Bitrix\Main\ArgumentException
     */
    public static function sectionsBindings()
    {
        if (!self::$loadedData['sectionsBindings'])
        {
            //Собрать в массив все категории из бд
            $categoryList = [];

            $arr = [];
            global $wpdb;

            $arr = $wpdb->get_results("SELECT * FROM `" . $wpdb->prefix . "cadesign_natali_bindings` " .
                "WHERE `BINDING_TYPE` = 'sections'");
            //$categoryList categoryId => SECTION_ID
            foreach ($arr as $row)
            {
                $categoryList[$row->FETCHED_ID] = $row->TARGET_ID;
            }
            self::$loadedData['sectionsBindings'] = $categoryList;
        }

        return self::$loadedData['sectionsBindings'];
    }

    /**
     * Получить наценки
     * @return array
     * @throws \Bitrix\Main\ArgumentException
     */
    public static function extraBindings()
    {
        if (!isset(self::$loadedData['extraBindings']))
        {
            $arr = [];
            global $wpdb;
            $result = $wpdb->get_results("SELECT * FROM `" . $wpdb->prefix . "cadesign_natali_bindings` " .
                "WHERE `BINDING_TYPE` = 'extra'");

            foreach ($result as $row)
            {
                $arr[$row->TARGET_ID] = $row->FETCHED_ID;
            }

            self::$loadedData['extraBindings'] = $arr;
        }

        return self::$loadedData['extraBindings'];
    }

    /**
     * Получить dont_upload_sale
     * @return array
     * @throws \Bitrix\Main\ArgumentException
     */
    public static function dontUploadSaleBinding()
    {
        if (!self::$loadedData['dont_upload_sale'])
        {
            $arr = [];
            global $wpdb;
            $result = $wpdb->get_results("SELECT * FROM `" . $wpdb->prefix . "cadesign_natali_bindings` " .
                "WHERE `BINDING_TYPE` = 'extra'");
            $result = BindingsTable::getList([
                'select' => ['ID', 'TARGET_ID', 'FETCHED_ID'],
                'filter' => ['BINDING_TYPE' => 'dont_upload_sale'],
            ]);
            if ($row = $result->fetch())
            {
                self::$loadedData['dont_upload_sale'] = $row['FETCHED_ID'];
            }
        }

        return self::$loadedData['dont_upload_sale'];
    }

    /**
     * Рендеринг опций для вывода разделов
     * @param $sectionsSite список разделов
     * @param false $selectedId выбранные разделы
     * @return string
     */
    public static function renderSectionsOptions($sectionsSite, $selectedId = false)
    {
        $options = '<option value="">Не импортировать</option>';
        foreach ($sectionsSite as $arSection)
        {
            //Уровень вложенности
            switch ($arSection['DEPTH_LEVEL'])
            {
                case 0:
                    $depthMargin = '';
                    break;
                case 1:
                    $depthMargin = str_repeat('- ', 1);
                    break;
                case 2:
                    $depthMargin = str_repeat('- ', 2);
                    break;
                case 3:
                    $depthMargin = str_repeat('- ', 3);
                    break;
                case 4:
                    $depthMargin = str_repeat('- ', 4);
                    break;
                default:
                    $depthMargin = '';
            }
            if ($arSection['ID'] == $selectedId)
            {
                $selected = ' selected';
            }
            else
            {
                $selected = '';
            }

            $options .= '<option title="' . $arSection['DESC'] . '" value="' . $arSection['ID'] . '"' . $selected . '>' . $depthMargin . $arSection['NAME'] . ' [' . $arSection['ID'] . ']' . '</option>';
        }

        return $options;
    }

    /**
     * Отрисовка списка опций для Цен
     * @param $priceListNatali
     * @param $selectedNatali
     * @return string
     */
    public function renderPriceOptions($priceListNatali, $selectedNatali)
    {
        $options = '<option value=""></option>';
        foreach ($priceListNatali as $val => $name)
        {
            if ($val == $selectedNatali)
            {
                $selected = ' selected';
            }
            else
            {
                $selected = '';
            }

            $options .= '<option value="' . $val . '"' . $selected . '>' . $name . '</option>';
        }

        return $options;
    }

    /**
     * Рендеринг списка опций для props
     * @param $siteProps
     * @param $selectedPropCode
     * @return array
     */
    public function renderPropsOptions($siteProps, $selectedPropCode)
    {
        $optionsTypes = [];
        foreach ($siteProps as $element)
        {
            if ($selectedPropCode == $element['CODE'])
            {
                $selected = ' selected';
            }
            else
            {
                $selected = '';
            }
            $optionsTypes[$element['PROPERTY_TYPE'] . $element['USER_TYPE']] .= '<option value="' . $element['CODE'] . '"' . $selected . '>' . $element['NAME'] . ' [' . $element['CODE'] . '] </option>';
        }
        foreach ($optionsTypes as &$optionsType)
        {
            $optionsType = '<option value="">' . GetMessage("CADESIGN_NATALI_DONT_IMPORT1") . '</option>' . '<option value="createNew">' . GetMessage("CADESIGN_NATALI_CREATE_NEW") . '</option>' . $optionsType;
        }

        return $optionsTypes;
    }

    /**
     * Деактивировать все элементы sale
     * Для командной строки
     *
     * @throws \Bitrix\Main\ArgumentException
     */
    public static function deactivateDontUpload()
    {
        $result = BindingsTable::getList([
            'select' => ['TARGET_ID', 'FETCHED_ID'],
            'filter' => ['BINDING_TYPE' => "sections"],
        ]);
        $dontUpload = [];
        while ($row = $result->fetch())
        {
            //Получить элементы раздела
            if ($row['FETCHED_ID'])
            {
                $prodList = new ProductList();
                $arProds = $prodList->getProductList(false, $row['FETCHED_ID']);
                $items = [];
                foreach ($arProds as $product)
                {
                    //Если есть по sale - добавить их в массив
                    if (in_array("Sale", $product['labels']))
                    {
                        $dontUpload[] = $product['productId'];
                    }
                }
            }
        }

//        print_r($dontUpload);

        if (!empty($dontUpload))
        {
            foreach ($dontUpload as $itemXmlId)
            {
                $elementResult = \CIBlockElement::GetList(
                    ["SORT" => "ASC"],
                    [
                        "LOGIC" => "OR",
                        ["XML_ID" => $itemXmlId],
                        ["XML_ID" => $itemXmlId . "color%"],
                    ]
                );
                while ($arElement = $elementResult->fetch())
                {
                    $arLoadProductArray = ["ACTIVE" => "N",];
                    $elem = new \CIBlockElement();
                    $elem->Update($arElement['ID'], $arLoadProductArray);
//                    print "Деактивирован элемент" . $arElement['ID'] . PHP_EOL;
//                    self::Log('Деактивирован '.$arElement['ID']);

                }
            }
        }
        //Деактивировать все элементы массива

    }

    public static function keysBindings()
    {
        if (!isset(self::$loadedData['keysBindings']))
        {
            $categoryList = [];
            global $wpdb;

            $arr = $wpdb->get_results("SELECT * FROM `" . $wpdb->prefix . "cadesign_natali_bindings` " .
                "WHERE `BINDING_TYPE` = 'user_key' or `BINDING_TYPE`='secret_key'");
            foreach ($arr as $row)
            {
                $categoryList[$row->FETCHED_ID] = $row->TARGET_ID;
            }
            self::$loadedData['keysBindings'] = $categoryList;
        }

        return self::$loadedData['keysBindings'];
    }

    public static function translitSef($value)
    {
        $converter = array(
            'а' => 'a',    'б' => 'b',    'в' => 'v',    'г' => 'g',    'д' => 'd',
            'е' => 'e',    'ё' => 'e',    'ж' => 'zh',   'з' => 'z',    'и' => 'i',
            'й' => 'y',    'к' => 'k',    'л' => 'l',    'м' => 'm',    'н' => 'n',
            'о' => 'o',    'п' => 'p',    'р' => 'r',    'с' => 's',    'т' => 't',
            'у' => 'u',    'ф' => 'f',    'х' => 'h',    'ц' => 'c',    'ч' => 'ch',
            'ш' => 'sh',   'щ' => 'sch',  'ь' => '',     'ы' => 'y',    'ъ' => '',
            'э' => 'e',    'ю' => 'yu',   'я' => 'ya',
        );

        $value = mb_strtolower($value);
        $value = strtr($value, $converter);
        $value = mb_ereg_replace('[^-0-9a-z]', '-', $value);
        $value = mb_ereg_replace('[-]+', '-', $value);
        $value = trim($value, '-');

        return $value;
    }
}