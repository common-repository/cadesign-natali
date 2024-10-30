<?php

namespace Cadesign\NataliApi;

use Cadesign\Natali\Helper;

/**
 * Класс для связи цен
 * @package Cadesign\NataliApi
 */
class BindPrices
{
	public $values;
	private $priceBindings;

	public function __construct(array $product)
	{
		$priceArr = [];
		$this->priceBindings = $priceBindings = Helper::priceBindings();
		if ($priceBindings)
		{
			$bxProduct['PRICES'] = [];
			foreach ($priceBindings as $priceId => $bindingName)
			{
				$this->values = $this->extraCharge($product[$priceId], $bindingName);
			}
		}
	}

	private function extraCharge($price, $priceId)
	{
		$extraBindings = Helper::extraBindings();

		$extra = $extraBindings[$priceId];
		if ($extra)
		{
			$price = ceil($price * (1 + $extra / 100));
		}

		return $price;
	}
}