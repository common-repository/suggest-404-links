<?php

declare(strict_types=1);
namespace Tekod\Suggest404Links\Core;

use Tekod\Suggest404Links\Dashboard\Dashboard;
use Tekod\Suggest404Links\Install\Activate;
use Tekod\Suggest404Links\Install\Deactivate;


/**
 * Class Bootstrap
 */
class Bootstrap
{
    // list of unmatched requirements
    protected static $requirements = [];


    /**
     * Start all services.
     */
    public static function init()
    {
        // initialize internal autoloader
        static::initAutoloader();

        // plugin activator / de-activator hooks
        static::initActivationHooks();

        // load service-container function in global namespace
        require __DIR__ . '/functions.php';

        // wait all other plugins to load to continue
        add_action('plugins_loaded', [__CLASS__, 'lateInitialization']);

        add_action( 'enqueue_block_assets', function() {
            $w = 4;
            return;
        });
    }


    /**
     * Deferred tasks for plugin initialization.
     * In this point all other plugins are loaded, so we can check for requirements.
     */
    public static function lateInitialization()
    {
        // we don't want to run high-level functionalities during uninstall process
        if (defined('WP_UNINSTALL_PLUGIN')) {
            return;
        }

        // check are requirements met
        static::requirementsCheck();

        // initialize services
        static::initServices();

        // initialize single-side systems
        if (is_admin()) {
            static::initAdminSystems();
        } else {
            static::initPublicSystems();
        }
    }


    /**
     * Initialize internal autoloader.
     */
    protected static function initAutoloader()
    {
        spl_autoload_register([__CLASS__, 'autoloader'], true, false);
    }


    /**
     * Setup plugin activator / de-activator hooks.
     */
    protected static function initActivationHooks()
    {
        register_activation_hook(SUGGEST_404_LINKS_PLUGINBASENAME, [Activate::class, 'init']);
        register_deactivation_hook(SUGGEST_404_LINKS_PLUGINBASENAME, [Deactivate::class, 'init']);
    }


    /**
     * Initialize features that are needed only on admin side.
     */
    protected static function initAdminSystems()
    {
        // load dashboard pages if not deleted
        if (is_dir(__DIR__ . '/../Dashboard')) {
            Dashboard::init();
        }

        // display notice about unmatched requirements
        if (!empty(static::$requirements)) {
            add_action('admin_notices', function() {
                $message = 'Suggest 404 links requirements: ' . nl2br(esc_html(implode("\n", static::$requirements)));
                echo '<div class="error"><p>' . wp_kses_post($message) . '</p></div>';
            });
        }
    }


    /**
     * Initialize features that are needed only on public.
     */
    protected static function initPublicSystems()
    {
        // skip if we are in rest, ajax or cron request
        if (wp_is_json_request() || wp_doing_ajax() || wp_doing_cron()) {
            return;
        }

        // register shortcodes
        Shortcodes::init();
    }


    /**
     * Initialize services.
     */
    protected static function initServices()
    {
        // check requirements
        ServiceContainer::getInstance()->init(static::$requirements);

        // register blocks
        if (function_exists('register_block_type_from_metadata')) {
            add_action('init', [__CLASS__, 'registerBlocks']);
            add_action('enqueue_block_assets', [__CLASS__, 'enqueueEditorBlockAssets']);
        }
    }


    /**
     * Register Gutenberg blocks.
     */
    public static function registerBlocks()
    {
        register_block_type_from_metadata( SUGGEST_404_LINKS_DIR . '/assets/block', [
            'render_callback' => function(array $atts) {
                return Shortcodes::renderSuggest404LinksWidget([]);
            }
        ]);
    }


    public static function enqueueEditorBlockAssets()
    {
        suggest_404_links()->frontend()->enqueueAsset('admin/admin-style.css');
    }


    /**
     * Check are all plugin requirements are meet.
     * It should populate list of error messages about missing requirements.
     *
     * @return array
     */
    protected static function requirementsCheck(): void
    {
        $classes = [
            //'WooCommerce' => 'Missing "Woocommerce" plugin.',
            //'acf_pro' => 'Missing "Advanced Custom Fields PRO" plugin.',
            //'WPCF7'   => 'Missing "Contact Form 7" plugin.',
        ];

        static::$requirements = [];
        foreach ($classes as $class => $msg) {
            if (!class_exists($class)) {
                static::$requirements[] = $msg;
            }
        }
    }


    /**
     * Autoloading handler.
     *
     * @param string $class
     * @return null|boolean
     */
    public static function autoloader(string $class): ?bool
    {
        // using "namespace" pattern to locate file
        $parts = array_filter(explode('\\', $class));
        if ($parts[0] !== 'Tekod' || $parts[1] !== 'Suggest404Links') {
            return null;
        }
        unset($parts[0], $parts[1]);
        $path = __DIR__ . '/../' . implode('/', $parts) . '.php';
        if (!is_file($path)) {
            // not found
            return null;
        }

        // dynamic inclusion
        require $path;

        // success
        return true;
    }

}
