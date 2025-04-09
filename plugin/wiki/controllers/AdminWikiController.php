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

// Check if the constant ROOT is defined to prevent direct access to the script
defined('ROOT') or exit('Access denied!');

// Include required classes for managing wiki pages and categories
require_once PLUGINS . 'wiki' . DS . 'entities' . DS . 'WikiPageManager.php';
require_once PLUGINS . 'wiki' . DS . 'entities' . DS . 'WikiCategoryManager.php';

/**
 * Class AdminWikiController
 *
 * This class manages the administration actions for the Wiki plugin of the 299Ko CMS.
 */
class AdminWikiController extends AdminController {
    // Instance of the page manager
    protected $pageManager;

    /**
     * Constructor.
     *
     * Initializes the page manager instance using a specific directory
     * where the pages are stored as JSON files.
     */
    public function __construct() {
        // Create the page manager with the path to the pages directory
        $this->pageManager = new WikiPageManager(DATA_PLUGIN . 'wiki' . DS . 'pages' . DS);
    }

    /**
     * Displays the list of Wiki pages in the administration panel.
     *
     * Retrieves the complete pages tree, creates a mapping for filenames to titles,
     * obtains the mapping of categories, and adds each page its category name and parent title.
     *
     * @return AdminResponse Returns the admin response containing the list template.
     */
    public function index() {
        // Retrieve the complete pages tree (includes all statuses, even drafts)
        $pagesTree = $this->pageManager->getPagesTree();

        // Flatten the tree to get a mapping of filename => title
        $flattened = $this->flattenPages($pagesTree, 0, null);
        $pagesMapping = [];
        foreach ($flattened as $p) {
            $pagesMapping[$p['filename']] = $p['title'];
        }

        // Retrieve the full list of wiki categories
        $categories = WikiCategoryManager::getInstance()->getAllCategories();
        $catMapping = [];
        foreach ($categories as $cat) {
            $catMapping[$cat['id']] = $cat['name'];
        }

        // Add category names and parent titles to the pages tree
        $pagesTree = $this->addCategoryNameToPages($pagesTree, $catMapping, $pagesMapping);

        // Create the admin response and add the dedicated template with necessary data
        $response = new AdminResponse();
        $tpl = $response->createPluginTemplate('wiki', 'admin/wiki-list');
        $response->setTitle("List of Wiki Pages");
        // Pass the pages tree to the template
        $tpl->set('pagesTree', $pagesTree);
        // Pass the category mapping to the template
        $tpl->set('categoriesMapping', $catMapping);
        // Add the administration menu to the template
        $tpl->set('adminMenu', $this->getAdminMenu());
        // Pass the router instance for URL management to the template
        $tpl->set('router', ROUTER::getInstance());
        $response->addTemplate($tpl);
        return $response;
    }

    /**
     * Displays the page edit form for creating or modifying a Wiki page.
     *
     * If the page exists, its data is pre-filled in the form; otherwise,
     * default values are defined for creating a new page.
     *
     * @return AdminResponse Returns the admin response containing the edit template.
     */
    public function edit() {
        // Retrieve the page identifier from GET parameter 'page'
        $pageId = isset($_GET['page']) ? $_GET['page'] : '';
        // Retrieve the page data for the given identifier
        $pageData = $this->pageManager->getPage($pageId);
        // If the page does not exist, define default values for a new page
        if (empty($pageData)) {
            $pageData = [
                'filename'   => '',
                'title'      => '',
                'content'    => '',
                'category'   => '',
                'parent'     => '',
                'draft'      => false,
                'created_at' => '',
                'updated_at' => ''
            ];
        }
        // Create the admin response with the edit template
        $response = new AdminResponse();
        $tpl = $response->createPluginTemplate('wiki', 'admin/wiki-edit');
        // Set the template title based on whether the page exists or not
        $response->setTitle($pageId ? "Edit Page" : "New Page");
        // Create and initialize the content editor with the existing page content
        $contentEditor = new Editor('content', $pageData['content']);
        $tpl->set('contentEditor', $contentEditor);
        // Pass the page data to the template
        $tpl->set('page', $pageData);
        // Retrieve and pass the list of categories available for the form
        $tpl->set('categories', WikiCategoryManager::getInstance()->getAllCategories());
        // Retrieve the pages tree for parent selection
        $pagesTree = $this->pageManager->getPagesTree();
        $exclude = !empty($pageData['filename']) ? $pageData['filename'] : null;
        // Flatten the tree while excluding the current page to avoid circular references
        $parentPages = $this->flattenPages($pagesTree, 0, $exclude);
        $tpl->set('parentPages', $parentPages);
        // Add the admin menu and router to the template
        $tpl->set('adminMenu', $this->getAdminMenu());
        $tpl->set('router', ROUTER::getInstance());
        $response->addTemplate($tpl);
        return $response;
    }

