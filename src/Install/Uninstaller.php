<?php

declare(strict_types=1);
namespace Tekod\Suggest404Links\Install;

use Tekod\Suggest404Links\Core\ServiceContainer;
use Tekod\Suggest404Links\Services\Logger;


/**
 * Class Uninstaller.
 */
class Uninstaller
{

    /**
     * Run uninstaller.
     */
    public static function run()
    {
        if (defined('SUGGEST_404_LINKS_PLUGINBASENAME')) {
            return; // probably we have active clone of this plugin, it is better to not touch anything
        }

        // call main "init" to set up constants and execute bootstrap to prepare services
        require __DIR__ . '/../../init.php';

        // late-initialization should not happen, prepare service container manually
        ServiceContainer::getInstance()->init([]);

        // perform uninstallation
        self::uninstallPlugin();
    }


    /**
     * Do uninstallation process.
     * Note that we have initialized services but without check that all requirements are met, use services carefully.
     */
    protected static function uninstallPlugin(): void
    {
        // delete database tables
        //Helpers::deleteTable(suggest_404_links()->getIndexModel()->getTableName());
        //Helpers::deleteTable(suggest_404_links()->getListsModel()->getTableName());

        // delete page
        //Helpers::deletePage(sanitize_title_with_dashes(__('enhancedproductsearch', 'eps')));

        // remove entries from "options" table
        delete_option((new Installer())->getDatabaseVersionKey());

        // remove log file
        self::removeLogFile();

        // clear WP cache
        wp_cache_flush();
    }


    /**
     * Delete logger file.
     */
    protected static function removeLogFile(): void
    {
        $path = Logger::getInstance()->getLogPath();
        if (is_file($path)) {
            unlink($path);
        }
    }

}
