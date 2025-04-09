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
 * Class WikiCategoryCMS
 *
 * This concrete class extends the native Category class of the CMS and
 * allows using the centralized category management features.
 */
class WikiCategoryCMS extends Category {
    /**
     * Constructor for WikiCategoryCMS.
     *
     * Here we define the plugin-specific properties for the Wiki.
     *
     * @param int $id Category ID, use -1 for a new instance.
     */
    public function __construct(int $id = -1) {
        // Set the plugin ID that uses the CMS category management
        $this->pluginId = 'wiki';
        // Use 'categories' as the filename
        $this->name = 'categories';
        // Enable hierarchical categories
        $this->nested = true;
        // Allow only a single selection
        $this->chooseMany = false;

        // Call the parent constructor which handles data loading if $id != -1
        parent::__construct($id);
    }

    /**
     * Public getter for pluginId.
     *
     * @return string Returns the plugin ID.
     */
    public function getPluginId(): string {
        return $this->pluginId;
    }

    /**
     * Public getter for name.
     *
     * @return string Returns the name.
     */
    public function getName(): string {
        return $this->name;
    }
}
