<?php

use Cadesign\Natali\WooCommerceApi;
use Cadesign\NataliApi\BindingsTable;
use Cadesign\NataliApi\Main;

if (!empty($_POST["SELECTED_SECTIONS"]))
{
    $selectedSections = esc_sql($_REQUEST['SELECTED_SECTIONS']);
    $prices = esc_sql($_REQUEST['SELECTED_PRICE']);
	$colorMode = ($_REQUEST['COLORS_ADD'] == "Y")? "Y":"N";
    Cadesign\Natali\Helper::rewriteBindings($selectedSections, "sections", true);
    Cadesign\Natali\Helper::rewriteBindings(["user_key" => esc_sql($_REQUEST['user_key'])], "user_key", true);
    Cadesign\Natali\Helper::rewriteBindings(["secret_key" => esc_sql($_REQUEST['secret_key'])], "secret_key", true);
    Cadesign\Natali\Helper::rewriteBindings(["colors_add" => $colorMode], "colors_add", false);

    $selectedPrice = Cadesign\Natali\Helper::array_filter_recursive(esc_sql($_REQUEST['SELECTED_PRICE']));

    if ($_REQUEST['EXTRA'])
    {
        $extra = Cadesign\Natali\Helper::array_filter_recursive(esc_sql($_REQUEST['EXTRA']));
        Cadesign\Natali\Helper::rewriteBindings($extra, "extra");
    }
    Cadesign\Natali\Helper::rewriteBindings($selectedPrice, "price");
	Main::addMissingAttributes();
//	\Cadesign\Natali\Assoc::truncate();
    wp_redirect('/wp-admin/admin.php?page=natali_import&step=2&PREPARE_IMPORT=Y');
}

//Метод который вытаскивает список разделов из Api
$sectionsNatali = \Cadesign\NataliApi\CategoryList::getCategoryTree(false, false, 'list');

//Метод который вытаскивает список разделов сайта

$sectionsBindings = Cadesign\Natali\Helper::sectionsBindings();
$colorSingleItemBindings = Cadesign\NataliApi\Main::getActualSettings();
$keysBindings = Cadesign\Natali\Helper::keysBindings();
$prices = \Cadesign\Natali\Helper::priceBindings();
$extra = \Cadesign\Natali\Helper::extraBindings();

