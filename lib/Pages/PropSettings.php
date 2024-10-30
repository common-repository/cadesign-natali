<?php

namespace CADesign\Natali\Pages;

class PropSettings
{
	/**
	 * Выводим страницы
	 */
	public function outputView()
	{
		ob_start();
		include CADESIGN_NATALI_PLUGIN_DIR . '/views/props_settings.php';
		ob_end_flush();
	}
}