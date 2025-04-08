<?php
// plugin/wiki/entities/WikiCategoryManager.php
// Adaptation de la gestion des catégories du plugin Wiki pour utiliser le core natif
// via la classe WikiCategoryCMS.
defined('ROOT') or exit('Access denied!');

// Inclure la classe concrète qui étend le core Category
require_once PLUGINS.'wiki'.DS.'entities'.DS.'WikiCategoryCMS.php';

class WikiCategoryManager {
    // Instance unique (singleton)
    protected static $instance;

    /**
     * Constructeur privé pour le pattern singleton.
     */
    private function __construct() {
        // Aucun initialisation particulière
    }

    /**
     * Retourne l'instance unique de WikiCategoryManager.
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
     * Récupère toutes les catégories sous forme de tableau associatif.
     *
     * @return array Liste des catégories (id, name, parent)
     */
    public function getAllCategories() {
        // Utilisation d'un objet dummy pour récupérer pluginId et name via les getters
        $dummy = new WikiCategoryCMS();
        $file = DATA_PLUGIN . $dummy->getPluginId() . '/categories-' . $dummy->getName() . '.json';
        $data = util::readJsonFile($file);

        // Vérification que $data est bien un tableau
        if (!is_array($data)) {
            $data = [];
        }

        $categories = [];
        // Parcours des catégories dans le fichier JSON
        foreach ($data as $id => $catData) {
            $categories[] = [
                'id'     => $id,
                // Le core utilise 'label' pour le nom affiché
                'name'   => $catData['label'] ?? '',
                // Utilisation de 'parentId' pour la relation hiérarchique
                'parent' => $catData['parentId'] ?? ''
            ];
        }
        return $categories;
    }

    /**
     * Construit l'arborescence des catégories.
     *
     * @return array Arborescence des catégories
     */
    public function getCategoriesTree() {
        $all = $this->getAllCategories();
        $tree = [];
        $indexed = [];

        // Indexation par identifiant et initialisation des enfants
        foreach ($all as $cat) {
            $cat['children'] = [];
            $indexed[$cat['id']] = $cat;
        }

        // Construction de l'arborescence
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
     * Récupère une catégorie par son identifiant.
     *
     * @param mixed $id
     * @return array|null
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
     * Sauvegarde une catégorie via le système natif.
     *
     * @param array $data Données de la catégorie (id, name, parent)
     */
    public function saveCategory($data) {
        $dummy = new WikiCategoryCMS();
        $file = DATA_PLUGIN . $dummy->getPluginId() . '/categories-' . $dummy->getName() . '.json';
        $categories = util::readJsonFile($file);
        if (!is_array($categories)) {
            $categories = [];
        }

        // Si 'id' est vide, on en génère un nouveau via uniqid()
        $id = !empty($data['id']) ? $data['id'] : uniqid();

        $categories[$id] = [
            // On stocke le nom sous 'label'
            'label'      => $data['name'],
            // Le parent est stocké sous 'parentId'
            'parentId'   => $data['parent'] ?? '',
            // On préserve d'éventuelles données existantes
            'items'      => $categories[$id]['items'] ?? [],
            'childrenId' => $categories[$id]['childrenId'] ?? [],
            'pluginArgs' => $categories[$id]['pluginArgs'] ?? []
        ];
        file_put_contents($file, json_encode($categories, JSON_PRETTY_PRINT));
    }


    /**
     * Supprime une catégorie en supprimant sa clé du fichier natif.
     *
     * @param mixed $id Identifiant de la catégorie à supprimer
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
