<?php

declare(strict_types=1);
namespace Tekod\Suggest404Links\Core;

use Tekod\Suggest404Links\Models\Model;
use Tekod\Suggest404Links\Services\Config;
use Tekod\Suggest404Links\Services\Frontend;


/**
 * Class ServiceContainer.
 * This is plugin service locator (servicer), it will be exposed via suggest_404_links() global function.
 */
class ServiceContainer
{

    // validation check result
    protected $requirementsMet = false;


    /**
     * Singleton getter.
     *
     * @return ServiceContainer
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
     * Initialize.
     *
     * @param array  list of unsatisfied requirements
     */
    public function init(array $requirements)
    {
        // save value
        $this->requirementsMet = $requirements;

        // register background services if all requirements for this plugin are met
        if (empty($requirements)) {
            Hooks::init();
        }

        // load frontend to trigger hooks
        Frontend::getInstance();
    }


    /**
     * Return validation check result.
     *
     * @return bool;
     */
    public function requirementsMet(): bool
    {
        return $this->requirementsMet;
    }


    /**
     * Public getter of data model.
     *
     * @return Model
     */
    public function model(): Model
    {
        // replace with real model class
        return Model::getInstance();
    }


    /**
     * Public getter of config registry.
     *
     * @return Config
     */
    public function config(): Config
    {
        return Config::getInstance();
    }


    /**
     * Public getter of Frontend service.
     *
     * @return Frontend
     */
    public function frontend(): Frontend
    {
        return Frontend::getInstance();
    }


    /**
     * Shorthand method for displaying template.
     *
     * @param string $path
     * @param array|null   $vars
     */
    public function template(string $path, ?array $vars = null)
    {
        $this->frontend()->template($path, $vars);
    }

}
