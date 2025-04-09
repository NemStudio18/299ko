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

// Prevent direct access to the script by verifying that ROOT is defined
defined('ROOT') or exit('Access denied!');

// Include the concrete class that extends the core Category
require_once PLUGINS . 'wiki' . DS . 'entities' . DS . 'WikiCategoryCMS.php';

/**
 * Class WikiCategoryManager
 *
 * Adapts the management of the Wiki plugin categories to use the native core
 * via the WikiCategoryCMS class.
 */
class WikiCategoryManager {
    // Singleton instance
    protected static $instance;

    /**
     * Private constructor for singleton pattern.
     */
    private function __construct() {
        // No specific initialization required
    }

    /**
     * Returns the unique instance of WikiCategoryManager.
     *
     * @return WikiCategoryManager
     */
    public static function getInstance() {
        if (!self::$instance) {
            self::$instance = new WikiCategoryManager();
        }
        return self::$instance;
    }

    /**
     * Retrieves all categories as an associative array.
     *
     * @return array List of categories (id, name, parent)
     */
    public function getAllCategories() {
        // Create a dummy object to get pluginId and name via getters
        $dummy = new WikiCategoryCMS();
        $file = DATA_PLUGIN . $dummy->getPluginId() . '/categories-' . $dummy->getName() . '.json';
        $data = util::readJsonFile($file);

        // Ensure that $data is an array
        if (!is_array($data)) {
            $data = [];
        }

        $categories = [];
        // Traverse the categories from the JSON file
        foreach ($data as $id => $catData) {
            $categories[] = [
                'id'     => $id,
                // The core uses 'label' for the displayed name
                'name'   => $catData['label'] ?? '',
                // Use 'parentId' to define the hierarchical relationship
                'parent' => $catData['parentId'] ?? ''
            ];
        }
        return $categories;
    }

    /**
     * Builds the categories tree.
     *
     * @return array Returns the hierarchical tree of categories.
     */
    public function getCategoriesTree() {
        $all = $this->getAllCategories();
        $tree = [];
        $indexed = [];

        // Index categories by their identifier and initialize their children
        foreach ($all as $cat) {
            $cat['children'] = [];
            $indexed[$cat['id']] = $cat;
        }

        // Build the tree structure
        foreach ($indexed as $id => &$cat) {
            if (!empty($cat['parent']) && isset($indexed[$cat['parent']])) {
                $indexed[$cat['parent']]['children'][] = &$cat;
            } else {
                $tree[] = &$cat;
            }
        }
        return $tree;
    }

    /**
     * Retrieves a category by its identifier.
     *
     * @param mixed $id Category identifier.
     * @return array|null Returns the category data or null if not found.
     */
    public function getCategory($id) {
        $all = $this->getAllCategories();
        foreach ($all as $cat) {
            if ($cat['id'] == $id) {
                return $cat;
            }
        }
        return null;
    }

    /**
     * Saves a category using the native system.
     *
     * @param array $data Category data (id, name, parent)
     */
    public function saveCategory($data) {
        $dummy = new WikiCategoryCMS();
        $file = DATA_PLUGIN . $dummy->getPluginId() . '/categories-' . $dummy->getName() . '.json';
        $categories = util::readJsonFile($file);
        if (!is_array($categories)) {
            $categories = [];
        }

        // If 'id' is empty, generate a new one using uniqid()
        $id = !empty($data['id']) ? $data['id'] : uniqid();

        $categories[$id] = [
            // Store the name under 'label'
            'label'      => $data['name'],
            // The parent is stored under 'parentId'
            'parentId'   => $data['parent'] ?? '',
            // Preserve any existing data
            'items'      => $categories[$id]['items'] ?? [],
            'childrenId' => $categories[$id]['childrenId'] ?? [],
            'pluginArgs' => $categories[$id]['pluginArgs'] ?? []
        ];
        file_put_contents($file, json_encode($categories, JSON_PRETTY_PRINT));
    }

    /**
     * Deletes a category by removing its key from the native file.
     *
     * @param mixed $id Identifier of the category to delete.
     */
    public function deleteCategory($id) {
        $dummy = new WikiCategoryCMS();
        $file = DATA_PLUGIN . $dummy->getPluginId() . '/categories-' . $dummy->getName() . '.json';
        $categories = util::readJsonFile($file);

        if (!is_array($categories)) {
            $categories = [];
        }

        if (isset($categories[$id])) {
            unset($categories[$id]);
            file_put_contents($file, json_encode($categories, JSON_PRETTY_PRINT));
        }
    }
}
