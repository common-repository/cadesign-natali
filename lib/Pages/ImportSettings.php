<?php


namespace CADesign\Natali\Pages;


class ImportSettings
{
    /**
     * Выводим страницы
     */
    public function outputView()
    {
        ob_start();
        include CADESIGN_NATALI_PLUGIN_DIR . '/views/import_settings.php';
        ob_end_flush();
    }
}