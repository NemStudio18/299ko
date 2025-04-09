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

// Prevent direct access to this script by checking if ROOT is defined
defined('ROOT') or exit('Access denied!');

// Include the WikiPageManager and WikiCategoryManager classes for managing wiki pages and categories
require_once PLUGINS . 'wiki' . DS . 'entities' . DS . 'WikiPageManager.php';
require_once PLUGINS . 'wiki' . DS . 'entities' . DS . 'WikiCategoryManager.php';

/**
 * Class WikiPageController
 *
 * Handles the public display of wiki pages.
 */
class WikiPageController {
    // Instance of the page manager for retrieving wiki pages
    protected $pageManager;
    // Instance of the category manager for retrieving wiki categories
    protected $categoryManager;

    /**
     * Constructor.
     *
     * Initializes the WikiPageManager and WikiCategoryManager instances.
     */
    public function __construct() {
        $this->pageManager = new WikiPageManager(DATA_PLUGIN . 'wiki' . DS . 'pages' . DS);
        $this->categoryManager = WikiCategoryManager::getInstance();
    }

    /**
     * Displays the wiki page or list of pages.
     *
     * If a page identifier is provided, retrieves and displays the page; otherwise,
     * displays a list of pages filtered by category, search query, or the full tree.
     *
     * @return PublicResponse Returns the public response with the corresponding template.
     */
    public function view() {
        // Retrieve the router instance
        $router = ROUTER::getInstance();
        // Get the page ID, category ID and search query from GET parameters
        $pageId = $_GET['page'] ?? '';
        $catId  = $_GET['cat'] ?? '';
        $q      = isset($_GET['q']) ? trim($_GET['q']) : '';

        // Build the navigation menu
        $navMenu = $this->buildNavigationMenu();

        // Define a base URL that always ends with a slash
        $baseUrl = rtrim($router->generate('wiki-view'), '/') . '/';

        // If a page ID is provided, display the specific page
        if ($pageId) {
            $page = $this->pageManager->getPage($pageId);
            // If the page is not found, stop execution with an error message
            if (!$page) {
                die("Page not found.");
            }
            // If the page is marked as a draft, stop execution with an error message
            if (isset($page['draft']) && ($page['draft'] === true || $page['draft'] == '1')) {
                die("Unpublished page.");
            }
            // Retrieve the content of the page
            $content = $page['content'];
            // Create a new public response and load the wiki view template
            $response = new PublicResponse();
            $tpl = $response->createPluginTemplate('wiki', 'public/wiki-view');
            // Set the page title
            $response->setTitle($page['title']);
            // Pass the page title and content to the template
            $tpl->set('pageTitle', $page['title']);
            $tpl->set('content', $content);
            // Pass the navigation menu, router, and base URL to the template
            $tpl->set('menu', $navMenu);
            $tpl->set('router', $router);
            $tpl->set('baseUrl', $baseUrl);
            $response->addTemplate($tpl);
            return $response;
        } else {
            // If no page ID is provided, determine the pages to display based on category or search query
            if ($catId) {
                // Retrieve pages filtered by the category ID
                $pages = $this->pageManager->getPagesByCategory($catId);
            } elseif ($q) {
                // Retrieve pages based on the search query
                $pages = $this->pageManager->searchPages($q);
            } else {
                // Retrieve the full pages tree
                $pages = $this->pageManager->getPagesTree();
            }
            // Create a new public response and load the wiki list template
            $response = new PublicResponse();
            $tpl = $response->createPluginTemplate('wiki', 'public/wiki-list');
            // Set the page title to "Wiki"
            $response->setTitle("Wiki");
            // Pass the list of pages, navigation menu, router, search query, and base URL to the template
            $tpl->set('pages', $pages);
            $tpl->set('menu', $navMenu);
            $tpl->set('router', $router);
            $tpl->set('searchQuery', $q);
            $tpl->set('baseUrl', $baseUrl);
            $response->addTemplate($tpl);
            return $response;
        }
    }

    /**
     * Builds the navigation menu based on categories and orphan pages.
     *
     * Retrieves the categories tree, flattens the pages for each category,
     * and generates an HTML unordered list with links.
     *
     * @return string Returns the HTML for the navigation menu.
     */
    protected function buildNavigationMenu() {
        // Retrieve the hierarchical tree of categories
        $categoriesTree = $this->categoryManager->getCategoriesTree();
        // For each category, retrieve and flatten its pages
        foreach ($categoriesTree as &$cat) {
            $pages = $this->pageManager->getPagesByCategory($cat['id']);
            $cat['pages'] = $this->flattenPagesTree($pages);
        }
        unset($cat);

        // Retrieve pages that are not assigned to any category
        $orphanPages = $this->pageManager->getOrphanPages();
        $router = ROUTER::getInstance();
        $baseUrl = rtrim($router->generate('wiki-view'), '/') . '/';

        // Start building the navigation menu HTML
        $html = '<ul>';
        // Render the menu for each category
        foreach ($categoriesTree as $cat) {
            $html .= $this->renderCategoryMenu($cat, $baseUrl);
        }
        // If orphan pages exist, add them to the menu
        if (!empty($orphanPages)) {
            $html .= '<li><strong>Pages</strong><ul>';
            foreach ($orphanPages as $page) {
                $html .= '<li><a href="' . $baseUrl . '?page=' . $page['filename'] . '">' . $page['title'] . '</a></li>';
            }
            $html .= '</ul></li>';
        }
        $html .= '</ul>';
        return $html;
    }

    /**
     * Flattens a hierarchical pages tree into a simple array.
     *
     * This method recursively traverses the pages and returns a flat array of pages.
     *
     * @param array $pages The hierarchical array of pages.
     * @return array Returns a flat array of pages.
     */
    private function flattenPagesTree($pages) {
        $flat = [];
        foreach ($pages as $page) {
            // Add the current page to the flat array
            $flat[] = $page;
            // If the page has children, recursively merge them into the flat array
            if (!empty($page['children'])) {
                $flat = array_merge($flat, $this->flattenPagesTree($page['children']));
            }
        }
        return $flat;
    }

    /**
     * Renders a category menu item, including its pages and subcategories.
     *
     * Generates an HTML list item for a category with links to its pages and recursively
     * renders any subcategories.
     *
     * @param array $cat The category data.
     * @param string $baseUrl The base URL for links.
     * @return string Returns the HTML for the category menu item.
     */
    protected function renderCategoryMenu($cat, $baseUrl) {
        // Create a list item for the category with a link
        $html = '<li><a href="' . $baseUrl . '?cat=' . $cat['id'] . '">' . htmlspecialchars($cat['name']) . '</a>';
        // If the category has pages, render them in a nested list
        if (!empty($cat['pages'])) {
            $html .= '<ul>';
            foreach ($cat['pages'] as $page) {
                $html .= '<li><a href="' . $baseUrl . '?page=' . $page['filename'] . '">' . htmlspecialchars($page['title']) . '</a></li>';
            }
            $html .= '</ul>';
        }
        // If the category has subcategories, render them in a nested list recursively
        if (!empty($cat['children'])) {
            $html .= '<ul>';
            foreach ($cat['children'] as $subcat) {
                $html .= $this->renderCategoryMenu($subcat, $baseUrl);
            }
            $html .= '</ul>';
        }
        $html .= '</li>';
        return $html;
    }
}
