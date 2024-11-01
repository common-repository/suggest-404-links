<?php
/**
 * Global functions declared by this plugin.
 */

declare(strict_types=1);
// phpcs:disable NeutronStandard.Globals.DisallowGlobalFunctions

/**
 * Register function that expose plugin interface.
 */
function suggest_404_links(): Tekod\Suggest404Links\Core\ServiceContainer
{
    return Tekod\Suggest404Links\Core\ServiceContainer::getInstance();
}
