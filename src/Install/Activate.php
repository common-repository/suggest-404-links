<?php

declare(strict_types=1);
namespace Tekod\Suggest404Links\Install;


/**
 * Class Activate
 */
class Activate
{

    public static function init()
    {
        flush_rewrite_rules();
    }

}
