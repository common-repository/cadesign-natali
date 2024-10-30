<?php

namespace CADesign\Natali;

defined('ABSPATH') || exit;

class Plugin
{
    public static function activate()
    {
        if (!current_user_can('activate_plugins'))
        {
            return;
        }
        global $wpdb;
        $arSql = [
            "CREATE TABLE IF NOT EXISTS `" . $wpdb->prefix . "cadesign_natali_bindings` (
                `ID` int(11) NOT NULL AUTO_INCREMENT,
                `BINDING_TYPE` text COLLATE utf8_unicode_ci NOT NULL,
                `TARGET_ID` text COLLATE utf8_unicode_ci NOT NULL,
                `FETCHED_ID` text COLLATE utf8_unicode_ci NOT NULL,
                PRIMARY KEY (`ID`)
            ) ENGINE=InnoDB CHARACTER SET utf8 COLLATE utf8_general_ci;",
            "CREATE TABLE IF NOT EXISTS `" . $wpdb->prefix . "cadesign_natali_import` (
                `ID` int(11) NOT NULL AUTO_INCREMENT,
                `NATALI_ID` text COLLATE utf8_unicode_ci NOT NULL,
                `BX_ID` text COLLATE utf8_unicode_ci NOT NULL,
                `LAST_MODIFY` datetime NOT NULL,
                PRIMARY KEY (`ID`)
            ) ENGINE=InnoDB CHARACTER SET utf8 COLLATE utf8_general_ci;",
            "CREATE TABLE IF NOT EXISTS `" . $wpdb->prefix . "cadesign_natali_updates` (
                `ID` int(11) NOT NULL AUTO_INCREMENT,
                `NATALI_ID` text COLLATE utf8_unicode_ci NOT NULL,
                `STATUS` text COLLATE utf8_unicode_ci NOT NULL,
                `TYPE` text COLLATE utf8_unicode_ci NOT NULL,
                `LAST_DATE` datetime NOT NULL,
                PRIMARY KEY (`ID`)
            ) ENGINE=InnoDB CHARACTER SET utf8 COLLATE utf8_general_ci;",
            "CREATE TABLE `" . $wpdb->prefix . "cadesign_natali_assoc` (
                `WP_ID` int(11) NOT NULL,
                `NATALI_ID` varchar(15) NOT NULL
            ) ENGINE=InnoDB CHARACTER SET utf8 COLLATE utf8_general_ci;"
        ];

        self::runSql($arSql);
    }

    public static function deactivate()
    {
        if (!current_user_can('activate_plugins'))
        {
            return;
        }
    }

    public static function uninstall()
    {
        if (!current_user_can('activate_plugins'))
        {
            return;
        }
        global $wpdb;
        $arSql = [
            "DROP TABLE `" . $wpdb->prefix . "cadesign_natali_bindings`",
            "DROP TABLE `" . $wpdb->prefix . "cadesign_natali_import`",
            "DROP TABLE `" . $wpdb->prefix . "cadesign_natali_updates`",
            "DROP TABLE `" . $wpdb->prefix . "cadesign_natali_assoc`"
        ];
        self::runSql($arSql);
    }

    public static function settingsLink($actions, $pluginFile)
    {
        if (strpos($pluginFile, 'cadesign-natali') === false)
        {
            return $actions;
        }

        $settingsLink = '<a href="admin.php?page=natali_import">' . __('Настройки', 'salesbeat') . '</a>';
        array_unshift($actions, $settingsLink);

        return $actions;
    }

    private static function runSql(array $arSql)
    {
        global $wpdb;
        foreach ($arSql as $sql)
        {
            $wpdb->query($sql);
        }
    }
}