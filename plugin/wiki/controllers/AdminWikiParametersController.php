<?php
/**
 * @copyright (C) 2025, 299Ko
 * @license https://www.gnu.org/licenses/gpl-3.0.en.html GPLv3
 * @author Maxime Blanc <nemstudio18@gmail.com>
 *
 * @package 299Ko https://github.com/299Ko/299ko
 *
 * Wiki Plugin for 299Ko CMS
 *
 * @license https://www.gnu.org/licenses/gpl-3.0.en.html GPLv3
 */

// Prevent direct access to the script by checking if ROOT is defined
defined('ROOT') or exit('Access denied!');

/**
 * Class AdminWikiParametersController
 *
 * Manages the administration settings for the Wiki plugin.
 */
class AdminWikiParametersController extends AdminController {
    // File path to the configuration file for the wiki plugin
    protected $configFile;

    /**
     * Constructor.
     *
     * Sets the path to the config file and creates it with an empty JSON object
     * if it does not already exist.
     */
    public function __construct() {
        // Define the configuration file path for the Wiki plugin
        $this->configFile = DATA_PLUGIN . 'wiki' . DS . 'config.json';
        // Check if the configuration file exists; if not, create it with an empty JSON array
        if (!file_exists($this->configFile)) {
            file_put_contents($this->configFile, json_encode([], JSON_PRETTY_PRINT));
        }
    }

    /**
     * Displays the configuration settings in the admin panel.
     *
     * Reads the configuration file, decodes its JSON content, and passes it along
     * with other required data to the admin template.
     *
     * @return AdminResponse Returns the admin response with the settings template.
     */
    public function index() {
        // Read the config file and decode its JSON content into an array
        $config = json_decode(file_get_contents($this->configFile), true);
        // Create a new admin response and load the corresponding plugin template
        $response = new AdminResponse();
        $tpl = $response->createPluginTemplate('wiki', 'admin/wiki-parameters');
        // Set the page title (e.g., "Wiki Settings")
        $response->setTitle("Wiki Settings");
        // Pass the configuration data to the template
        $tpl->set('config', $config);
        // Add the admin menu to the template
        $tpl->set('adminMenu', AdminWikiController::getAdminMenu());
        // Pass the router instance for managing URLs to the template
        $tpl->set('router', ROUTER::getInstance());
        // Add the template to the response
        $response->addTemplate($tpl);
        return $response;
    }

    /**
     * Saves the updated configuration settings.
     *
     * Reads the existing configuration, merges it with the new settings received
     * from the POST request, saves the updated configuration back to the file,
     * and then redirects back to the settings page.
     */
    public function save() {
        // Retrieve form data sent via POST
        $data = $_POST;
        // Initialize an empty array for the existing configuration
        $existingConfig = [];
        // Check if the configuration file exists and read its content
        if (file_exists($this->configFile)) {
            $existingConfig = json_decode(file_get_contents($this->configFile), true);
            // Ensure that the existing config is an array; if not, reset it
            if (!is_array($existingConfig)) {
                $existingConfig = [];
            }
        }
        // Merge the old configuration with the new POST data
        $newConfig = array_merge($existingConfig, $data);
        // Save the merged configuration back to the file in a pretty-printed JSON format
        file_put_contents($this->configFile, json_encode($newConfig, JSON_PRETTY_PRINT));
        // Redirect to the Wiki settings page
        header("Location: " . ROUTER::getInstance()->generate('admin-wiki-parameters'));
        exit;
    }

}
