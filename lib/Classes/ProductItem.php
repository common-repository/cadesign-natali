<?php

namespace Cadesign\NataliApi;

use Cadesign\Natali\Assoc;
use Cadesign\Natali\Helper;
use Cadesign\Natali\WooCommerceApi;

class ProductItem
{

	/**
	 * @param $productId
	 * @return mixed
	 */
	function getProductInfo($productId)
	{
		$urlPath = '/product/get?productId=' . $productId;
		$obProduct = new Api($urlPath);
		$arProduct = $obProduct->fetchArray();

		return $arProduct["data"]["product"];
	}

	/**
	 * Создание нового продукта по массиву из NataliApi
	 * @param array $product
	 * @throws \Bitrix\Main\ArgumentException
	 * @throws \Bitrix\Main\Db\SqlQueryException
	 */
	public function createProduct(array $product)
	{
		if (Main::colorSingleItem())
		{
			$productOneOrMany = self::createMany($product);
		}
		else
		{
			$productOneOrMany = self::createOne($product);
		}

		return $productOneOrMany;
	}

	/**
	 * Создаем из одного спарсеного продукта - один
	 * @param $product
	 * @return false|int|mixed
	 * @throws \Bitrix\Main\ArgumentException
	 */
	public static function createOne($product)
	{
		$nataliAttributes = Main::getNataliAttributes();
		$bxProduct = [];
		$bxProduct['SECTIONS'] = CategoryList::convertBindings($product['categoriesIds']);

		$firstSect = $bxProduct['SECTIONS'][0];

		//Поготовим основные поля элемента
		$bxFields = new PrepareFields($product, $bxProduct['SECTIONS']);
		$bxProduct['ELEMENT'] = $bxFields->values;
		$images = Files::saveFiles($product["images"] );

		foreach ($images as $image)
		{
			$bxProduct['ELEMENT']['images'][] = ['id' => $image];
		}
		//Обработка привязки свойств
		$bxProperties = new BindProps($product);
		$bxProduct['ELEMENT']["attributes"] = $bxProperties->getValues();
		$colorsImg = $bxProperties->getColorsImg();

		//Обработка привязки цен
		$bxPrices = new BindPrices($product);
		$bxProduct['ELEMENT']['external_id'] = $product["productId"];
		$bxProduct['ELEMENT']['status'] = "publish";
		$bxProduct['ELEMENT']['type'] = "variable";
		$bxProduct['ELEMENT']['regular_price'] = (string)$bxPrices->values;
		$bxProduct['ELEMENT']['stock_quantity'] = 1000;
		$bxProduct['ELEMENT']['stock_status'] = "instock";
		$bxProduct['ELEMENT']['weight'] = (string)$product['weight'];

		//Формирование массива офферсов
		$bxOffers = new Offers($product, false, $colorsImg);
		$bxProduct['OFFERS'] = $bxOffers->getOffers();

		$colors = $bxOffers->getColors();
		$sizes= $bxOffers->getSizes();
		$bxProduct['ELEMENT']["attributes"][] = [
			'id' => $nataliAttributes['color'],
			'position' => 0,
//            'name' => 'Цвет',
			'visible' => true,
			'variation' => true,
			'options' => $colors
		];
		$bxProduct['ELEMENT']["attributes"][] = [
			'id' => $nataliAttributes['size'],
			'position' => 0,
//            'name' => 'Размер',
			'visible' => true,
			'variation' => true,
			'options' => $sizes
		];
		$bxProduct['ELEMENT']['default_attributes']=[
			[
				'id' => $nataliAttributes['color'],
				'option' => $colors['0']
			],
			[
				'id'=>$nataliAttributes['size'],
				'option' => $sizes['0']
			]
		];
		$newId = self::create($bxProduct);

		return $newId;
	}

	/**
	 * Создаем из одного спарсеного продукта несколько
	 * @param $product
	 * @return array
	 * @throws \Bitrix\Main\ArgumentException
	 */
	public static function createMany($product)
	{
		$nataliAttributes = Main::getNataliAttributes();
		$newIds = [];
		foreach ($product['colors'] as $arColor)
		{
			$colorId = $arColor["colorId"];
			$colorName = $arColor['title'];

			$bxProduct = [];
			$bxProduct['SECTIONS'] = CategoryList::convertBindings($product['categoriesIds']);

			$firstSect = $bxProduct['SECTIONS'][0];

			//Подготовим основные поля элемента
			$bxFields = new PrepareFields($product, $bxProduct['SECTIONS'], $colorId, $colorName);

			if (!$bxFields->approve)
			{
				continue;
			}

			$bxProduct['ELEMENT'] = $bxFields->values;

			foreach ($product["images"] as $image)
			{
				$bxProduct['ELEMENT']['images'][] = ['src' => $image["url"]];
			}
			//Обработка привязки свойств
			$bxProperties = new BindProps($product);
			$bxProduct['ELEMENT']["attributes"] = $bxProperties->getValues();
			$colorsImg = $bxProperties->getColorsImg();

			//Обработка привязки цен
			$bxPrices = new BindPrices($product);
			$bxProduct['ELEMENT']['external_id'] = $product["productId"] . '-' . $colorId;
			$bxProduct['ELEMENT']['status'] = "publish";
			$bxProduct['ELEMENT']['type'] = "variable";
			$bxProduct['ELEMENT']['regular_price'] = (string)$bxPrices->values;
			$bxProduct['ELEMENT']['stock_quantity'] = 1000;
			$bxProduct['ELEMENT']['stock_status'] = "instock";
			$bxProduct['ELEMENT']['weight'] = (string)$product['weight'];

			//Формирование массива офферсов
			$bxOffers = new Offers($product, $arColor, $colorsImg);
			$bxProduct['OFFERS'] = $bxOffers->getOffers();
			$bxProduct['ELEMENT']["attributes"][] = [
				'id' => $nataliAttributes['color'],
				'position' => 0,
//            'name' => 'Цвет',
				'visible' => true,
				'variation' => false,
				'options' => $bxOffers->getColors()
			];
			$bxProduct['ELEMENT']["attributes"][] = [
				'id' => $nataliAttributes['size'],
				'position' => 0,
//            'name' => 'Размер',
				'visible' => true,
				'variation' => true,
				'options' => $bxOffers->getSizes()
			];
//pr($bxProduct);
//die;
			$newIds[] = self::create($bxProduct);
			unset($bxFields);
			unset($bxProperties);
			unset($bxOffers);
		}

		return $newIds;
	}

