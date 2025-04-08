<?php
// plugin/wiki/entities/WikiPageManager.php
defined('ROOT') or exit('Access denied!');
require_once PLUGINS.'wiki'.DS.'entities'.DS.'WikiPage.php';

class WikiPageManager {
    protected $pagesDir;

    public function __construct($pagesDir) {
        $this->pagesDir = $pagesDir;
        if (!is_dir($this->pagesDir)) {
            mkdir($this->pagesDir, 0755, true);
        }
    }

    public function getPagesTree() {
        $pages = $this->loadAllPages();
        return $this->buildTree($pages);
    }

    public function getPage($filename) {
        // Si aucun nom de fichier n'est fourni (nouvelle page), retourner un tableau vide
        if (empty($filename)) {
            return [];
        }
        $filePath = $this->pagesDir . $filename;
        if (!file_exists($filePath)) return null;
        $data = json_decode(file_get_contents($filePath), true);
        $data['filename'] = $filename;
        return $data;
    }

    public function savePage($data) {
        $filename = isset($data['filename']) && !empty($data['filename']) ? $data['filename'] : uniqid() . '.json';
        $data['filename'] = $filename;
        $data['updated_at'] = date('Y-m-d H:i:s');
        $filepath = $this->pagesDir . $filename;
        if (file_exists($filepath)) {
            // Conserver la date de création existante
            $oldData = json_decode(file_get_contents($filepath), true);
            $data['created_at'] = isset($oldData['created_at']) ? $oldData['created_at'] : date('Y-m-d H:i:s');
        } else {
            $data['created_at'] = date('Y-m-d H:i:s');
        }
        file_put_contents($filepath, json_encode($data, JSON_PRETTY_PRINT));
    }

    public function deletePage($filename) {
        $filePath = $this->pagesDir . $filename;
        if (file_exists($filePath)) {
            unlink($filePath);
        }
    }

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

    protected function loadAllPages() {
        $pages = [];
        $files = scandir($this->pagesDir);
        foreach ($files as $file) {
            if ($file === '.' || $file === '..') continue;
            if (pathinfo($file, PATHINFO_EXTENSION) !== 'json') continue;
            $data = json_decode(file_get_contents($this->pagesDir . $file), true);
            $data['filename'] = $file;
            $pages[] = $data;
        }
        return $pages;
    }

    protected function buildTree(array $pages) {
        $tree = [];
        $indexed = [];
        foreach ($pages as $page) {
            $page['children'] = [];
            $indexed[$page['filename']] = $page;
        }
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