    /**
     * Saves a Wiki page (either creating a new one or updating an existing one).
     *
     * Retrieves the form data via POST, saves it, and then redirects the user
     * back to the pages list.
     */
    public function save() {
        // Retrieve the posted form data
        $data = $_POST;
        // The savePage method handles updating 'updated_at' and setting 'created_at' for new pages
        $this->pageManager->savePage($data);
        // Redirect to the Wiki admin page list after saving
        header("Location: " . ROUTER::getInstance()->generate('admin-wiki'));
        exit;
    }

    /**
     * Deletes a Wiki page.
     *
     * Retrieves the page identifier from GET, deletes the page if an identifier is provided,
     * and then redirects to the pages list.
     */
    public function delete() {
        // Retrieve the page identifier from GET parameter 'page'
        $pageId = $_GET['page'] ?? '';
        // If an identifier is provided, delete the page
        if ($pageId) {
            $this->pageManager->deletePage($pageId);
        }
        // Redirect to the Wiki admin page list
        header("Location: " . ROUTER::getInstance()->generate('admin-wiki'));
        exit;
    }

    /**
     * Generates the admin menu for the Wiki plugin.
     *
     * Constructs an HTML list containing links to different admin sections.
     *
     * @return string Returns the HTML code for the admin menu.
     */
    public static function getAdminMenu() {
        // Retrieve the router instance to generate URLs
        $router = ROUTER::getInstance();
        // Define the menu items with labels and their respective URLs
        $menu = [
            ['label' => 'Pages',      'url' => $router->generate('admin-wiki'), 'icon' => 'fa-solid fa-file-lines'],
            ['label' => 'Categories', 'url' => $router->generate('admin-wiki-categories'), 'icon' => 'fa-solid fa-tags'],
            ['label' => 'Settings',   'url' => $router->generate('admin-wiki-parameters'), 'icon' => 'fa-solid fa-sliders-h']
        ];
        // Build the HTML for the menu section
        $html = '<section>';
        foreach ($menu as $item) {
            $iconHtml = '';
            if (isset($item['icon']) && !empty($item['icon'])) {
                $iconHtml = '<i title="' . $item['label'] . '" class="' . $item['icon'] . '"></i> ';
            }
            $html .= '<a href="' . $item['url'] . '" class="button">' . $iconHtml . $item['label'] . '</a>';
        }
        $html .= '</section>';
        return $html;
    }

    /**
     * Recursively traverses the pages tree to add the category name and parent's title to each page.
     *
     * For every page in the tree, assigns the property "categoryName" based on the provided mapping.
     * Also adds "parentTitle" if the page has a parent.
     *
     * @param array $pagesTree The pages tree array.
     * @param array $catMapping Mapping of category id to category name.
     * @param array $pagesMapping Mapping of filename to title.
     * @return array The updated pages tree with category names and parent titles.
     */
    protected function addCategoryNameToPages(array $pagesTree, array $catMapping, array $pagesMapping) {
        // Iterate through each page in the tree
        foreach ($pagesTree as &$page) {
            // Set the category name if defined and present in the mapping
            if (!empty($page['category']) && isset($catMapping[$page['category']])) {
                $page['categoryName'] = $catMapping[$page['category']];
            } else {
                $page['categoryName'] = 'None';
            }
            // Set the parent title if a parent is defined and exists in the mapping
            if (!empty($page['parent']) && isset($pagesMapping[$page['parent']])) {
                $page['parentTitle'] = $pagesMapping[$page['parent']];
            } else {
                $page['parentTitle'] = '';
            }
            // If the page has children, recursively process them
            if (!empty($page['children'])) {
                $page['children'] = $this->addCategoryNameToPages($page['children'], $catMapping, $pagesMapping);
            }
        }
        return $pagesTree;
    }

    /**
     * Flattens the pages tree into a simple list with indentation.
     *
     * This method generates a flat list from a hierarchical tree by adding indentation
     * to reflect the depth level. The page whose filename matches $exclude is omitted.
     *
     * @param array $pages The hierarchical pages array.
     * @param int $depth The current indentation level (initialized to 0).
     * @param string|null $exclude (Optional) The filename of the page to exclude.
     * @return array A flat list of pages with keys 'filename' and 'title' prefixed by indentation.
     */
    protected function flattenPages(array $pages, $depth = 0, $exclude = null) {
        // Initialize the result array
        $result = [];
        // Loop through each page in the hierarchy
        foreach ($pages as $page) {
            // Skip the page if it should be excluded
            if ($exclude !== null && $page['filename'] == $exclude) {
                continue;
            }
            // Add the page to the result with proper indentation for the title
            $result[] = [
                'filename' => $page['filename'],
                'title' => str_repeat('-- ', $depth) . $page['title']
            ];
            // If the page has children, recursively flatten the hierarchy
            if (!empty($page['children'])) {
                $result = array_merge($result, $this->flattenPages($page['children'], $depth + 1, $exclude));
            }
        }
        return $result;
    }
}
