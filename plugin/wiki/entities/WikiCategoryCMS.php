<?php
// plugin/wiki/entities/WikiCategoryCMS.php
// Cette classe concrète étend la classe native Category du CMS et
// permet d'utiliser les fonctionnalités centralisées de gestion des catégories.
defined('ROOT') or exit('Access denied!');

class WikiCategoryCMS extends Category {
    /**
     * Constructeur de WikiCategoryCMS
     * On définit ici les propriétés spécifiques pour le plugin Wiki.
     *
     * @param int $id ID de la catégorie, -1 pour une nouvelle instance
     */
    public function __construct(int $id = -1) {
        // Définition de l'ID du plugin qui utilise la gestion des catégories du CMS
        $this->pluginId = 'wiki';
        // On utilise 'categories' comme nom de fichier
        $this->name = 'categories';
        // Catégories hiérarchisées
        $this->nested = true;
        // Une seule sélection possible
        $this->chooseMany = false;

        // Appel du constructeur parent qui gère le chargement des données si $id != -1
        parent::__construct($id);
    }

    /**
     * Getter public pour pluginId.
     *
     * @return string
     */
    public function getPluginId(): string {
        return $this->pluginId;
    }

    /**
     * Getter public pour name.
     *
     * @return string
     */
    public function getName(): string {
        return $this->name;
    }
}
