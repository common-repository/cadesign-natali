<?php

namespace Cadesign\NataliApi;

/**
 * Класс для подготовки офферов
 * @package Cadesign\NataliApi
 */
class Offers
{
    public $offers;
    private $colors;
    private $sizes;

    public function __construct(array $product, $needColor = false, $colorsImg = false)
    {
        $bxProduct['OFFERS'] = [];

        $colorsForContext = $product['colors'];
        if ($needColor)
        {
            $colorsForContext = [$needColor];
        }
        $bxProduct['COLORS'] = [];
        $bxProduct['SIZES'] = [];
        foreach ($colorsForContext as $arColor)
        {
            if ($arColor["colorId"] == 0 && count($product['colors']) > 1)
            {
                continue;
            }

            $bxProduct['COLORS'][$arColor["colorId"]] = $arColor["title"];
            foreach ($arColor['sizes'] as $arSize)
            {
                $bxProduct['SIZES'][$arSize['sizeId']] = $arSize['title'];

                $colorImg = $colorsImg[$arColor["colorId"]];
                $arOffer = [];
                $arOffer["ELEMENT"] = [
                    "name" => $product['title'] . " " . $arColor['title'] . " (" . $arSize['title'] . ")",
                    'external_id' => $product['productId']  . '-' . $arColor["colorId"] . '-' . $arSize['sizeId'],
                    'attributes' => [
                    ]
                ];

                if ($colorImg)
                {
					$arOffer["ELEMENT"]["image"] = ['id' => Files::getFileByUrl($colorImg)];
                }

                $arOffer["ELEMENT"]['attributes'][] = [
                    [
						'id' => 2,
//                        'name' => 'Цвет',
                        'option' => $arColor["title"]//[]
                    ]
                ];
                $arOffer["ELEMENT"]['attributes'][] = [
                    [
						'id' => 3,
//                        'name' => 'Размер',
                        'option' => $arSize["title"]// []
                    ]
                ];

				$arOffer["ELEMENT"]['color'] = $arColor["title"];
				$arOffer["ELEMENT"]['size'] = $arSize["title"];

                $arOffer['weight'] = (string)$arSize['weight'];

                //Обработка привязки цен
                $offerPrices = new BindPrices($product);
                $arOffer["ELEMENT"]['regular_price'] = (string)$offerPrices->values;

                $bxProduct['OFFERS'][] = $arOffer;
            }
        }

        $this->offers = $bxProduct['OFFERS'];
        $this->colors = array_values($bxProduct['COLORS']);
        $this->sizes = array_values($bxProduct['SIZES']);
    }

    public function getOffers()
    {
        return $this->offers;
    }

    /**
     * @return array
     */
    public function getColors(): array
    {
        return $this->colors;
    }

    /**
     * @return array
     */
    public function getSizes(): array
    {
        return $this->sizes;
    }

}