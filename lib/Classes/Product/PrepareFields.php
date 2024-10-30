<?php


namespace Cadesign\NataliApi;

use Bitrix\Main\Engine\Controller;
use Cadesign\Natali\Helper;
use Cadesign\NataliApi\CategoryList;
use Cadesign\NataliApi\ProductList;
use Cadesign\NataliApi\Main;

/**
 * Класс для подготовки полей нового элемента
 * @package Cadesign\NataliApi
 */
class PrepareFields
{

    public $values;
    public $approve;

    /**
     *
     * PrepareFields constructor.
     * @param array $product массив продукта
     * @param false $arSections код для главного раздела
     * @param string $colorId код для цвета (необязательный параметр)
     * @param string $colorName название для цвета (необязательный параметр)
     */
	public function __construct(array $product, $arSections = [], $colorId = '', $colorName = '')
	{
		$this->approve = true;

		//Выкидываем если размер в ассортименте
		if ($colorId == '0' && count($product['colors']) > 1)
		{
			$this->approve = false;
		}

		$imgUrl = $product['imageUrl'];
		$title = $product["title"];

		if ($colorId)
		{
			$colorIdText = 'color' . $colorId;

			$images = $product['images'];
			foreach ($images as $img)
			{
				if ($img['colorId'] == $colorId)
				{
					if ($img['url'])
					{
						$imgUrl = $img['url'];
					}
				}
			}
		}
		if ($colorName)
		{
			$title .= " " . $colorName;
		}

		$product["title_translit"] = Helper::translitSef($title);

		$this->values = [
			"categories" => $arSections,
			"name" => $title,
			"slug" => $product["title_translit"],
			"short_description" => $product["shortDescriptionText"],
			"description" => $product["descriptionText"],

		];
	}
}