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
 * Class WikiPage
 *
 * Represents a wiki page with properties such as filename, title, content, category, etc.
 */
class WikiPage {
    public $filename;
    public $title;
    public $content;
    public $category;
    public $parent;
    public $draft;
    public $updated_at;
    public $created_at;
    public $versions;

    /**
     * Constructor for WikiPage.
     *
     * Initializes the wiki page properties from the given data array.
     *
     * @param array $data The associative array containing wiki page data.
     */
    public function __construct($data) {
        $this->filename   = $data['filename'] ?? '';
        $this->title      = $data['title'] ?? '';
        $this->content    = $data['content'] ?? '';
        $this->category   = $data['category'] ?? '';
        $this->parent     = $data['parent'] ?? '';
        $this->draft      = $data['draft'] ?? false;
        $this->updated_at = $data['updated_at'] ?? '';
        $this->created_at = $data['created_at'] ?? '';
        $this->versions   = $data['versions'] ?? [];
    }

    /**
     * Converts the WikiPage object into an associative array.
     *
     * @return array Returns an associative array containing the wiki page data.
     */
    public function toArray() {
        return [
            'filename'   => $this->filename,
            'title'      => $this->title,
            'content'    => $this->content,
            'category'   => $this->category,
            'parent'     => $this->parent,
            'draft'      => $this->draft,
            'updated_at' => $this->updated_at,
            'created_at' => $this->created_at,
            'versions'   => $this->versions
        ];
    }
}
