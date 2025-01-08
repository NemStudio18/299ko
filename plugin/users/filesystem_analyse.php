<?php
// Nom du fichier Markdown généré
$outputFile = __DIR__ . '/filesystem_report.md';

/**
 * Fonction pour générer la vue ASCII d'un dossier
 * 
 * @param string $dir Chemin du dossier
 * @param int $level Niveau d'indentation
 * @return string Vue ASCII du dossier
 */
function generateAsciiTree($dir, $level = 0) {
    $output = '';
    $items = scandir($dir);

    foreach ($items as $item) {
        if ($item === '.' || $item === '..') {
            continue;
        }

        $path = $dir . DIRECTORY_SEPARATOR . $item;

        // Exclure les fichiers SQLite et plucss.min.css
        if (is_file($path) && (str_ends_with($item, '.sqlite') || $item === 'plucss.min.css' || $item === 'filesystem_analyse.php' || $item === 'explorer.php' || $item === 'index_data.php')) {
            continue;
        }

        $prefix = str_repeat('  ', $level) . '|-- ';
        $output .= $prefix . $item . "\n";

        if (is_dir($path)) {
            $output .= generateAsciiTree($path, $level + 1);
        }
    }

    return $output;
}

/**
 * Fonction pour récupérer les détails des fichiers dans un dossier
 * 
 * @param string $dir Chemin du dossier
 * @return string Détails des fichiers formatés en Markdown
 */
function generateFileDetails($dir) {
    $output = '';
    $items = scandir($dir);

    foreach ($items as $item) {
        if ($item === '.' || $item === '..') {
            continue;
        }

        $path = $dir . DIRECTORY_SEPARATOR . $item;

        // Exclure les fichiers SQLite et plucss.min.css
        if (is_file($path) && (str_ends_with($item, '.sqlite') || $item === 'plucss.min.css' || $item === 'plucss.css' || $item === 'filesystem_analyse.php' || $item === 'filesystem_report.md' || $item === 'plucss.css' || $item === 'explorer.php' || $item === 'refact.php' || $item === 'index_data.php')) {
            continue;
        }

        if (is_file($path)) {
            $output .= "### Fichier : " . $item . "\n";
            $output .= "**Chemin :** " . $path . "\n\n";
            $output .= "**Contenu :**\n";
            $output .= "```\n" . file_get_contents($path) . "\n```\n\n";
        } elseif (is_dir($path)) {
            $output .= generateFileDetails($path);
        }
    }

    return $output;
}

try {
    $baseDir = __DIR__;

    // Générer la vue ASCII de l'arborescence
    $markdown = "# Rapport sur le système de fichiers\n\n";
    $markdown .= "## Vue ASCII de l'arborescence des fichiers\n\n";
    $markdown .= "```\n";
    $markdown .= $baseDir . "\n";
    $markdown .= generateAsciiTree($baseDir);
    $markdown .= "```\n\n";

    // Générer les détails des fichiers
    $markdown .= "## Contenu des fichiers\n\n";
    $markdown .= generateFileDetails($baseDir);

    // Écrire dans le fichier Markdown
    file_put_contents($outputFile, $markdown);
    echo "Rapport généré dans le fichier : " . $outputFile;
} catch (Exception $e) {
    die("Erreur : " . $e->getMessage());
}
?>
