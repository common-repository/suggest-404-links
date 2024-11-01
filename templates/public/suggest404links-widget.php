<?php

/**
 * Widget template.
 *
 * This template can be overridden by copying it to /yourtheme/templates/suggest_404_links/public/suggest404links-widget.php.
 */

declare(strict_types=1);
defined('ABSPATH') || die();
// phpcs:disable Squiz.WhiteSpace.ControlStructureSpacing

// silencing sniffer
$vars = $vars ?? [];
$links = $vars['Links'] ?? [];
$autoRedirect = $vars['AutoRedirect'] ?? null;

// redirect user
if ($autoRedirect) {
    // reimplement "wp_safe_redirect()" but with javascript, that allows redirecting after headers was sent
    $location = wp_sanitize_redirect($autoRedirect);
    $fallback_url = apply_filters('wp_safe_redirect_fallback', admin_url(), 302);
    $location = wp_validate_redirect($location, $fallback_url);
    echo '<script>document.location.href = "' . esc_url($location) . '";</script>';
}

?>

<div class="suggest_404_links-widget">

    <div>
        <h2>Check these similar pages:</h2>
        <ul>
            <?php foreach ($links as $link) { ?>
            <li>
                <?php suggest_404_links()->template('public/suggest404links-widget-item.php', $vars + ['Link' => $link]); ?>
            </li>
            <?php } ?>
        </ul>
    </div>

</div>
