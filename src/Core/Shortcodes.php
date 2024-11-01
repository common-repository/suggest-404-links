<?php

declare(strict_types=1);
namespace Tekod\Suggest404Links\Core;


/**
 * Class Shortcodes.
 */
class Shortcodes
{

    /**
     * Init shortcodes available for the plugin
     */
    public static function init()
    {
        // register shortcodes
        add_shortcode('suggest_404_links', [__CLASS__, 'renderSuggest404LinksWidget']);
    }


    /**
     * Render "suggest_404_links" widget content.
     *
     * @param string|array $params  array of parameters for the shortcode
     * @param string|null $content  shortcode content (usually empty)
     * @return string  HTML
     */
    public static function renderSuggest404LinksWidget($params, ?string $content = null): string // phpcs:ignore Inpsyde.CodeQuality.ArgumentTypeDeclaration -- mixed by WP
    {
        $setting = suggest_404_links()->config()->getSettings();
        $model = suggest_404_links()->model();
        $path = $model->getUrl('path');
        $links = $model->search($path);

        // calculate auto-redirect
        $threshold = ceil(strlen($path) / 10);  // 10% of characters
        $autoRedirect = !empty($links) && $links[0]['score'] < $threshold && $setting['SelectedAutoRedirect']
            ? get_permalink($links[0]['id'])
            : null;

        // pack template params
        $additionalParams = [
            'Links'    => $links,
            'AutoRedirect' => $autoRedirect,
        ];

        // render template and return html
        $template = empty($links)
            ? 'public/suggest404links-widget-no-links.php'
            : 'public/suggest404links-widget.php';
        return suggest_404_links()->frontend()->returnTemplate($template, shortcode_atts($additionalParams, $params));
    }

}
