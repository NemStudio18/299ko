<?php
/**
 * Plugin Wiki pour 299Ko CMS
 * Créé le plugin Wiki en créant le répertoire dédié aux pages.
 */

defined('ROOT') or exit('Access denied!');

// Détection du mode (administration ou public) en fonction de l'URL
if (strpos($_SERVER['REQUEST_URI'], '/admin/') !== false) {
    define('ADMIN_MODE', true);
} else {
    define('ADMIN_MODE', false);
}

// Fonction d'installation du plugin Wiki
function wikiInstall() {
    $wikiDir = DATA_PLUGIN . 'wiki' . DS;
    if (!is_dir($wikiDir)) {
        if (!mkdir($wikiDir, 0755, true)) {
            die("Erreur lors de la création du répertoire Wiki.");
        }
    }
    echo "Installation du plugin Wiki terminée avec succès.";
}

/**
 * Hook appelé avant l'exécution du plugin Wiki.
 * Vérifie que le dossier de stockage existe et le crée si nécessaire.
 */
function wikiBeforeRunPlugin() {
    // Chargement de la configuration du plugin
    $configPath = PLUGINS . 'wiki' . DS . 'config.json';
    if (file_exists($configPath)) {
        $config = json_decode(file_get_contents($configPath), true);
    } else {
        $config = [
            "defaultPage" => "Accueil",
            "storageDir"   => "wiki",
            "versionLimit" => 2
        ];
    }
    $storageDir = DATA_PLUGIN . $config['storageDir'] . DS;
    if (!is_dir($storageDir)) {
        mkdir($storageDir, 0755, true);
    }
    return true;
}
