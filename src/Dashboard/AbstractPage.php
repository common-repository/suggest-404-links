<?php

declare(strict_types=1);
namespace Tekod\Suggest404Links\Dashboard;

/**
 * Base class for all admin pages.
 */
class AbstractPage
{

    // specify location of this admin page
    // (set null for main menu item, parent slug for subitem (ex: "demo"), 'tools' or 'options' for "Settings" subpage)
    // (for CPT menu item put something like: "edit.php?post_type=cpt_book")
    protected $menuParent = 'options';

    // specify title of menu item
    protected $menuTitle = 'Suggest 404 links';

    // dunno what is this :)
    protected $pageTitle = 'Suggest 404 links settings';

    // required admin permitions toa access this page
    protected $pageCapability = 'manage_options';

    // slug of this page, left empty for auto-calculate
    protected $pageSlug = null;

    // icon for main menu item
    protected $menuIconUrl = 'dashicons-chart-bar';

    // position in menu list
    protected $menuPagePosition = null;

    // template to be rendered
    protected $pageTemplate = 'settings';

    // list of action handlers (actions must be unique and prefixed with short plugin name)
    protected $actionHandlers = [
        //'suggest_404_links_save' => 'onActionSave'
    ];

    // internal properties
    protected $dashboard;

    protected $pageId;


    /**
     * Initialization.
     *
     * @param Dashboard $dashboard
     */
    public static function init(Dashboard $dashboard)
    {
        // instantiate object
        new static($dashboard);
    }


    /**
     * AbstractPage constructor.
     *
     * @param Dashboard $dashboard
     */
    public function __construct(Dashboard $dashboard)
    {
        // keep connection to dashboard
        $this->dashboard = $dashboard;

        // calculate page slug
        if ($this->pageSlug === null) {
            $this->pageSlug = sanitize_title_with_dashes($this->menuTitle);
        }

        // setup hooks
        $this->hooks();
    }


    /**
     * Setup action and filter hooks.
     * Note: this will be executed for each admin page, on every request, to set hooks for current page only extend OnLoad method.
     */
    protected function hooks()
    {
        // set basic hooks
        add_action('admin_menu', [$this, 'onAdminMenu']);

        // register action handlers
        foreach ($this->actionHandlers as $action => $method) {
            add_action("admin_post_$action", [$this, 'ActionHandler']);
        }
    }


    /**
     * Register admin menu or submenu page.
     */
    public function onAdminMenu()
    {
        // shortening names
        [$pTitle, $mTitle, $cap, $slug, $fun, $ico, $pos] = [
            $this->pageTitle,
            $this->menuTitle,
            $this->pageCapability,
            $this->pageSlug,
            [$this, 'renderPage'],
            $this->menuIconUrl,
            $this->menuPagePosition,
        ];

        // register admin page
        if ($this->menuParent === 'options') {
            // create sub-"Settings" page
            $this->pageId = add_options_page($pTitle, $mTitle, $cap, $slug, $fun, $pos);
        } elseif ($this->menuParent === 'tools') {
            // create sub-"Tools" page
            $this->pageId = add_management_page($pTitle, $mTitle, $cap, $slug, $fun, $pos);
        } elseif (!$this->menuParent) {
            // create root page
            $this->pageId = add_menu_page($pTitle, $mTitle, $cap, $slug, $fun, $ico, $pos);
        } else {
            // create sub-page
            $this->pageId = add_submenu_page($this->menuParent, $pTitle, $mTitle, $cap, $slug, $fun, $pos);
        }

        // hook on loading that page
        add_action("load-$this->pageId", [$this, 'onLoad']);
    }


    /**
     * Perform tasks when admin page is actually loaded.
     * Only one page can be loaded.
     */
    public function onLoad()
    {
        // enqueue js and css files
        $this->includeAssets();

        // admin notices
        $this->dashboard->prepareAdminNotices();

        // set hooks for admin footer content
        add_filter('admin_footer_text', [$this, 'pluginFooterLeft']);
        add_filter('update_footer', [$this, 'pluginFooterRight'], 11);

        // set hook to include localizator
        add_action('admin_enqueue_scripts', [$this, 'enqueueLocalizer']);
    }


