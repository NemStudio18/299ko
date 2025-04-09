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

// Include the WikiCategoryManager class for managing wiki categories
require_once PLUGINS . 'wiki' . DS . 'entities' . DS . 'WikiCategoryManager.php';

/**
 * Class AdminWikiCategoriesController
 *
 * Manages the administration actions for wiki categories.
 */
class AdminWikiCategoriesController extends AdminController {
    // Instance of the category manager
    protected $categoryManager;

    /**
     * Constructor.
     *
     * Initializes the category manager using the singleton instance.
     */
    public function __construct() {
        $this->categoryManager = WikiCategoryManager::getInstance();
    }

    /**
     * Displays the categories tree in the admin panel.
     *
     * Retrieves the hierarchical tree of categories and passes it, along with other
     * required data, to the admin template.
     *
     * @return AdminResponse Returns the admin response with the categories template.
     */
    public function index() {
        // Retrieve the hierarchical categories tree
        $categoriesTree = $this->categoryManager->getCategoriesTree();
        // Create a new admin response and load the corresponding plugin template
        $response = new AdminResponse();
        $tpl = $response->createPluginTemplate('wiki', 'admin/wiki-categories');
        // Set the page title (translated as "Category Management")
        $response->setTitle("Category Management");
        // Pass the categories tree to the template
        $tpl->set('categoriesTree', $categoriesTree);
        // Set the admin menu in the template
        $tpl->set('adminMenu', AdminWikiController::getAdminMenu());
        // Pass the router instance for URL management
        $tpl->set('router', ROUTER::getInstance());
        $response->addTemplate($tpl);
        return $response;
    }

    /**
     * Displays the category edit form for creating a new category or editing an existing one.
     *
     * Retrieves the current category data if editing, or prepares default data for a new category.
     *
     * @return AdminResponse Returns the admin response with the category edit template.
     */
    public function edit() {
        // Retrieve the category ID from the GET parameter 'id'
        $catId = $_GET['id'] ?? '';
        // If an ID is provided, get the category data; otherwise, set to null for a new category
        $category = $catId ? $this->categoryManager->getCategory($catId) : null;
        // Create a new admin response and load the corresponding plugin template
        $response = new AdminResponse();
        $tpl = $response->createPluginTemplate('wiki', 'admin/wiki-categories-edit');
        // Set the title based on whether the category is being edited or a new one is being created
        $response->setTitle($catId ? "Edit Category" : "New Category");
        // Pass the category data to the template
        $tpl->set('category', $category);
        // Pass a list of all categories to the template (useful for parent selection)
        $tpl->set('allCategories', $this->categoryManager->getAllCategories());
        // Set the admin menu in the template
        $tpl->set('adminMenu', AdminWikiController::getAdminMenu());
        // Pass the router instance for URL management
        $tpl->set('router', ROUTER::getInstance());
        $response->addTemplate($tpl);
        return $response;
    }

    /**
     * Saves the category data.
     *
     * Retrieves data from the POST request, saves the category using the manager,
     * and then redirects back to the categories list.
     */
    public function save() {
        // Retrieve the form data sent via POST
        $data = $_POST;
        // Save or update the category using the category manager
        $this->categoryManager->saveCategory($data);
        // Redirect to the categories list page after saving
        header("Location: " . ROUTER::getInstance()->generate('admin-wiki-categories'));
        exit;
    }

    /**
     * Deletes a category.
     *
     * Retrieves the category ID from GET, deletes the category if the ID exists,
     * and redirects back to the categories list.
     */
    public function delete() {
        // Retrieve the category ID from the GET parameter 'id'
        $catId = $_GET['id'] ?? '';
        // Delete the category if an ID is provided
        if ($catId) {
            $this->categoryManager->deleteCategory($catId);
        }
        // Redirect to the categories list page after deletion
        header("Location: " . ROUTER::getInstance()->generate('admin-wiki-categories'));
        exit;
    }
}
