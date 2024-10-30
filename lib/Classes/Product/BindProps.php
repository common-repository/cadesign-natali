<?php

namespace Cadesign\NataliApi;

use Bitrix\Main\Engine\Controller;
use Cadesign\NataliApi\CategoryList;
use Cadesign\NataliApi\ProductList;

use Cadesign\NataliApi\Helper;
use Cadesign\NataliApi\Props;

class BindProps
{
    private $values;
    private $colorsImg;

    /**
     * Делаем сопоставление привязанных свойств
     * BindProps constructor.
     * @param array $product
     * @throws \Bitrix\Main\ArgumentException
     */
    public function __construct(array $product, $colorId = false)
    {
        $this->colorsImg = [];
        $bxProperties = [];
		$nataliAttributes = Main::getNataliAttributes();
        $arPropsListing = Props::listing();
        $properties = new Properties();

        //Заполним служебное свойство NATALI
        $bxProperties[] = $prop = [
        	'id' => $nataliAttributes['is_natali'],
            'position' => 0,
            'name' => 'Товар Натали',
            'visible' => false,
            'variation' => false,
            'options' => ['Y']
        ];
//
        //Привязка списков
        foreach ($arPropsListing as $propCode => $arProp)
        {
            if(!$product[$propCode]) continue;

            $prop = [
                'position' => 0,
                'name' => $arProp['NAME'],
                'visible' => $properties->getCurrentValue($propCode) =='Y',
                'variation' => false,
                "options" => [$product[$propCode]]
            ];

            if($nataliAttributes[$propCode])
            	$prop['id'] = $nataliAttributes[$propCode];

            if ($propCode == 'videos')//видео
            {
                $videos = [];
                if (is_array($product['videos']))
                {
                    foreach ($product['videos'] as $video)
                    {
                        $videos[] = $video['url'];
                    }

                    $prop["options"] = $videos;
                }
            }
			elseif ($propCode == 'images')
			{
				foreach ($product['images'] as $image)
				{
					if ($image['colorId'])
					{
						$colorsImg[$image['colorId']] = $image['url'];
					}
				}

				$this->colorsImg = $colorsImg;
				continue;
			}
            elseif ($propCode == 'garments')//Таблица размеров
            {
                if ($product['garments'])
                {
                    $prop["visible"] = false;
                    $prop["options"] = [json_encode($product['garments'], JSON_UNESCAPED_UNICODE)];
                }
            }
            elseif ($propCode == 'materials')//Привязка материалов
            {
                $materials = [];
                if (is_array($product['materials']))
                {
                    foreach ($product['materials'] as $material)
                    {
                        $materials[] = $material["title"];
                    }
                    $prop["options"] = $materials;
                }
            }
            elseif ($propCode == 'brands')//Привязка брендов
            {
                $prop["options"] = [$product['brandId']]; //$brands->convertBinding($product['brandId']);
            }
            elseif ($propCode == 'labels')//Привязка лейблов
            {
                $labels = [];
                if (is_array($product['labels']))
                {
                    foreach ($product['labels'] as $label)
                    {
                        $labels[] = $label; //$labels->convertBinding($labelExternalId);
                    }
                    $prop["options"] = $labels;
                }

            }
			elseif($propCode == 'isMarked')//Товар маркирован
			{
				if($product['isMarked'] == true)
				{
					$prop["options"] = 'Да';
				}
			}
            $bxProperties[] = $prop;
        }

        $this->values = $bxProperties;
    }

    public function getColorsImg()
    {
        return $this->colorsImg;
    }

    /**
     * @return array
     */
    public function getValues(): array
    {
        return $this->values;
    }

}