<?php
// plugin/wiki/entities/WikiPage.php
defined('ROOT') or exit('Access denied!');

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