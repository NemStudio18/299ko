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

// Include the WikiPage class for handling wiki page objects
require_once PLUGINS . 'wiki' . DS . 'entities' . DS . 'WikiPage.php';

/**
 * Class WikiPageManager
 *
 * Manages wiki pages by loading, saving, deleting, searching, and organizing pages into a tree structure.
 */
class WikiPageManager {
    // Directory where page files are stored
    protected $pagesDir;

    /**
     * Constructor.
     *
     * Initializes the pages directory and creates it if it doesn't exist.
     *
     * @param string $pagesDir The directory path where wiki pages are stored.
     */
    public function __construct($pagesDir) {
        $this->pagesDir = $pagesDir;
        // Create the pages directory if it does not exist
        if (!is_dir($this->pagesDir)) {
            mkdir($this->pagesDir, 0755, true);
        }
    }

    /**
     * Retrieves all pages organized as a hierarchical tree.
     *
     * @return array Returns the tree of pages.
     */
    public function getPagesTree() {
        $pages = $this->loadAllPages();
        return $this->buildTree($pages);
    }

    /**
     * Retrieves a single page by its filename.
     *
     * @param string $filename The filename of the page.
     * @return array|null Returns an associative array of page data or null if not found.
     */
    public function getPage($filename) {
        if (empty($filename)) {
            return [];
        }
        $filePath = $this->pagesDir . $filename;
        if (!file_exists($filePath)) return null;
        $data = json_decode(file_get_contents($filePath), true);
        $data['filename'] = $filename;
        return $data;
    }

    /**
     * Saves a wiki page.
     *
     * If a page does not have a filename, a new one is generated.
     * Updates the page's update time and stores version history if content or title has changed.
     *
     * @param array $data The associative array containing page data.
     */
    public function savePage($data) {
        $filename = isset($data['filename']) && !empty($data['filename'])
            ? $data['filename']
            : uniqid() . '.json';
        $data['filename'] = $filename;
        $data['updated_at'] = date('Y-m-d H:i:s');
        $filepath = $this->pagesDir . $filename;

        // Initialize versions array
        $versions = [];

        if (file_exists($filepath)) {
            $oldData = json_decode(file_get_contents($filepath), true);
            // Preserve the existing creation date
            $data['created_at'] = isset($oldData['created_at']) ? $oldData['created_at'] : date('Y-m-d H:i:s');
            // If content or title has changed, record the old version
            if (
                ($oldData['content'] ?? '') !== ($data['content'] ?? '')
                || ($oldData['title'] ?? '') !== ($data['title'] ?? '')
            ) {
                // Preserve some details from the old version
                $oldVersion = [
                    'title'      => $oldData['title'] ?? '',
                    'content'    => $oldData['content'] ?? '',
                    'category'   => $oldData['category'] ?? '',
                    'parent'     => $oldData['parent'] ?? '',
                    'draft'      => $oldData['draft'] ?? false,
                    'created_at' => $oldData['created_at'] ?? '',
                    'updated_at' => $oldData['updated_at'] ?? '',
                ];
                // Retrieve previous versions if they exist
                if (isset($oldData['versions']) && is_array($oldData['versions'])) {
                    $versions = $oldData['versions'];
                }
                // Add the old version to the beginning of the versions array
                array_unshift($versions, $oldVersion);

                // Retrieve version limit from the configuration file
                $configPath = PLUGINS . 'wiki' . DS . 'param' . DS . 'config.json';
                $versionLimit = 2;
                if (file_exists($configPath)) {
                    $config = json_decode(file_get_contents($configPath), true);
                    if (isset($config['versionLimit'])) {
                        $versionLimit = (int)$config['versionLimit'];
                    }
                }
                // Keep only the defined number of versions
                if (count($versions) > $versionLimit) {
                    $versions = array_slice($versions, 0, $versionLimit);
                }
            } else {
                // If no changes, simply retain the old versions
                if (isset($oldData['versions']) && is_array($oldData['versions'])) {
                    $versions = $oldData['versions'];
                }
                $data['created_at'] = $oldData['created_at'] ?? date('Y-m-d H:i:s');
            }
        } else {
            $data['created_at'] = date('Y-m-d H:i:s');
        }
        // Assign the versions array to the page data
        $data['versions'] = $versions;
        file_put_contents($filepath, json_encode($data, JSON_PRETTY_PRINT));
    }

    /**
     * Deletes a wiki page.
     *
     * @param string $filename The filename of the page to delete.
     */
    public function deletePage($filename) {
        $filePath = $this->pagesDir . $filename;
        if (file_exists($filePath)) {
            unlink($filePath);
        }
    }

    /**
     * Searches pages by title or content matching a query.
     *
     * @param string $query The search query.
     * @return array Returns an array of pages that match the query.
     */
    public function searchPages($query) {
        $all = $this->loadAllPages();
        $results = [];
        foreach ($all as $page) {
            if (stripos($page['title'], $query) !== false || stripos($page['content'], $query) !== false) {
                $results[] = $page;
            }
        }
        return $results;
    }

    /**
     * Retrieves pages by a specific category ID, organized as a tree.
     *
     * @param mixed $catId The category ID.
     * @return array Returns the tree of pages filtered by the category.
     */
    public function getPagesByCategory($catId) {
        $all = $this->loadAllPages();
        $filtered = [];
        foreach ($all as $page) {
            if (isset($page['category']) && $page['category'] == $catId) {
                $filtered[] = $page;
            }
        }
        return $this->buildTree($filtered);
    }

    /**
     * Retrieves pages with no category assigned.
     *
     * @return array Returns an array of orphan pages.
     */
    public function getOrphanPages() {
        $all = $this->loadAllPages();
        $orphans = [];
        foreach ($all as $page) {
            if (empty($page['category'])) {
                $orphans[] = $page;
            }
        }
        return $orphans;
    }

    /**
     * Loads all wiki pages from the pages directory.
     *
     * @return array Returns an array of all pages.
     */
    protected function loadAllPages() {
        $pages = [];
        $files = scandir($this->pagesDir);
        foreach ($files as $file) {
            if ($file === '.' || $file === '..') continue;
            if (pathinfo($file, PATHINFO_EXTENSION) !== 'json') continue;
            $data = json_decode(file_get_contents($this->pagesDir . $file), true);
            // If not in admin mode, ignore draft pages
            if (!defined('ADMIN_MODE') || ADMIN_MODE === false) {
                if (!empty($data['draft']) && ($data['draft'] === true || $data['draft'] == '1')) {
                    continue;
                }
            }
            $data['filename'] = $file;
            $pages[] = $data;
        }
        return $pages;
    }

    /**
     * Builds a hierarchical tree from a flat array of pages.
     *
     * @param array $pages The flat array of pages.
     * @return array Returns the pages organized as a tree.
     */
    protected function buildTree(array $pages) {
        $tree = [];
        $indexed = [];
        // Index each page by filename and initialize children array
        foreach ($pages as $page) {
            $page['children'] = [];
            $indexed[$page['filename']] = $page;
        }
        // Build the tree by assigning child pages to their parent
        foreach ($indexed as $filename => $page) {
            if (!empty($page['parent']) && isset($indexed[$page['parent']])) {
                $indexed[$page['parent']]['children'][] = &$indexed[$filename];
            } else {
                $tree[] = &$indexed[$filename];
            }
        }
        return $tree;
    }
}
