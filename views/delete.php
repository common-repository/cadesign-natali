<?php
$countImportedItems = \Cadesign\Natali\Assoc::getCount();
?>
<div class="wrap" id="tabs">
    <h1><?php echo get_admin_page_title() ?></h1>
    <div id="message" class="notice notice-error">
        <p>
            Внимание! После нажатия "Начать удаление" будут удалены все товары, которые были импортированы данным
            модулем, а так же удалит их торговые предложения и все фотографии.
        </p>
    </div>

    <div class="adm-detail-content-wrap">

        <div class="adm-detail-content">
            <p>Всего товаров: <span><?php echo $countImportedItems ?></span></p>
            <p>Удалено: <span id="js-ca-count-elements">0</span></p>
        </div>

        <div class="adm-detail-content-btns">
            Количество одновременно удаляемых товаров: <br>
            <input type="number" id="count_per_step" max="100" name="count_per_step" value="10">
        </div>
        <div class="adm-detail-content consoleScreen"></div>

        <div class="adm-detail-content-btns-wrap" id="tabControl_buttons_div" style="margin-top: 10px; left: 0; width:0;">
            <div class="adm-detail-content-btns">
                <input type="button" id="btn_start_delete" name="delete" value="Удалить" class="adm-btn-save">
            </div>
        </div>
        <br>
        <br>
        <a href="/wp-admin/admin.php?page=wc-status&tab=logs&source=cadesign-natali" target="_blank">Логи работы плагина</a>
        <script>
            let deleteApp = {};
            deleteApp.countToDelete = <?php echo $countImportedItems?>;
            deleteApp.countDeleted = 0;
            deleteApp.pauseState = true;
            /**
             * Метод для импорта
             */
            deleteApp.ajaxQuery = function (num){
                return jQuery.ajax({
                    url: ajaxurl + '?action=cadesign_natali_import&ajax=Y&offset=' + num,
                    data: {'data': {removeNextElement: 'nextElement'}},
                    method: 'POST',
                    dataType: 'json',
                    success: function (response) {
                        if (response.success === 'ok') {
                            if (response.deleted) {
                                //Обновим количество
                                deleteApp.countDeleted++;
                                jQuery("#js-ca-count-elements").text(deleteApp.countDeleted);
                                // deleteApp.ajaxDeleteNext();
                            } else {
                                jQuery("#btn_start_delete").hide();
                                jQuery(".consoleScreen").text("Удаление завершено");
                            }
                        }
                    }
                });
            }

            deleteApp.ajaxDeleteNext = function () {
                if (!deleteApp.pauseState) {
                    let count_per_step = jQuery('#count_per_step').val();
                    if(count_per_step>100)
                    {
                        count_per_step = 100;
                    }
                    let promises = [];
                    for (let i = 0; i < count_per_step; i++) {
                        promises.push(deleteApp.ajaxQuery(i));
                    }

                    Promise.all(promises)
                        .then(res => {
                            deleteApp.ajaxDeleteNext();
                        })
                        .catch(err => {
                            deleteApp.ajaxDeleteNext();
                        });
                }
            };
            /*
            Старт удаления по клику
             */

            jQuery(document).on('click', "#btn_start_delete", function () {
                deleteApp.pauseState = !deleteApp.pauseState;

                if (deleteApp.pauseState) {
                    jQuery("#btn_start_delete").val('Продолжить');
                } else {
                    jQuery("#btn_start_delete").val('Пауза');
                    deleteApp.ajaxDeleteNext();
                }
            });
        </script>
    </div>
</div>