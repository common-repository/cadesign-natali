<?php

namespace Cadesign\NataliApi;

class Properties
{
	private const props = [
		'labels' => [
			'NAME' => 'Лейбл',
			'DEFAULT' => 'Y'
		],
		'composition' => [
			'NAME' => 'Состав',
			'DEFAULT' => 'Y'
		],
		'materials' => [
			'NAME' => 'Материалы',
			'DEFAULT' => 'Y'
		],
		'videos' => [
			'NAME' => 'Видео',
			'DEFAULT' => 'Y'
		],
		'brands' => [
			'NAME' => 'Бренд',
			'DEFAULT' => 'Y'
		],
		'minSize' => [
			'NAME' => 'Размер мин',
			'DEFAULT' => 'Y'
		],
		'maxSize' => [
			'NAME' => 'Размер макс',
			'DEFAULT' => 'Y'
		],
		'isMarked' => [
			'NAME' => 'Маркированный товар',
			'DEFAULT' => 'Y'
		],
	];
	private const PREFIX = 'natali_';

	public function getPropsArray(): array
	{
		return self::props;
	}

	public function getCurrentValues(): array
	{
		$result = [];

		foreach (self::props as $name => $prop)
		{
			$result[$name] = $this->getCurrentValue($name);
		}

		return $result;
	}

	public function getCurrentValue($name)
	{
		return get_option(self::PREFIX . $name, self::props[$name]['DEFAULT']);
	}

	public function saveData()
	{
		foreach (self::props as $name => $prop)
		{
			$this->updateProp($name, $_POST[$name]);
		}
	}

	private function updateProp(string $name, $value)
	{
		$value = $this->getValueToSave($value, self::props[$name]['DEFAULT']);

		update_option(self::PREFIX . $name, $value);
	}

	private function getValueToSave($value, $defaultValue)
	{
		$value = esc_sql($value);

		if (!$value)
		{
			$value = $defaultValue;
		}

		return $value;
	}
}