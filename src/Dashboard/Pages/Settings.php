<?php

declare(strict_types=1);
namespace Tekod\Suggest404Links\Dashboard\Pages;

use Tekod\Suggest404Links\Dashboard\AbstractPage;


/**
 * Example of classic admin page,
 * showing how to specify it location in admin menu, how to handle submits, how to store values in configuration and display notice.
 */
class Settings extends AbstractPage
{

    // details about menu item
    protected $menuParent = 'options';

    protected $menuTitle = 'Suggest 404 Links';

    protected $pageTitle = 'Suggest 404 Links';

    // template to be rendered
    protected $pageTemplate = 'settings';

    // list of action handlers (actions must be unique and prefixed with short plugin name)
    protected $actionHandlers = [
        'suggest_404_links_save' => 'onActionSave',
    ];


    /**
     * Handle saving settings.
     */
    public function onActionSave()
    {
        // get Config service
        $config = suggest_404_links()->config();

        // update settings
        $newSettings = [
            'SelectedPostTypes' => array_filter(array_map('sanitize_text_field', $_POST['s404l_types'] ?? [])),
            'SelectedAutoRedirect' => intval($_POST['s404l_autoredirect'] ?? '') === 1,
        ];
        $config->setSettings($newSettings);

        // store
        $config->saveConfig();

        // confirmation message
        $this->setAdminNotice(true, 'Saved.');
    }

}
