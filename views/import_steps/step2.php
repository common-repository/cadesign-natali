<div class="wrap" id="tabs">
    <h1><?php echo get_admin_page_title() ?></h1>
    <?
    if (isset($_REQUEST['PREPARE_IMPORT']) && $_REQUEST['PREPARE_IMPORT'] == 'Y')
    {
        \Cadesign\NataliApi\ImportControl::dataBeforeClean();
        $storage = [];
        \CADesign\Natali\Storage::main()->set('READY_CATS', $storage);

        $prepareImport = true;
    }

    $sectionsCount = \Cadesign\NataliApi\ImportControl::sectionsCount();
    $elementsCount = \Cadesign\NataliApi\ImportControl::elementsCount();
    $elementsImportedCount = \Cadesign\NataliApi\ImportControl::elementsImportedCount();
    ?>
    <? if ($prepareImport): ?>
        <div class="adm-detail-content-wrap">
            <div class="adm-detail-content">
                <p>Идет сбор сведений для подготовки к импорту, пожалуйста подождите. </p>
                <p>Подготовлено разделов:<span id="js-prepared-sections">0</span></p>
            </div>
        </div>
        <script>
            var app = {};
            app.preparedSections = 0;
            /**
             * Метод для подготовки импорта категории
             */
            app.ajaxPrepareNext = function () {
                if (!app.pauseState) {
                    jQuery.ajax({
                        url: ajaxurl + '?action=cadesign_natali_import&ajax=Y',
                        data: {'data': {prepareNext: 'prepareNext'}},
                        method: 'POST',
                        dataType: 'json',
                        success: function (response) {
                            if (response.success === 'ok') {
                                if (response.next === 'ok') {
                                    app.preparedSections++;
                                    document.getElementById("js-prepared-sections").innerText = app.preparedSections;
                                    app.ajaxPrepareNext();
                                } else {
                                    document.getElementById("js-prepared-sections").innerText = app.preparedSections;
                                    var loc = window.location;
                                    window.location = loc.pathname + '?page=natali_import&step=2';
                                }
                            }
                        },
                        error:function () {
                            app.ajaxPrepareNext();
                        }
                    });
                }
            };
            app.ajaxPrepareNext();
        </script>
    <? else: ?>
        <div class="adm-detail-content-wrap">
            <div class="adm-detail-content">
                <h4>Выбрано</h4>
                <p>Разделов: <span><?php echo $sectionsCount ?></span></p>
                <p>Элементов: <span><?php echo $elementsCount ?></span></p>

            </div>
            <div class="adm-detail-content">
                <h4>Импортировано</h4>
                <p>Элементов <span id="js-ca-count-elements"><?php echo $elementsImportedCount ?></span></p>
            </div>
            <div class="adm-detail-content">
                <div class="ui-progressbar ui-progressbar-lg">
                    <div class="ui-progressbar-track">
                        <div class="ui-progressbar-bar" id="progress" style="width:<?php echo $elementsImportedCount ?>%;"></div>
                    </div>
                </div>
            </div>
            <div class="adm-detail-content" id="consoleScreen"></div>

            <div class="adm-detail-content-btns-wrap" id="tabControl_buttons_div" style="left: 0px;">
                <div class="adm-detail-content-btns">
                    Количество одновременно выгружаемых товаров: <br>
                    <input type="number" id="count_per_step" max="100" name="count_per_step" value="10">
                </div>
            </div>

            <div class="adm-detail-content-btns-wrap" id="tabControl_buttons_div" style="margin-top: 10px;left: 0px;">
                <div class="adm-detail-content-btns">
                    <input type="submit" id="btn_start_import" name="save" value="Начать импорт" class="adm-btn-save">
                </div>
            </div>
            <br>
            <br>
            <a href="/wp-admin/admin.php?page=wc-status&tab=logs&source=cadesign-natali" target="_blank">Логи работы плагина</a>
        </div>
        <script>
            var app = {};
            app.elementsCount = <?php echo $elementsCount?>;
            app.importedCount = <?php echo $elementsImportedCount?>;
            app.pauseState = true;
            /**
             * Метод для импорта
             */
            app.ajaxQuery = function (num){
                return jQuery.ajax({
                    url: ajaxurl + '?action=cadesign_natali_import&ajax=Y&offset=' + num,
                    data: {'data': {nextElement: 'nextElement'}},
                    method: 'POST',
                    dataType: 'json',
                    success: function (response) {
                        if (response.success === 'ok') {
                            // app.ajaxImportNext();
                        } else {
                            if (response.next === 'false') {
                                document.getElementById("consoleScreen").innerText = "Импортирование завершено";
                                jQuery("#btn_start_import").hide();
                            } else {
                                document.getElementById("consoleScreen").innerText = response;
                            }
                        }
                        if (response.imported) {
                            //Обновим количество
                            app.importedCount = Math.max(app.importedCount, response.imported);
                            app.updateProgress();
                            document.getElementById("js-ca-count-elements").innerText = app.importedCount;
                        }
                    }
                });
            }

            app.ajaxImportNext = function () {
                if (!app.pauseState) {
                    let count_per_step = jQuery('#count_per_step').val();
                    if(count_per_step>100)
                    {
                        count_per_step = 100;
                    }
                    let promises = [];
                    for (let i = 0; i < count_per_step; i++) {
                        promises.push(app.ajaxQuery(i));
                    }

                    Promise.all(promises)
                        .then(res => {
                            app.ajaxImportNext();
                        })
                        .catch(err => {
                            app.ajaxImportNext();
                        });
                }
            };

            app.updateProgress = function () {
                progress = app.importedCount / (app.elementsCount / 100);
                document.getElementById("progress").style.width = progress + '%';
            };

            app.updateProgress();
            /*
            Старт импорта по клику
             */
            jQuery(document).on('click', "#btn_start_import", function () {
                app.pauseState = !app.pauseState;

                if (app.pauseState) {
                    jQuery("#btn_start_import").val('Продолжить');
                } else {
                    jQuery("#btn_start_import").val('Пауза');
                    app.ajaxImportNext();
                }
            });
        </script>
    <? endif; ?>
</div>