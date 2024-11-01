<?php

declare(strict_types=1);
defined('ABSPATH') || die();
// phpcs:disable Squiz.WhiteSpace.ControlStructureSpacing

// prepare variables
$action = 'suggest_404_links_save';
$currSettings = suggest_404_links()->config()->getSettings();

$allPostTypes = [];
foreach (get_post_types(['public' => true], 'objects') as $key => $postType) {
    $allPostTypes[$key] = $postType->labels->name;
};


?>
<div class="suggest_404_links wrap">

    <h1>Suggest 404 links</h1>


    <div style="background:#f8f8f8; border:1px solid #ccc; padding:1em; maring-top:2em;">
        <p>
            Plugin "suggest-404-links" will show block of "similar" links in your 404 page, helping your visitors to quickly find page they are looking for.
        </p>
        <p>
            It is quite common to make typing error (typo) while entering URL manually in browser, but showing only "404" information is not really helpful.
            <br>This plugin will find the closest existing URLs, sort them and show list of 5 the most similar links.
        </p>
        <p>
            In order to include this feature to your 404 page you have to add shortcode (or block in gutenberg editor) to your 404 page template.
            Check documentation for more detailed instructions.
        </p>
    </div>

    <h3 style="margin-top: 3em;;">Settings</h3>

    <form action="admin-post.php" method="post">
        <h4 style="margin:2em 0 0 0">Suggest links to these post-types:</h4>
        <ul style="margin: 6px 0 0 1em">
            <?php foreach($allPostTypes as $key => $name) { ?>
            <li>
                <input type="checkbox"
                       name="s404l_types[]"
                       id="s404l_<?php echo esc_attr($key);?>"
                       value="<?php echo esc_attr($key);?>"
                    <?php checked(in_array($key, $currSettings['SelectedPostTypes']));?>
                />
                <label for="s404l_<?php echo esc_attr($key);?>">
                    <?php echo esc_html($name);?>
                </label>
            </li>
            <?php } ?>
        </ul>

        <h4 style="margin:2em 0 6px 0">Auto-redirect visitor on near miss</h4>
        <div style="padding-left:1em;">
            <input type="checkbox"
                   name="s404l_autoredirect"
                   id="s404l_autoredirect"
                   value="1"
                <?php checked($currSettings['SelectedAutoRedirect']);?>
            />
            <label for="s404l_autoredirect">
                <span>Immediately redirect visitor to link that is very close to current URL</span>
            </label>
        </div>

        <?php submit_button(); ?>
        <input type="hidden" name="action" value="<?php echo esc_attr($action); ?>">
        <?php wp_nonce_field($action, 'nonce', false); ?>
        <?php wp_referer_field(); ?>
    </form>


</div>