    /**
     * Echo dashboard content.
     */
    public function renderPage()
    {
        // validate access
        if (!$this->validateAccess()) {
            wp_die(esc_html__('You do not have sufficient permissions to access this page.', 'suggest-404-links'));
        }

        // render admin page
        suggest_404_links()->template("admin/$this->pageTemplate.php", $this->preparePageData());
    }


    /**
     * Setup data that have to be pushed to page template.
     */
    protected function preparePageData(): array
    {
        return [];
    }


    /**
     * Enqueue javascript and css files.
     */
    protected function includeAssets()
    {
        //suggest_404_links()->frontend()->enqueueAsset('admin/admin-scripts.js', [],true);
        suggest_404_links()->frontend()->enqueueAsset('admin/admin-style.css');
    }


    /**
     * Enqueue localizator.
     */
    public function enqueueLocalizer()
    {
        $data = [
            // URL for REST endpoints
            'apiUrl' => home_url('/wp-json'),

            // URL for plugin assets (scripts, images, ...)
            'assetsUrl' => home_url('/wp-content/plugins/suggest-404-links/assets'),

            // wp nonce for securing form submissions
            'nonce' => wp_create_nonce('wp_rest'),
        ];

        $lastEnqueuedScript = end(wp_scripts()->queue);
        wp_localize_script($lastEnqueuedScript, 'suggest_404_links_config', $data);
    }


    /**
     * Listener of all actions.
     * It will perform some checks and call actual handler.
     */
    public function actionHandler()
    {
        // validate access
        if (!$this->validateAccess()) {
            wp_die(esc_html__('You do not have sufficient permissions to access this page.', 'suggest-404-links'));
        }

        // fetch current action and remove "admin_post_" prefix
        $action = substr(current_filter(), 11);
        $method = $this->actionHandlers[$action];

        // ensure action exist
        if (!$method || !method_exists($this, $method)) {
            wp_die(esc_html('Unknown action "' . $action . '".'));
        }

        // validate action and execute dynamic method
        if ($this->validateSubmit($action)) {
            // update settings (collect values form $_POST and send them to Config::setSettings and Config::saveConfig)
            $this->$method();
        }

        // redirect to viewing context
        $redirect = wp_get_referer();
        if ($redirect) {
            wp_safe_redirect($redirect);
            die();
        }
    }


    /**
     * Check is submit request valid.
     * Descendant classes can modify this validation.
     *
     * @param string $action
     * @return bool
     */
    protected function validateSubmit(string $action): bool
    {
        if (!wp_verify_nonce(sanitize_text_field($_POST['nonce'] ?? ''), $action)) {
            $this->setAdminNotice(false, 'Session expired, please try again.');
            return false;
        }
        if (!isset($_POST['_wp_http_referer'])) {
            $this->setAdminNotice(false, 'Missing target.');
            return false;
        }
        return true;
    }


    /**
     * Check whether current user can access to this page.
     *
     * @return bool
     */
    protected function validateAccess(): bool
    {
        return current_user_can($this->pageCapability);
    }


    /**
     * Insert custom content at bottom-left of admin page.
     *
     * @param string|null $default
     * @return string|null
     */
    public function pluginFooterLeft(?string $default): ?string
    {
        return get_current_screen()->id === $this->pageId
            ? $this->dashboard->renderFooterLeft()
            : $default;
    }


    /**
     * Insert custom content at bottom-right of admin page.
     *
     * @param string|null $default
     * @return string|null
     */
    public function pluginFooterRight(?string $default): ?string
    {
        return get_current_screen()->id === $this->pageId
            ? $this->dashboard->renderFooterRight()
            : $default;
    }


    /**
     * Set admin notice message.
     * Specify $Type as boolean for simple confirmation/error style of message or as string to customize message class.
     *
     * @param string|bool $success
     * @param string      $message
     */
    protected function setAdminNotice($success, string $message) // phpcs:ignore Inpsyde.CodeQuality.ArgumentTypeDeclaration -- mixed
    {
        $this->dashboard->setAdminNotice($success, $message);
    }

}
