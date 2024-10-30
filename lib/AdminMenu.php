<?php


namespace CADesign\Natali;

use CADesign\Natali\Pages\DeleteCatalog;
use CADesign\Natali\Pages\ImportSettings;
use CADesign\Natali\Pages\SyncCatalog;
use CADesign\Natali\Pages\PropSettings;

defined('ABSPATH') || exit;

if (class_exists('CADesign\\Natali\\AdminMenu', false))
    return new AdminMenu();

class AdminMenu
{
    public $option = 'manage_options';
    public $section = 'cadesign_natali';

    public function __construct()
    {
        add_action('admin_menu', [$this, 'addAdminMenu'], 1);
        add_action('admin_menu', [$this, 'removeAdminMenu'], 9999);
    }

    public function addAdminMenu()
    {
        add_menu_page(
            __('Каталог Natali', 'cadesign_natali'),
            __('Каталог Natali', 'cadesign_natali'),
            $this->option,
            $this->section,
            null,
            'dashicons-database-import',
            56
        );

        add_submenu_page(
            $this->section,
            __('Импорт каталога Natali', 'cadesign_natali'),
            __('Импорт каталога Natali', 'cadesign_natali'),
            $this->option,
            'natali_import',
            [new ImportSettings, 'outputView']
        );

		add_submenu_page(
			$this->section,
			__('Свойства', 'cadesign_natali'),
			__('Свойства', 'cadesign_natali'),
			$this->option,
			'natali_prop',
			[new PropSettings, 'outputView']
		);
        add_submenu_page(
            $this->section,
            __('Обновление каталога', 'cadesign_natali'),
            __('Обновление каталога', 'cadesign_natali'),
            $this->option,
            'natali_sync',
            [new SyncCatalog, 'outputView']
        );

        add_submenu_page(
            $this->section,
            __('Удаление каталога', 'cadesign_natali'),
            __('Удаление каталога', 'cadesign_natali'),
            $this->option,
            'natali_delete',
            [new DeleteCatalog, 'outputView']
        );
    }

    public function removeAdminMenu()
    {
        remove_submenu_page($this->section, $this->section);
    }
}