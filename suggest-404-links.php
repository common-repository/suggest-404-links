<?php
/**
 * Plugin Name: Suggest 404 links
 * Plugin URI:  https://wordpress.org/plugins/suggest-404-links
 * Description: Appends "did you mean" links to your "404" page.
 * Version:     0.3.1
 * Author:      Miroslav Curcic
 * Author URI:  https://profiles.wordpress.org/tekod
 * Text Domain: suggest-404-links
 * License URI: https://www.gnu.org/licenses/old-licenses/gpl-2.0.html
 * License:     GPL v2 or later
 */

declare(strict_types=1);
defined('ABSPATH') || die();

// prevent fatal error if clone of plugin is activated
// remember: no function or class declarations in this file
if (defined('SUGGEST_404_LINKS_PLUGINBASENAME')) {
    return;
}


// constants
// phpcs:disable PSR1.Files.SideEffects -- constants
define('SUGGEST_404_LINKS_PLUGINBASENAME', plugin_basename(__FILE__));
define('SUGGEST_404_LINKS_DIR', __DIR__);
define('SUGGEST_404_LINKS_VERSION', '0.3.1'); // plugin version


// load & start plugin
require __DIR__ . '/src/Core/Bootstrap.php';
Tekod\Suggest404Links\Core\Bootstrap::init();
