<?php
/**
 * Widget template for "no results" situation.
 *
 * This template can be overridden by copying it to /yourtheme/templates/suggest_404_links/public/suggest404links-widget-no-links.php.
 */

declare(strict_types=1);
defined('ABSPATH') || die();
// phpcs:disable Squiz.WhiteSpace.ControlStructureSpacing

// silencing sniffer
$vars = $vars ?? [];

// place "return;" here to completely avoid any HTML in "no results" situation
?>

<div class="suggest_404_links-widget suggest_404_links-widget-no-links">
    <div>
        Sorry, no suggestions available.
        <!-- Idea: maybe show input text field and submit to "google.com" with suffix " site:mydomain.com" -->
    </div>
</div>
