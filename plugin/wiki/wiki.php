<?php
/**
 * Plugin Wiki pour 299Ko CMS
 * Créé le plugin Wiki en créant le répertoire dédié aux pages.
 */

defined('ROOT') or exit('Access denied!');

function wikiInstall() {
    $wikiDir = DATA_PLUGIN . 'wiki' . DS;
    if (!is_dir($wikiDir)) {
        if (!mkdir($wikiDir, 0755, true)) {
            die("Erreur lors de la création du répertoire Wiki.");
        }
    }
    echo "Installation du plugin Wiki terminée avec succès.";
}

function wikiBeforeRunPlugin() {
    // Par défaut, rien à faire
    return true;
}