	/**
	 * Создание элемента и назначение цен, добавление офферов
	 * @param $product
	 * @param false $isOffer
	 */
	static function create($product, $isOffer = false)
	{
		$ID = self::createElement($product['ELEMENT'], $isOffer);

		if ($product['OFFERS'])
		{
			self::addOffers($ID, $product['OFFERS']);
		}

		return $ID;
	}

	/**
	 * Создание или обновление продукта по массиву продукта из api
	 * @param $product
	 * @return false|int|mixed
	 */
	private static function createElement($product, $isOffer = false)
	{
		if ($wpId = \Cadesign\Natali\Assoc::getWpID($product['external_id']))
		{
			$PRODUCT_ID = self::update($wpId, $product, $isOffer);
		}
		else
		{
			$PRODUCT_ID = self::add($product, $isOffer);
		}

		return $PRODUCT_ID;
	}

	/**
	 * Добавляет оффер
	 * @param $productId
	 * @param $arOffers
	 */
	public static function addOffers($productId, $arOffers)
	{
		foreach ($arOffers as $offer)
		{
			$offer["ELEMENT"]["product_id"] = $productId;
			$offerId = self::create($offer, true);
		}
	}

	/**
	 * Получим массив элементов, которые нужно удалить ( деактивировать )
	 * @param array $arOffersParsed
	 * @param array $currentOffers
	 * @return array
	 */
	public static function notActualOffers(array $arOffersParsed, array $currentOffers)
	{
		$toDelete = [];

		foreach ($currentOffers as $currentOfferData)
		{
			$needDelete = true;
			foreach ($arOffersParsed as $parsedOffer)
			{
				if ($parsedOffer['ELEMENT']['XML_ID'] == $currentOfferData['XML_ID'])
				{
					$needDelete = false;
				}
			}
			if ($needDelete)
			{
				$toDelete[] = $currentOfferData['ID'];
			}
		}

		return $toDelete;
	}

	private static function add($product, $isOffer = false)
	{
		$woocommerce = new WooCommerceApi();

		if ($isOffer)
		{
			$endpoint = 'products/' . $product['product_id'] . '/variations';
			$size = $product['size'];
			$color = $product['color'];
			unset($product['product_id'], $product['size'], $product['color']);
			$startText = 'Вариация добавлена. ';
		}
		else
		{
			$endpoint = 'products';
			$startText = 'Товар добавлен. ';
		}

		$res = $woocommerce->getClient()->post($endpoint, $product);
		\Cadesign\Natali\Assoc::save($res->id, $product['external_id']);

		$images = [];
		if ($isOffer)
		{
			update_post_meta($res->id, 'attribute_pa_natali_size', sanitize_title($size));
			update_post_meta($res->id, 'attribute_pa_natali_color', sanitize_title($color));
			$images[] = $res->image->id;
		}

		foreach ($res->images as $image)
		{
			$images[] = $image->id;
		}

		self::saveImageToProduct($res->id, $images);

		Helper::Log($startText . 'Id товара Натали: ' . $product['external_id'] . '. локальный Id  ' . $res->id );
		return $res->id;
	}

	private static function update($wpId, $product, bool $isOffer)
	{
		$woocommerce = new WooCommerceApi();

		if ($isOffer)
		{
			$endpoint = 'products/' . $product['product_id'] . '/variations/' . $wpId;
			unset($product['product_id']);
			$startText = 'Вариация обновлена. ';
		}
		else
		{
			$endpoint = 'products/' . $wpId;
			$startText = 'Товар обновлен. ';
		}

		try
		{
			$res = $woocommerce->getClient()->put($endpoint, $product);
		}
		catch (\Exception $exception)
		{
			Assoc::deleteAssoc($product['external_id']);
			Helper::Log('Ошибка обновления. Не найден товар. Id товара: ' .$wpId . '.' . $exception->getMessage());

			return '0';
		}

		Helper::Log($startText . 'Id товара Натали: ' . $product['external_id'] . '. локальный Id  ' . $res->id );

		return $res->id;
	}

	private static function saveImageToProduct($id, $images)
	{
		foreach ($images as $img)
		{
			wp_update_post([
				'ID' => $img,
				'post_parent' => $id
			]);
		}
//		set_post_thumbnail($id, $images[0]);

	}
}