?>
<div class="wrap" id="tabs">
    <h1><?php echo get_admin_page_title() ?></h1>
    <div id="message" class="notice notice-warning">
        <h3>Внимание!</h3>
        <p>
            Перед началом импорта убедитесь, что у вас достаточно места для хранения фотографий товаров.
            Полностью выгруженный каталог может занимать до 60 ГБ свободного пространства на диске.
        </p>
    </div>
    <div id="message" class="notice notice-warning">
        <p>
            Полная выгрузка каталога может занять более 6 часов. При первой настройке импорта рекомендуем попробовать
            выгрузить 1 раздел для тестирования.
        </p>
    </div>
    <form method="post" action="/wp-admin/admin.php?page=natali_import&step=1">
        <input type="hidden" name="page" value="<?php echo __CLASS__ ?>"/>
        <p>Получить <a href="/wp-admin/admin.php?page=wc-settings&tab=advanced&section=keys" target="_blank">Api
                ключи</a> для работы модуля. </p>
        <p>Необходимы права на чтение/запись</p>

        <table class="wp-list-table widefat fixed striped table-view-list posts">
            <tr>
                <td>
                    Пользовательский ключ
                </td>
                <td>
                    <input type="text" name="user_key" value="<?php echo $keysBindings["user_key"] ?>">
                </td>
            </tr>
            <tr>
                <td>
                    Секретный код пользователя
                </td>
                <td>
                    <input type="text" name="secret_key" value="<?php echo $keysBindings["secret_key"] ?>">
                </td>
            </tr>
            <tr>
                <td>
                    <label for="colorsAdd">Выгружать расцветки в разные карточки товара</label>
                </td>
                <td>
                    <input type="hidden" name="COLORS_ADD" value="N">
                    <input type="checkbox" name="COLORS_ADD" id="colorsAdd" value="Y" <?if(Main::colorSingleItem()):?>checked<? endif;?>>
                </td>
            </tr>
        </table>
        <br><br>
        <table class="wp-list-table widefat fixed striped table-view-list posts">
            <tr>
                <td>Тип цены</td>
                <td>Наценка %</td>
            </tr>
            <tr>
                <td>
                    <select name="SELECTED_PRICE[1]" onchange="" id="">
                        <option value=""></option>
                        <option value="price" <? if (isset($prices['price'])): ?>selected<? endif; ?>>Цена продукта
                            розница.
                        </option>
                        <option value="priceSmallWholesale"
                                <? if (isset($prices['priceSmallWholesale'])): ?>selected<? endif; ?>>Цена продукта
                            мелкий опт.
                        </option>
                        <option value="priceWholesale" <? if (isset($prices['priceWholesale'])): ?>selected<? endif; ?>>
                            Цена продукта опт.
                        </option>
                    </select>
                </td>
                <td>
                    <input type="text" name="EXTRA[1]" onchange="" id="" value="<?php echo $extra[1] ?>">
                </td>
            </tr>
        </table>

        <br><br>

        <table class="wp-list-table widefat fixed striped table-view-list posts">
            <?
            foreach ($sectionsNatali as $section)
            {
                $arRow = [];
                switch ($section['DEPTH'])
                {
                    case 0:
                        $depthMargin = '';
                        $section['title'] = '<b>' . $section['title'] . '</b>';
                        break;
                    case 1:
                        $depthMargin = str_repeat('- ', 1);
                        break;
                    case 2:
                        $depthMargin = str_repeat('- ', 2);
                        break;
                    case 3:
                        $depthMargin = str_repeat('- ', 3);
                        break;
                    case 4:
                        $depthMargin = str_repeat('- ', 4);
                        break;
                    default:
                        $depthMargin = '';
                }

                $sectionString = $depthMargin . $section['title'] . " [" . $section['categoryId'] . "]";
                if (!empty($section['set']))
                {
                    $sectionString .= "<br><small>(" . implode(' -> ', $section['set']) . ")</small>";
                }
                ?>
                <tr>
                    <td>
                        <?php echo $sectionString ?>
                    </td>
                    <td data-category-list>
                        <?
                        $args = [
                            'show_option_all' => 'Не импортировать',
                            'show_option_none' => '',
                            'option_none_value' => -1,
                            'orderby' => 'ID',
                            'order' => 'ASC',
                            'show_last_update' => 0,
                            'show_count' => 0,
                            'hide_empty' => 0,
                            'child_of' => 0,
                            'exclude' => '',
                            'echo' => 1,
                            'selected' => $sectionsBindings[$section['categoryId']] ?? false,
                            'hierarchical' => 1,
                            'name' => 'SELECTED_SECTIONS[' . $section['categoryId'] . ']',
                            'id' => 'name'.$section['categoryId'],
                            'class' => 'postform js-select2',
                            'depth' => 0,
                            'tab_index' => 0,
                            'taxonomy' => 'product_cat',
                            'hide_if_empty' => false,
                            'value_field' => 'term_id', // значение value e option
                            'required' => false,
                        ];

                        wp_dropdown_categories($args); ?>
                    </td>
                </tr>
                <?
            }
            ?>
        </table>
        <br>
        <button>Сохранить</button>
    </form>
    <link rel="stylesheet" href="<?= plugins_url('/css/select2.min.css', __DIR__) ?>">
    <style>
        .select2-results__option .foo{
            color: #ccc; font-size: 12px;
        }

        .js-select2{
            display: none;
        }
    </style>
    <script type="text/javascript" src="<?= plugins_url('/js/select2.js', __DIR__) ?>"></script>
    <script>
        jQuery(document).ready(function () {
            function formatCustom(state) {
                return jQuery(
                    '<div><div>' + state.text + '</div></div>'
                );
            }
            jQuery('[data-category-list] .postform').each(function () {
                if (jQuery(this).val() !== '0') {
                    jQuery(this).select2({ templateResult: formatCustom });
                    jQuery(this).closest("tr").find('.js-select2').select2()
                } else {
                    jQuery(this).parent().append('<a href="#" class="js-bind-to-sect">Выбрать раздел</a>')
                }
            });
            /**
             * Подключение select2 по клику .js-bind-to-sect
             */
            jQuery(document).on("click", ".js-bind-to-sect", function () {
                jQuery(this).parent().find('.js-select2').select2({ templateResult: formatCustom });
                jQuery(this).fadeOut(0);
                jQuery(this).closest("tr").find('[data-size-category] select').select2()
                return false;
            });
        });
    </script>
</div>