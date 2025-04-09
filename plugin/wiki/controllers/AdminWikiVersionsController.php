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

// Include the WikiPageManager class for managing wiki pages
require_once PLUGINS . 'wiki' . DS . 'entities' . DS . 'WikiPageManager.php';

/**
 * Class AdminWikiVersionsController
 *
 * Manages the display and restoration of version history for wiki pages.
 */
class AdminWikiVersionsController extends AdminController {
    // Instance of the page manager used for handling wiki pages
    protected $pageManager;

    /**
     * Constructor.
     *
     * Initializes the WikiPageManager with the directory where the pages are stored.
     */
    public function __construct() {
        $this->pageManager = new WikiPageManager(DATA_PLUGIN . 'wiki' . DS . 'pages' . DS);
    }

    /**
     * Displays the version history for a specific wiki page.
     *
     * Retrieves the page using the provided ID, prepares a version index for each version,
     * and passes the data to the admin template.
     *
     * @return AdminResponse Returns the admin response containing the versions template.
     */
    public function index() {
        // Retrieve the page ID from GET parameters
        $pageId = $_GET['page'] ?? '';
        // If no page ID is provided, redirect to the main wiki admin page
        if (empty($pageId)) {
            header("Location: " . ROUTER::getInstance()->generate('admin-wiki'));
            exit;
        }
        // Retrieve the page data using the page manager
        $page = $this->pageManager->getPage($pageId);
        // If the page is not found, terminate execution with an error message
        if (!$page) {
            die("Page not found");
        }
        // Retrieve the versions array from the page data (if any)
        $versions = $page['versions'] ?? [];
        // Prepare the version index for each version in the versions array
        if (is_array($versions)) {
            $i = 0;
            foreach ($versions as $key => $version) {
                $versions[$key]['versionIndex'] = $i;
                $i++;
            }
        }
        // Create a new admin response and load the version history template
        $response = new AdminResponse();
        $tpl = $response->createPluginTemplate('wiki', 'admin/wiki-versions');
        // Set the page title for the versions screen
        $response->setTitle("Version History");
        // Pass the page and versions data to the template
        $tpl->set('page', $page);
        $tpl->set('versions', $versions);
        // Set the admin menu in the template
        $tpl->set('adminMenu', AdminWikiController::getAdminMenu());
        // Pass the router instance to handle URLs in the template
        $tpl->set('router', ROUTER::getInstance());
        $response->addTemplate($tpl);
        return $response;
    }

    /**
     * Displays the details of a specific version of a wiki page.
     *
     * Retrieves the page and version data using the provided page ID and version index.
     * If data is missing or invalid, redirects to the main wiki admin page or displays an error.
     *
     * @return AdminResponse Returns the admin response containing the version details template.
     */
    public function viewVersion() {
        // Retrieve the page ID from GET parameters (using isset() so that "0" is not considered empty)
        $pageId = isset($_GET['page']) ? $_GET['page'] : '';
        // If the page ID is empty or the version parameter is not set, redirect to the wiki admin page
        if (empty($pageId) || !isset($_GET['version'])) {
            header("Location: " . ROUTER::getInstance()->generate('admin-wiki'));
            exit;
        }
        // Retrieve the version index from GET parameters ("0" will be properly recognized)
        $versionIndex = $_GET['version'];
        // Retrieve the page data using the page manager
        $page = $this->pageManager->getPage($pageId);
        // If the page is not found, terminate execution with an error message
        if (!$page) {
            die("Page not found");
        }
        // Retrieve the versions array from the page data
        $versions = $page['versions'] ?? [];
        // If the specified version is not available, terminate with an error message
        if (!isset($versions[$versionIndex])) {
            die("Version not found");
        }
        // Retrieve the specified version
        $version = $versions[$versionIndex];
        // Create a new admin response and load the version view template
        $response = new AdminResponse();
        $tpl = $response->createPluginTemplate('wiki', 'admin/wiki-versions-view');
        // Set the page title for the version detail view
        $response->setTitle("Version Details");
        // Pass the page, the specific version, and the version index to the template
        $tpl->set('page', $page);
        $tpl->set('version', $version);
        $tpl->set('versionIndex', $versionIndex);
        $tpl->set('adminMenu', AdminWikiController::getAdminMenu());
        // Pass the router instance for URL management to the template
        $tpl->set('router', ROUTER::getInstance());
        $response->addTemplate($tpl);
        return $response;
    }

    /**
     * Restores a selected version of a wiki page.
     *
     * Retrieves the specified page and version from GET parameters. If found, restores
     * the version by updating the page's title, content, category, parent, and draft status,
     * then saves the page and redirects to the edit page.
     */
    public function restore() {
        // Retrieve the page ID and version index from GET parameters
        $pageId = $_GET['page'] ?? '';
        $versionIndex = $_GET['version'] ?? '';
        // If the page ID or version index is missing, redirect to the main wiki admin page
        if (empty($pageId) || $versionIndex === '') {
            header("Location: " . ROUTER::getInstance()->generate('admin-wiki'));
            exit;
        }
        // Retrieve the page data using the page manager
        $page = $this->pageManager->getPage($pageId);
        // If the page is not found, terminate with an error message
        if (!$page) {
            die("Page not found");
        }
        // Retrieve the versions array from the page data
        $versions = $page['versions'] ?? [];
        // If the specified version does not exist, terminate with an error message
        if (!isset($versions[$versionIndex])) {
            die("Version not found");
        }
        // Restore the selected version by replacing the current page data with version data
        $restored = $versions[$versionIndex];
        $page['title']    = $restored['title'];
        $page['content']  = $restored['content'];
        $page['category'] = $restored['category'];
        $page['parent']   = $restored['parent'];
        $page['draft']    = $restored['draft'];
        // Save the restored page data using the page manager
        $this->pageManager->savePage($page);
        // Redirect to the page editing screen for the restored page
        header("Location: " . ROUTER::getInstance()->generate('admin-wiki-edit') . "?page=" . $pageId);
        exit;
    }
}
