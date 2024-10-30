<?

namespace CADesign\Natali\Pages;

class SyncCatalog
{
    /**
     * Выводим страницы
     */
    public function outputView()
    {
        ob_start();
        include CADESIGN_NATALI_PLUGIN_DIR . '/views/sync.php';
        ob_end_flush();
    }
}