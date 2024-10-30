<?php
/**
 * Plugin Name: Natali - синхронизация каталога
 * Plugin URI: https://cadesign.ru/
 * Description: Больше не нужно тратить уйму времени, чтобы добавить товары “Натали” на ваш сайт, ведь теперь в один клик вы сможете синхронизировать весь каталог магазина с вашим ресурсом, да еще и абсолютно бесплатно! И это возможно благодаря нашему модулю, который позволяет поддерживать актуальную информацию для пользователей вашей площадки, уделяя этому минимум времени. Хотите синхронизировать более 6000 товаров всего за 5 минут? Сделайте это вместе с нами!
 * Version: 1.1.18
 * Author: CADesign
 * Author URI: https://cadesign.ru
 */

use Cadesign\Natali\Helper;
use Cadesign\NataliApi\BindPrices;

defined('ABSPATH') || exit;

$actPlugins = apply_filters('active_plugins', get_option('active_plugins'));
if (!in_array('woocommerce/woocommerce.php', $actPlugins))
{
    exit;
}

define('CADESIGN_NATALI_PLUGIN_FILE', __FILE__);
define("CADESIGN_NATALI_PLUGIN_DIR", __DIR__);
define('CADESIGN_NATALI_PLUGIN_NAME', basename(__DIR__));

require CADESIGN_NATALI_PLUGIN_DIR . '/vendor/autoload.php';

$arClasses = [
    CADESIGN_NATALI_PLUGIN_DIR . '/lib/Plugin.php',
    CADESIGN_NATALI_PLUGIN_DIR . '/lib/AdminMenu.php',
    CADESIGN_NATALI_PLUGIN_DIR . '/lib/Pages/ImportSettings.php',
    CADESIGN_NATALI_PLUGIN_DIR . '/lib/Pages/PropSettings.php',
    CADESIGN_NATALI_PLUGIN_DIR . '/lib/Pages/DeleteCatalog.php',
    CADESIGN_NATALI_PLUGIN_DIR . '/lib/Pages/SyncCatalog.php',
    CADESIGN_NATALI_PLUGIN_DIR . '/lib/Classes/Main.php',
    CADESIGN_NATALI_PLUGIN_DIR . '/lib/Classes/Helper.php',
    CADESIGN_NATALI_PLUGIN_DIR . '/lib/Classes/Api.php',
    CADESIGN_NATALI_PLUGIN_DIR . '/lib/Classes/CategoryList.php',
    CADESIGN_NATALI_PLUGIN_DIR . '/lib/Classes/Props.php',
    CADESIGN_NATALI_PLUGIN_DIR . '/lib/Classes/ImportControl.php',
    CADESIGN_NATALI_PLUGIN_DIR . '/lib/Classes/UpdateControl.php',
    CADESIGN_NATALI_PLUGIN_DIR . '/lib/Classes/DeleteProduct.php',
    CADESIGN_NATALI_PLUGIN_DIR . '/lib/Storage.php',
    CADESIGN_NATALI_PLUGIN_DIR . '/lib/Classes/ProductList.php',
    CADESIGN_NATALI_PLUGIN_DIR . '/lib/Classes/ProductItem.php',
    CADESIGN_NATALI_PLUGIN_DIR . '/lib/Classes/Product/PrepareFields.php',
    CADESIGN_NATALI_PLUGIN_DIR . '/lib/Classes/Product/BindProps.php',
    CADESIGN_NATALI_PLUGIN_DIR . '/lib/Classes/Product/BindPrices.php',
    CADESIGN_NATALI_PLUGIN_DIR . '/lib/Classes/Product/Properties.php',
    CADESIGN_NATALI_PLUGIN_DIR . '/lib/Classes/Product/Offers.php',
    CADESIGN_NATALI_PLUGIN_DIR . '/lib/Classes/Assoc.php',
    CADESIGN_NATALI_PLUGIN_DIR . '/lib/Classes/Files.php',
    CADESIGN_NATALI_PLUGIN_DIR . '/lib/Classes/WooCommerceApi.php',
];

foreach ($arClasses as $class)
{
    include $class;
}

register_activation_hook(__FILE__, ['CADesign\Natali\Plugin', 'activate']); // При активации плагина
register_deactivation_hook(__FILE__, ['CADesign\Natali\Plugin', 'deactivate']); // При деактивации плагина
register_uninstall_hook(__FILE__, ['CADesign\Natali\Plugin', 'uninstall']); // При удалении плагина
add_filter('plugin_action_links', ['CADesign\Natali\Plugin', 'settingsLink'], 10, 4);

add_action('wp_ajax_cadesign_natali_import', 'cadesign_natali_import_ajax');
add_action('wp_ajax_nopriv_cadesign_natali_import', 'cadesign_natali_import_ajax');
function cadesign_natali_import_ajax()
{
    $response = [];

    if ($_REQUEST['data'])
    {
        $request = $_REQUEST['data'];
        if ($request['nextElement'] == 'nextElement')
        {
            try
            {
                $result = \Cadesign\NataliApi\ImportControl::importNextElement();
            }
            catch (Exception $e)
            {
                $result = [
                	"fail" => $e->getMessage(),
					'error' => $e->getTrace()
				];
				Helper::Log('Ошибка при импорте. ' . json_encode($result));
            }

            if ($result)
            {
                $response['imported'] = \Cadesign\NataliApi\ImportControl::elementsImportedCount();
                $response['result'] = $result;
                $response['next'] = 'true';
                $response['success'] = 'ok';
            }
            else
            {
                $response['imported'] = \Cadesign\NataliApi\ImportControl::elementsImportedCount();
                $response['next'] = 'false';
            }
        }

        if ($request['removeNextElement'])
        {
            if (\Cadesign\NataliApi\ImportControl::removeNext())
            {
                $response['deleted'] = 'ok';
            }
            $response['success'] = 'ok';
        }

        if ($request['startSync'])
        {
            $response['sync'] = 'ok';
			$response['success'] = 'ok';
            \Cadesign\NataliApi\UpdateControl::syncTable();
            $response['items'] = \Cadesign\NataliApi\UpdateControl::getChangeCount();
            $date = date("d.m.Y H:i");
            update_option('last_natali_sync_date', $date);
            $response['date'] = $date;
        }

        if ($request['updateNext'])
        {
            $updateState = \Cadesign\NataliApi\UpdateControl::updateNext();
            $response['items'] = \Cadesign\NataliApi\UpdateControl::getChangeCount();
            if ($updateState)
            {
                $response['success'] = 'ok';
                $response['next'] = 'true';
            }
            else
            {
                $response['updated'] = 'ok';
            }
        }

        if ($request['prepareNext'])
        {
            $isBeforeImportNext = \Cadesign\NataliApi\ImportControl::doBeforeImport();
            if ($isBeforeImportNext)
            {
                $response['next'] = 'ok';
            }
            else
            {
                $response['next'] = false;
            }

            $response['success'] = 'ok';
        }
    }

    header('Content-Type: application/json');
    echo json_encode($response);

    wp_die(); // выход нужен для того, чтобы в ответе не было ничего лишнего, только то что возвращает функция
}
