<?php
/**
 * Главный класс для работы с приложением
 */

namespace Cadesign\NataliApi;

use Cadesign\Natali\WooCommerceApi;

class Main
{
	private static $actualSettings;
	private static $fieldList = [
		[
			'slug' => 'is_natali',
			'name' => 'Товар Натали'
		],
		[
			'slug' => 'brands',
			'name' => 'Бренд'
		],
		[
			'slug' => 'materials',
			'name' => 'Материал'
		],
		[
			'slug' => 'labels',
			'name' => 'Лейбл'
		],
		[
			'slug' => 'is_marked',
			'name' => 'Товар маркирован'
		],
		[
			'slug' => 'color',
			'name' => 'Цвет'
		],
		[
			'slug' => 'size',
			'name' => 'Размер'
		],
	];
	private static $nataliAttributes = [];

	public static function consoleImport()
	{
		$oCategoryList = new CategoryList();
		$oProductList = new ProductList();
		$work = true;
		$offset = 0;

		while ($work == true)
		{
			$arProducts = $oProductList->getProducts($offset);
			$offset++;

			foreach ($arProducts as $product)
			{
				$oProd = new ProductItem();
				$oProd->createProduct($product);
				$_SESSION["CAD_IMPORT"]["UPDATED"]++;
			}

			if (empty($arProducts))
			{
				$_SESSION["CAD_IMPORT"]["STOP"] = true;
			}
		}
	}

	/**
	 * Собираем актуальные настройки
	 * @return array
	 */
	public static function getActualSettings()
	{
		if (!self::$actualSettings)
		{
			global $wpdb;
			$arr = $wpdb->get_results("SELECT * FROM `" . $wpdb->prefix . "cadesign_natali_bindings` WHERE " .
				"`BINDING_TYPE` = 'iblock_catalog' OR `BINDING_TYPE` = 'iblock_offers' OR`BINDING_TYPE` = 'colors_add'"
			);
			$data = [];
			foreach ($arr as $row)
			{
				$data[$row->BINDING_TYPE] = $row->FETCHED_ID;
			}
			self::$actualSettings = $data;
		}

		return self::$actualSettings;
	}

	public static function CatalogIblockId()
	{
		$settings = self::getActualSettings();

		return $settings['iblock_catalog'];
	}

	public static function OffersIblockId()
	{
		$settings = self::getActualSettings();

		return $settings['iblock_offers'];
	}

	public static function colorSingleItem()
	{
		$settings = self::getActualSettings();

		return ($settings['colors_add'] == "Y");
	}

	public static function getNataliAttributes()
	{
		if (self::$nataliAttributes)
		{
			return self::$nataliAttributes;
		}

		$attributes = wc_get_attribute_taxonomies();
		$result       = array();
		foreach ( $attributes as $attribute )
		{
			if (strpos($attribute->attribute_name, 'natali') !== false)
			{
				$result[str_replace('natali_', '', $attribute->attribute_name)] = $attribute->attribute_id;
			}
		}

		self::$nataliAttributes = $result;

		return $result;
	}

	public static function addMissingAttributes()
	{
		$addedAttributes = self::getNataliAttributes();

		foreach (self::$fieldList as $field)
		{
			if (!$addedAttributes[$field['slug']])
			{
				self::addAttribute($field['name'], $field['slug']);
			}
		}
	}

	public static function addAttribute($name, $slug)
	{
		$woocommerce = new WooCommerceApi();
		$data = [
			'name' => $name,
			'slug' => 'pa_natali_' . $slug,
			'type' => 'select',
			'order_by' => 'menu_order',
			'has_archives' => false
		];
		$res = $woocommerce->getClient()->post('products/attributes', $data);
	}
}