<?php

declare(strict_types=1);
namespace Tekod\Suggest404Links\Core;


/**
 * Class Hooks
 */
class Hooks
{

    /**
     * Initialize.
     */
    public static function init()
    {
        add_action('wp_head', [__CLASS__, 'onInit']);
    }


    /**
     * Perform some tasks on "init" wp hook.
     */
    public static function onInit()
    {
        // enqueue frontend javascript and css files
        if (is_404()) {
            $frontend = suggest_404_links()->frontend();
            $frontend->enqueueAsset('public/style.css');
            //$frontend->enqueueAsset('public/scripts.js');
        }
    }

}
