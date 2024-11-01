<?php

declare(strict_types=1);
namespace Tekod\Suggest404Links\Services;


/**
 * Class Frontend
 */
class Frontend
{

    /**
     * Singleton getter.
     *
     * @return static
     */
    public static function getInstance(): self
    {
        static $instance;
        if (!$instance) {
            $instance = new self();
        }
        return $instance;
    }


    /**
     * Enqueue asset file.
     *
     * @param string $file
     * @param array  $deps
     * @param array  $args  ['strategy'=>'defer', 'in_footer'=>true]
     */
    public function enqueueAsset(string $file, array $deps = [], array $args = [])
    {
        $handle = "suggest_404_links-$file";
        $ext = strtolower(array_reverse(explode('.', $file))[0]);
        $pluginURL = untrailingslashit(plugin_dir_url(SUGGEST_404_LINKS_DIR . '/.'));

        if ($ext === 'js') {
            wp_enqueue_script($handle, "$pluginURL/assets/$file", $deps, SUGGEST_404_LINKS_VERSION, $args);
        } else {
            wp_enqueue_style($handle, "$pluginURL/assets/$file", $deps, SUGGEST_404_LINKS_VERSION);
        }
    }


    /**
     * Find template to load.
     * It will try to locate "overriding" template in 3 possible folders and fallback to plugin on fail.
     *
     * @param string $path
     * @return string
     */
    public function locateTemplate(string $path): string
    {

        // search for overridden templates
        $located = locate_template(array_filter([

            // search first in "<theme>/woocommerce/." (if we have woocommerce installed)
            function_exists('WC') ? WC()->template_path() . $path : null,

            // search in "<theme>/templates/suggest_404_links/."
            'templates/suggest_404_links/' . $path,

            // search in "<theme>/templates/."
            'templates/' . $path,
        ]));

        // no problem, get it from plugin
        $pluginPath = SUGGEST_404_LINKS_DIR . '/templates/' . $path;
        if (!$located && file_exists($pluginPath)) {
            return $pluginPath;
        }

        // ret
        return $located;
    }


    /**
     * Load and echo template.
     *
     * @param string $path
     * @param array  $vars
     */
    public function template(string $path, array $vars = []): void
    {
        // get template location
        $path = $this->locateTemplate($path);
        $path = apply_filters("suggest_404_links_template_$path", $path, $vars);
        if (!$path) {
            return;
        }

        // load file
        include $path;
    }


    /**
     * Load and return template content.
     *
     * @param string $path
     * @param array  $vars
     * @return string
     */
    public function returnTemplate(string $path, array $vars = []): string
    {
        ob_start();
        $this->template($path, $vars);
        return apply_filters("suggest_404_links_render_template_$path", ob_get_clean(), $vars);
    }

}
