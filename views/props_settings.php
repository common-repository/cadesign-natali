<?php

use Cadesign\NataliApi\Main;
$properties = new \Cadesign\NataliApi\Properties();

if (!empty($_POST["submit"]))
{
	$properties->saveData();
	wp_redirect('/wp-admin/admin.php?page=natali_prop&success=Y');
}

$allProps = $properties->getPropsArray();
$currentValues = $properties->getCurrentValues();
?>
<div class="wrap" id="tabs">
    <h1><?php echo get_admin_page_title() ?></h1>
	<? if(esc_sql($_GET['success']) == 'Y'):?>
        <div id="message" class="notice notice-success">
            <p>
                Настройки успешно сохранены
            </p>
        </div>
	<? endif;?>
    <div id="message" class="notice notice-warning">
        <h3>Внимание!</h3>
        <p>
            Изменения в этом разделе вступят в силу после повторной выгрузки товаров.
        </p>
    </div>
    <form method="post" action="/wp-admin/admin.php?page=natali_prop">
        <input type="hidden" name="page" value="<?php echo __CLASS__ ?>"/>
        <table class="wp-list-table widefat fixed striped table-view-list posts">
			<?php foreach ($allProps as $name => $prop): ?>
                <tr>
                    <td>
                        <?=$prop['NAME']?>
                    </td>
                    <td>
                        <input type="hidden" name="<?=$name?>" value="N">
                        <input type="checkbox" name="<?=$name?>" value="Y" <?if($currentValues[$name] == 'Y'):?>checked<? endif;?>>
                    </td>
                </tr>
			<?php endforeach; ?>
        </table>
        <br>
        <button name="submit" value="y">Сохранить</button>
    </form>
    <link rel="stylesheet" href="<?= plugins_url('/css/select2.min.css', __DIR__) ?>">
    <style>
        .select2-results__option .foo {
            color: #ccc;
            font-size: 12px;
        }

        .js-select2 {
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
                    jQuery(this).select2({templateResult: formatCustom});
                    jQuery(this).closest("tr").find('.js-select2').select2()
                } else {
                    jQuery(this).parent().append('<a href="#" class="js-bind-to-sect">Выбрать раздел</a>')
                }
            });
            /**
             * Подключение select2 по клику .js-bind-to-sect
             */
            jQuery(document).on("click", ".js-bind-to-sect", function () {
                jQuery(this).parent().find('.js-select2').select2({templateResult: formatCustom});
                jQuery(this).fadeOut(0);
                jQuery(this).closest("tr").find('[data-size-category] select').select2()
                return false;
            });
        });
    </script>
</div>