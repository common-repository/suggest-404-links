<?php
/**
 * Widget template for single item in list.
 *
 * This template can be overridden by copying it to /yourtheme/templates/suggest_404_links/public/suggest404links-widget-item.php.
 */

declare(strict_types=1);
defined('ABSPATH') || die();
// phpcs:disable Squiz.WhiteSpace.ControlStructureSpacing

// silencing sniffer
$vars = $vars ?? [];
$link = $vars['Link'];

// prepare template vars
$post = get_post($link['id']);
$url = get_permalink($post);
$title = $post->post_title ?: '#' . $post->ID;
$score = round($link['score'], 2);

// you can render links in more grayish color to represent that it is less likely what user wanted
$colorClass = $score === 0 ? '0' : ($score < 3 ? '1' : ($score < 6 ? '2' : '3'));

// image
$image = get_post_thumbnail_id($post);
$image = $image ? wp_get_attachment_image_src($image) : false;
$image = $image ? $image[0] : plugins_url('assets/images/page.png', dirname(__FILE__, 2));

?>

        <div class="suggest_404_links-widget-item link-color-<?php echo esc_attr($colorClass); ?>" data-score="<?php echo esc_attr($score); ?>">
            <img src="<?php echo esc_url($image); ?>" alt="icon">
            <a href="<?php echo esc_url($url); ?>">
                <?php echo esc_html($title); ?>
            </a>
            <span><?php echo esc_url($url); ?></span>
        </div>
