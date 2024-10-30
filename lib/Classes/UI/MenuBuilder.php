<?php
namespace Cadesign\NataliApi;
class MenuBuilder{
    function addMenuItem(&$aGlobalMenu, &$aModuleMenu)
    {
        global $USER;

        if ($USER->IsAdmin()) {

            $aGlobalMenu['global_menu_natali'] = [
                'menu_id' => 'natali',
                'text' => GetMessage("CADESIGN_NATALI_CATALOG_NATALI"),
                'title' => GetMessage("CADESIGN_NATALI_SYNC_NATALI"),
                'url' => 'cadesign_natali.php',
                'sort' => 1000,
                'items_id' => 'global_menu_natali',
                'help_section' => 'natali',
                'more_url'    => [
                ],
                'items' => [
                    [
                        'parent_menu' => 'global_menu_natali',
                        'sort'        => 10,
                        'url'         => 'cadesign_natali.php?action=step1',
                        'more_url'    => [
                            'cadesign_natali.php?action=step2',
                            'cadesign_natali.php?action=step3',
                            'cadesign_natali.php?action=step4',
                            'cadesign_natali.php?action=step4&PREPARE_IMPORT=Y',

                        ],
                        'text'        => GetMessage("CADESIGN_NATALI_IMPORT_CATALOG_NATALI"),
                        'title'       => GetMessage("CADESIGN_NATALI_IMPORT_CATALOG_NATALI"),
                        'icon'        => 'update_menu_icon',
                        'page_icon'   => 'update_menu_icon',
                        'items_id'    => 'menu_natali',
                    ],
                    [
                        'parent_menu' => 'global_menu_natali',
                        'sort'        => 20,
                        'url'         => 'cadesign_natali.php?action=update',
                        'text'        => GetMessage("CADESIGN_NATALI_UPDATE_SETTINGS"),
                        'title'       => GetMessage("CADESIGN_NATALI_UPDATE_SETTINGS"),
                        'icon'        => 'sys_menu_icon',
                        'page_icon'   => 'sys_menu_icon',
                        'items_id'    => 'menu_natali',
                    ],
                    [
                        'parent_menu' => 'global_menu_natali',
                        'sort'        => 30,
                        'url'         => 'cadesign_natali.php?action=delete',
                        'text'        => GetMessage("CADESIGN_NATALI_DELETE_CATALOG"),
                        'title'       => GetMessage("CADESIGN_NATALI_DELETE_CATALOG"),
                        'icon'        => 'sys_menu_icon',
                        'page_icon'   => 'sys_menu_icon',
                        'items_id'    => 'menu_natali',
                    ]


                ],
            ];

        }
    }


}