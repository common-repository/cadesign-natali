<?php
$lastSync = get_option('last_natali_sync_date');
$changesCount = \Cadesign\NataliApi\UpdateControl::getChangeCount();
?>
<div class="wrap" id="tabs">
    <h1><?php echo get_admin_page_title() ?></h1>

    <div class="theme-browser rendered">
        <div class="themes wp-clearfix">
            <div class="theme active" tabindex="0">
                <div class="theme-id-container">
                    <h2 class="theme-name" id="storefront-name">
                        Проверка изменений с Natali:
                    </h2>
                </div>
                <div class="theme-wrapp">
                    <div class="tile-heading"></div>
                    <div class="tile-body">
                        <h3 class="">Последняя проверка в: <span class="js-update-date"><?php echo $lastSync ?></span></h3>
                        <img src="<?php echo plugins_url( '/img/loader-natali.gif', __DIR__ );?>" alt=""
                             class="js-loader-get-updates" style="display: none;">
                    </div>
                    <a class="button button-primary customize load-customize hide-if-no-customize" id="btn_get_updates">
                        Получить данные
                    </a>
                </div>
            </div>
            <div class="theme active" tabindex="0">
                <div class="theme-id-container">
                    <h2 class="theme-name" id="storefront-name">
                        Обновление:
                    </h2>
                </div>
                <div class="theme-wrapp">
                    <div class="tile-heading"></div>
                    <div class="tile-body">
                        <h3 class="">Устаревших элементов: <span class="js-update-count"><?php echo $changesCount ?></span>
                        </h3>
                        <div class="adm-detail-content-btns">
                            Количество одновременно выгружаемых товаров: <br>
                            <input type="number" id="count_per_step" max="100" name="count_per_step" value="10">
                        </div>
                        <img src="<?php echo plugins_url( '/img/loader-natali.gif', __DIR__ );?>" alt=""
                             class="js-loader-update-elements" style="display: none;">
                    </div>
                    <a class="button button-primary customize load-customize hide-if-no-customize"
                       id="btn_start_update">Обновить элементы</a>
                </div>
            </div>
        </div>
    </div>
    <div class="theme-browser rendered">
        <div class="themes wp-clearfix">
            <div class="theme active" tabindex="0" style="width: 48.4%">
                <div class="theme-id-container">
                    <h2 class="theme-name" id="storefront-name">
                        Настройка автоматического обновления:
                    </h2>
                </div>
                <div class="theme-wrapp">
                    <div class="tile-heading"></div>
                    <div class="tile-body">
                        php <?php echo CADESIGN_NATALI_PLUGIN_DIR?>/updateNatali.php http://<?php echo $_SERVER['HTTP_HOST']?>
                        <br><br>
                        <a href="/wp-admin/admin.php?page=wc-status&tab=logs&source=cadesign-natali" target="_blank">Логи работы плагина</a>
                    </div>
                </div>
            </div>
        </div>
    </div>

</div>
<style>
    .theme-wrapp {
        margin: 10px;
    }

    .tile-body {
        height: 130px;
    }

    .theme-browser .theme,
    .theme-browser .theme:focus, .theme-browser .theme:hover {
        cursor: default;
        background: #fff;
    }
</style>
<script>
    let syncApp = {};
    syncApp.countToDelete = 0;
    syncApp.countDeleted = 0;
    syncApp.pauseState = true;
    syncApp.pauseStateUpdate = true;
    syncApp.productsCount = <?=$changesCount?>;


    /**
     * Метод для импорта
     */
    syncApp.ajaxSyncCatalog = function () {
        if (!syncApp.pauseState) {
            jQuery.ajax({
                url: ajaxurl + '?action=cadesign_natali_import&ajax=Y',
                data: {'data': {startSync: 'syncElements'}},
                method: 'POST',
                dataType: 'json',
                success: function (response) {
                    jQuery(".js-loader-get-updates").hide();
                    syncApp.pauseState = true;
                    jQuery(".js-update-date").text(response.date);
                    jQuery(".js-update-count").text(response.items);
                }
            });
        }
    };

    syncApp.ajaxQuery = function (num){
        return jQuery.ajax({
            url: ajaxurl + '?action=cadesign_natali_import&ajax=Y&offset=' + num,
            data: {'data': {updateNext: 'updateNext'}},
            method: 'POST',
            dataType: 'json',
            success: function (response) {
                jQuery(".js-update-count").text(response.items);
                if (response.success === 'ok') {
                    // syncApp.ajaxStartUpdate();
                } else {
                    syncApp.pauseStateUpdate = true;
                    jQuery(".js-loader-get-updates").hide();
                }

                syncApp.productsCount = Math.min(response.items, syncApp.productsCount);

                jQuery(".js-update-count").text(syncApp.productsCount);
            }
        });
    }

    syncApp.ajaxStartUpdate = function () {
        if (!syncApp.pauseStateUpdate) {
            let count_per_step = jQuery('#count_per_step').val();
            if(count_per_step>100)
            {
                count_per_step = 100;
            }
            let promises = [];
            for (let i = 0; i < count_per_step; i++) {
                promises.push(syncApp.ajaxQuery(i));
            }

            Promise.all(promises)
                .then(res => {
                    syncApp.ajaxStartUpdate();
                })
                .catch(err => {
                    syncApp.ajaxStartUpdate();
                });
        }
    }

    jQuery(document).on('click', "#btn_get_updates", function () {
        syncApp.pauseState = !syncApp.pauseState;
        jQuery(".js-loader-get-updates").show();
        syncApp.ajaxSyncCatalog();
    });

    jQuery(document).on('click', "#btn_start_update", function () {
        syncApp.pauseStateUpdate = !syncApp.pauseStateUpdate;

        if (syncApp.pauseStateUpdate) {
            jQuery("#btn_start_update").text('Обновить элементы');
            jQuery(".js-loader-update-elements").hide();
        } else {
            jQuery("#btn_start_update").text('Пауза');
            jQuery(".js-loader-update-elements").show();
            syncApp.ajaxStartUpdate();
        }

    });
</script>
