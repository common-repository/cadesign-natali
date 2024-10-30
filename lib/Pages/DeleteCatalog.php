<?php


namespace CADesign\Natali\Pages;


class DeleteCatalog
{
    /**
     * Выводим страницы
     */
    public function outputView()
    {
        ob_start();
        include CADESIGN_NATALI_PLUGIN_DIR . '/views/delete.php';
        ob_end_flush();
    }
}