=== Suggest 404 links ===
Contributors: tekod
Tags: did you mean, suggestions, similar, 404
Requires at least: 6.2
Tested up to: 6.6
Stable tag: 0.3.1
Requires PHP: 7.2
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html

Appends "did you mean" links to your "404" page.

== Description ==
Purpose of 404 page is to inform visitor that WordPress cannot resolve current URL,
but leaves the visitor wondering why.
Whether the page was deleted or simply the link was not typed correctly.

This plugin analyzes what is entered in the URL, compares it with all existing urls on the site, finds the closest ones and offers the visitor the 5 closest ones.
Something like "did you mean...".

==Usage==
The plugin supports both classic and block themes.

For classic themes, you need to add shortcode to the template of your 404 page:
     <?php echo do_shortcode('[suggest_404_links]'); ?>

For block themes, you will get a new Gutenberg block "404 page: similar links" that you simply insert into your template.

Finally, the plugin allows you to override template to completely customize resulting HTML,
and a few filter hooks to modify its logic.

==Contact==
    
Please, send bug reports and feature requests to <a href="mailto:office@tekod.com">office@tekod.com</a>

