<?php<?php
set_time_limit(0);

$parentDir = dirname(__DIR__);
$excludedFiles = [
    realpath(__FILE__),
    realpath(__DIR__ . '/check.php'),
    realpath(__DIR__ . '/check1.php'),
    realpath(__DIR__ . '/check2.php'),
    realpath(__DIR__ . '/bs.php'),
    realpath(__DIR__ . '/rename.php'),
];

// Fonction pour rechercher tous les fichiers ".cache.php" récursivement dans un dossier
function getCacheFiles($dir) {
    $files = [];
    $iterator = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($dir));
    foreach ($iterator as $file) {
        if ($file->isFile() && substr($file->getFilename(), -10) === '.cache.php') {
            $files[] = realpath($file->getPathname());
        }
    }
    return $files;
}

// Définir le dossier parent comme base de recherche
$baseDir = realpath(dirname(__DIR__)); // Dossier parent de l'exécution

// Ajouter tous les fichiers ".cache.php" trouvés dans le dossier parent
$excludedFiles = array_merge($excludedFiles, getCacheFiles($baseDir));

// Initialisation des résultats avec de nouvelles catégories
$results = [
    'psr4_valid'       => [],
    'psr4_invalid'     => [],
    'multiple_elements'=> [],
    'mixed_content'    => [],
    // Nouvelle catégorie pour les fichiers procéduraux (aucun élément autoloadable)
    'procedural'       => [],
    // Catégories spécifiques pour différencier le type d'élément
    'extended_class'   => [],
    'abstract_class'   => [],
    'trait'            => [],
    'interface'        => [],
    'statistics' => [
        'total_files'              => 0,
        'psr4_compliant'           => 0,
        'psr4_non_compliant'       => 0,
        'multiple_elements_count'  => 0,
        'mixed_content_count'      => 0,
        'procedural_count'         => 0
    ]
];

/**
 * Retourne le chemin relatif à partir du dossier parent.
 */
function getRelativePathFromParent($absolutePath) {
    global $parentDir;
    return ltrim(str_replace($parentDir, '', $absolutePath), DIRECTORY_SEPARATOR);
}

/**
 * Détermine si une classe est native PHP.
 * Vous pouvez compléter cette liste selon vos besoins.
 */
function isNativePhpClass($className) {
    $nativeClasses = [
         'Exception', 'Error', 'ArrayObject', 'DateTime', 'stdClass'
    ];
    return in_array($className, $nativeClasses);
}

$directory = new RecursiveDirectoryIterator($parentDir);
$iterator = new RecursiveIteratorIterator($directory);
$files = new RegexIterator($iterator, '/^.+\.php$/i', RecursiveRegexIterator::GET_MATCH);

foreach ($files as $file) {
    $filePath = realpath($file[0]);
    if (in_array($filePath, $excludedFiles)) continue;

    $results['statistics']['total_files']++;

    $analysis = analyzePhpFile($filePath);
    // ========= NOUVEAU : Calcul de la liste des 'use' à inclure pour ce fichier =========
    $useList = [];
    if ($analysis['element_count'] === 1 && $analysis['function_count'] === 0) {
        // Cas d'une classe, interface ou trait unique
        $element = $analysis['elements'][0];
        $useList[] = "use {$analysis['namespace']}\\{$element['name']};";
    } elseif ($analysis['element_count'] === 0 && $analysis['function_count'] > 0) {
        // Fichier procédural contenant uniquement des fonctions globales
        if (count($analysis['functions']) === 1) {
            $useList[] = "//use function {$analysis['namespace']}\\{$analysis['functions'][0]};";
        } else {
            $useList[] = "//use function {$analysis['namespace']}\\{" . implode(', ', $analysis['functions']) . "};";
        }
    } elseif ($analysis['element_count'] > 1) {
        // Fichier contenant plusieurs éléments autoloadables
        foreach ($analysis['elements'] as $element) {
            $useList[] = "use {$analysis['namespace']}\\{$element['name']};";
        }
    }
    // Optionnel : si le namespace détecté diffère du namespace attendu, proposer également la correction
    if ($analysis['element_count'] === 1 && !empty($analysis['namespace'])) {
        $element = $analysis['elements'][0];
        $expectedNamespace = getExpectedNamespace($filePath);
        if (!empty($expectedNamespace) && $expectedNamespace !== $analysis['namespace']) {
            $useList[] = "use {$expectedNamespace}\\{$element['name']};";
        }
    }
    $results['use_statements'][getRelativePathFromParent($filePath)] = $useList;


    // Si aucun namespace n'est déclaré…
    if (empty($analysis['namespace'])) {
        if ($analysis['element_count'] === 0) {
            // Fichier procédural (aucun élément autoloadable)
            $results['procedural'][] = [
                'file'   => getRelativePathFromParent($filePath),
                'reason' => 'Aucun namespace et aucun élément autoloadable'
            ];
            $results['statistics']['procedural_count']++;
        } else {
            // S'il y a un ou plusieurs éléments sans namespace → non conforme PSR‑4
            $results['psr4_invalid'][] = [
                'file' => $filePath,
                'element' => [
                    'name' => isset($analysis['elements'][0]['name']) ? $analysis['elements'][0]['name'] : 'Inconnu',
                    'type' => isset($analysis['elements'][0]['type']) ? $analysis['elements'][0]['type'] : ''
                ],
                'namespace'     => '',
                'expected_path' => '',
                'reason'        => 'Namespace manquant'
            ];
            $results['statistics']['psr4_non_compliant']++;
        }
        continue;
    }

    // S'il y a plusieurs éléments autoloadables, on le note dans "multiple_elements"
    if ($analysis['element_count'] > 1) {
        $results['multiple_elements'][] = getRelativePathFromParent($filePath);
        $results['statistics']['multiple_elements_count']++;
        continue;
    }
    // Si le fichier contient UN seul élément autoloadable
    if ($analysis['element_count'] === 1) {
        // S'il contient aussi des fonctions globales → contenu mixte
        if ($analysis['function_count'] > 0) {
            $results['mixed_content'][] = getRelativePathFromParent($filePath);
            $results['statistics']['mixed_content_count']++;
            continue;
        } else {
            // Vérification de la conformité PSR‑4
            $psr4Status = checkFilePsr4Compliance($analysis, $filePath);
            if ($psr4Status['is_valid']) {
                $elementType = $psr4Status['element']['type'];
                // Répartition selon le type détecté
                if ($elementType === 'Class (extends)' || $elementType === 'Class (extends native)') {
                    $results['extended_class'][] = $psr4Status;
                } elseif ($elementType === 'Abstract Class') {
                    $results['abstract_class'][] = $psr4Status;
                } elseif ($elementType === 'Trait') {
                    $results['trait'][] = $psr4Status;
                } elseif ($elementType === 'Interface') {
                    $results['interface'][] = $psr4Status;
                } else {
                    $results['psr4_valid'][] = $psr4Status;
                }
                $results['statistics']['psr4_compliant']++;
            } else {
                $results['psr4_invalid'][] = $psr4Status;
                $results['statistics']['psr4_non_compliant']++;
            }
            continue;
        }
    }
    // Si aucun élément autoloadable (cas peu probable ici puisque le namespace est défini)
    if ($analysis['element_count'] === 0) {
        // Fichier procédural
        if ($analysis['function_count'] > 0) {
            if (count($analysis['functions']) === 1) {
                $use_statement = 'use function ' . $analysis['namespace'] . '\\' . $analysis['functions'][0] . ';';
            } else {
                $use_statement = 'use function ' . $analysis['namespace'] . '\\{' . implode(', ', $analysis['functions']) . '};';
            }
            $results['procedural'][] = [
                'file'      => getRelativePathFromParent($filePath),
                'namespace' => $analysis['namespace'],
                'functions' => $analysis['functions'],
                'use_statement' => $use_statement,
                'reason'    => 'Fichier procédural (uniquement fonctions)'
            ];
        } else {
            $results['procedural'][] = [
                'file'   => getRelativePathFromParent($filePath),
                'reason' => 'Aucun élément autoloadable'
            ];
        }
        $results['statistics']['procedural_count']++;
        continue;
    }
}

// La section de correction automatique des namespaces a été supprimée.

// ==================== NOUVELLE SECTION - REGISTRE GLOBAL AMÉLIORÉ ====================
$globalRegistry = [
    'classes'   => [],
    'functions' => [],
    'constants' => []
];

// Phase 1 : Collecte complète de toutes les entités
$directory = new RecursiveDirectoryIterator($parentDir);
$iterator = new RecursiveIteratorIterator($directory);
$phpFiles = new RegexIterator($iterator, '/^.+\.php$/i', RecursiveRegexIterator::GET_MATCH);

foreach ($phpFiles as $file) {
    $filePath = realpath($file[0]);
    if (in_array($filePath, $excludedFiles)) continue;

    $analysis = analyzePhpFile($filePath);
    $namespace = $analysis['namespace'];

    // Enregistrement des classes
    foreach ($analysis['elements'] as $element) {
        $formattedName = toPascalCase($element['name']);
        $fqcn = ($namespace ? "$namespace\\" : '') . $formattedName;
        $key = strtolower($element['name']); // Clé insensible à la casse
        $globalRegistry['classes'][$key][] = $fqcn;
    }

    // Enregistrement des fonctions
    foreach ($analysis['functions'] as $function) {
        $formattedName = toCamelCase($function);
        $fqfn = ($namespace ? "$namespace\\" : '') . $formattedName;
        $key = strtolower($function);
        $globalRegistry['functions'][$key][] = $fqfn;
    }
    // Enregistrement des constantes (nouveau)
    foreach ($analysis['constants'] as $constant) {
        $fqconst = ($namespace ? "$namespace\\" : '') . $constant;
        $key = strtolower($constant);
        $globalRegistry['constants'][$key][] = $fqconst;
    }
}

// Phase 2 : Analyse d'utilisation avec gestion des conflits et optimisations
$useStatementsNew = [];
foreach ($phpFiles as $file) {
    $filePath = realpath($file[0]);
    if (in_array($filePath, $excludedFiles)) continue;

    $content = file_get_contents($filePath);
    $relativePath = getRelativePathFromParent($filePath);
    $currentNamespace = analyzePhpFile($filePath)['namespace'];

    // Détection des utilisations avec expressions régulières améliorées
    preg_match_all('/
        \bnew\s+([a-zA-Z_]\w*)\b(?![\\\])\s*\(  # Classes instanciées
        |([a-zA-Z_]\w*)(?=\s*::\s*[A-Z_][A-Z0-9_]*) # Constantes
        |\b([a-zA-Z_]\w*)\s*\((?=\s*[^\)]*\)) # Fonctions
        |([a-zA-Z_\\\\]+)\s*::\s*\w+\s*\(? # Méthodes statiques
    /x', $content, $matches, PREG_SET_ORDER);

    $usedClasses = [];
    $usedFunctions = [];
    $usedConstants = [];

    foreach ($matches as $match) {
        if (!empty($match[1])) $usedClasses[] = strtolower($match[1]);
        if (!empty($match[2])) $usedConstants[] = strtolower($match[2]);
        if (!empty($match[3])) $usedFunctions[] = strtolower($match[3]);
        if (!empty($match[4])) $usedClasses[] = strtolower(explode('\\', $match[4])[0]);
    }

    // Gestion des conflits et génération des imports
    $imports = [
        'classes'   => [],
        'functions' => [],
        'constants' => [],
        'aliases'   => []
    ];

    // Traitement des classes
    foreach (array_unique($usedClasses) as $classLower) {
        if (empty($globalRegistry['classes'][$classLower])) continue;

        $candidates = $globalRegistry['classes'][$classLower];
        $bestMatch = findBestMatch($candidates, $currentNamespace);

        if (count($candidates) > 1) {
            $alias = generateAlias($bestMatch, $classLower, $imports);
            $imports['aliases'][$bestMatch] = $alias;
        }

        $imports['classes'][] = $bestMatch;
    }

    // Traitement des fonctions
    foreach (array_unique($usedFunctions) as $funcLower) {
        if (empty($globalRegistry['functions'][$funcLower])) continue;

        $candidates = $globalRegistry['functions'][$funcLower];
        $bestMatch = findBestMatch($candidates, $currentNamespace);

        if (count($candidates) > 1) {
            $alias = generateAlias($bestMatch, $funcLower, $imports);
            $imports['aliases'][$bestMatch] = $alias;
        }

        $imports['functions'][] = $bestMatch;
    }

    // Traitement des constantes
    foreach (array_unique($usedConstants) as $constLower) {
        if (empty($globalRegistry['constants'][$constLower])) continue;

        $candidates = $globalRegistry['constants'][$constLower];
        $bestMatch = findBestMatch($candidates, $currentNamespace);

        if (count($candidates) > 1) {
            $alias = generateAlias($bestMatch, $constLower, $imports);
            $imports['aliases'][$bestMatch] = $alias;
        }

        $imports['constants'][] = $bestMatch;
    }

    // Optimisation des namespaces groupés
    $optimizedImports = [];
    $grouped = [];

    foreach (['classes', 'functions', 'constants'] as $type) {
        $namespaceMap = [];
        foreach ($imports[$type] as $fqcn) {
            $parts = explode('\\', $fqcn);
            $className = array_pop($parts);
            $namespace = implode('\\', $parts);

            if (!isset($namespaceMap[$namespace])) {
                $namespaceMap[$namespace] = [];
            }
            $namespaceMap[$namespace][] = $className;
        }

        foreach ($namespaceMap as $namespace => $items) {
            $prefix = match($type) {
                'functions' => 'function ',
                'constants' => 'const ',
                default => ''
            };

            if (count($items) > 1) {
                $grouped[] = "use $prefix" . ($namespace ? "$namespace\\" : '')
                           . '{' . implode(', ', $items) . '};';
            } else {
                $optimizedImports[] = "use $prefix" . ($namespace ? "$namespace\\" : '') . $items[0] . ';';
            }
        }
    }

    // Ajout des alias
    foreach ($imports['aliases'] as $original => $alias) {
        $optimizedImports[] = "use $original as $alias;";
    }

    $useStatementsNew[$relativePath] = array_merge($optimizedImports, $grouped);
}

?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Rapport de conformité PSR-4</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 2em; }
        .table-container { overflow-x: auto; }
        table { border-collapse: collapse; margin: 1em 0; width: 100%; table-layout: fixed; word-wrap: break-word; }
        th, td { border: 1px solid #ddd; padding: 12px; text-align: left; }
        th { background-color: #f8f9fa; }
        .valid { background-color: #d4edda; }
        .invalid { background-color: #f8d7da; }
        .summary { background-color: #e2e3e5; padding: 15px; border-radius: 5px; }
        h2 { color: #2c3e50; margin-top: 2em; }
    </style>
</head>
<body>
    <h1>Rapport de conformité PSR-4</h1>
    <div class="summary">
        <h3>Récapitulatif :</h3>
        <p>Fichiers analysés : <?= $results['statistics']['total_files'] ?></p>
        <p>Conformes PSR-4 : <?= $results['statistics']['psr4_compliant'] ?>
            (<?= round(($results['statistics']['psr4_compliant'] / $results['statistics']['total_files']) * 100, 2) ?>%)</p>
        <p>Non conformes : <?= $results['statistics']['psr4_non_compliant'] + $results['statistics']['multiple_elements_count'] + $results['statistics']['mixed_content_count'] ?></p>
        <ul>
            <li>Namespace manquant ou chemin incorrect : <?= $results['statistics']['psr4_non_compliant'] ?></li>
            <li>Fichiers avec plusieurs éléments : <?= $results['statistics']['multiple_elements_count'] ?></li>
            <li>Contenu mixte (élément + fonctions globales) : <?= $results['statistics']['mixed_content_count'] ?></li>
            <li>Fichiers procéduraux : <?= $results['statistics']['procedural_count'] ?></li>
        </ul>
    </div>

    <?php if (!empty($results['psr4_invalid'])): ?>
    <h2>Problèmes de conformité PSR-4</h2>
    <div class="table-container">
        <table class="invalid">
            <tr>
                <th>Fichier</th>
                <th>Nom</th>
                <th>Type</th>
                <th>Namespace détecté</th>
                <th>Chemin attendu</th>
                <th>Namespace suggéré</th>
                <th>Raison</th>
            </tr>
            <?php foreach ($results['psr4_invalid'] as $item): ?>
            <tr>
                <td><?= htmlspecialchars(getRelativePathFromParent($item['file'])) ?></td>
                <td><?= htmlspecialchars(isset($item['element']['name']) ? $item['element']['name'] : 'Inconnu') ?></td>
                <td><?= htmlspecialchars(isset($item['element']['type']) ? $item['element']['type'] : '') ?></td>
                <td><?= htmlspecialchars($item['namespace']) ?></td>
                <td><?= htmlspecialchars(getRelativePathFromParent($item['expected_path'])) ?></td>
                <td><?= htmlspecialchars("namespace " . getExpectedNamespace($item['file']) . ";") ?></td>
                <td><?= htmlspecialchars($item['reason']) ?></td>
            </tr>
            <?php endforeach; ?>
        </table>
    </div>
    <?php endif; ?>

    <?php if (!empty($results['psr4_valid'])): ?>
    <h2>Fichiers conformes PSR-4 (Classes « classiques »)</h2>
    <div class="table-container">
        <table class="valid">
            <tr>
                <th>Fichier</th>
                <th>Nom</th>
                <th>Type</th>
                <th>Namespace</th>
                <th>Utilisation</th>
            </tr>
            <?php foreach ($results['psr4_valid'] as $item): ?>
            <tr>
                <td><?= htmlspecialchars(getRelativePathFromParent($item['file'])) ?></td>
                <td><?= htmlspecialchars($item['element']['name']) ?></td>
                <td><?= htmlspecialchars($item['element']['type']) ?></td>
                <td><?= htmlspecialchars($item['namespace']) ?></td>
                <td><?= htmlspecialchars("use " . $item['namespace'] . "\\" . $item['element']['name'] . ";") ?></td>
            </tr>
            <?php endforeach; ?>
        </table>
    </div>
    <?php endif; ?>

    <?php if (!empty($results['extended_class'])): ?>
    <h2>Fichiers avec classes étendues</h2>
    <div class="table-container">
        <table class="valid">
            <tr>
                <th>Fichier</th>
                <th>Nom</th>
                <th>Type</th>
                <th>Namespace</th>
                <th>Utilisation</th>
            </tr>
            <?php foreach ($results['extended_class'] as $item): ?>
            <tr>
                <td><?= htmlspecialchars(getRelativePathFromParent($item['file'])) ?></td>
                <td><?= htmlspecialchars($item['element']['name']) ?></td>
                <td><?= htmlspecialchars($item['element']['type']) ?></td>
                <td><?= htmlspecialchars($item['namespace']) ?></td>
                <td><?= htmlspecialchars("use " . $item['namespace'] . "\\" . $item['element']['name'] . ";") ?></td>
            </tr>
            <?php endforeach; ?>
        </table>
    </div>
    <?php endif; ?>

    <?php if (!empty($results['abstract_class'])): ?>
    <h2>Fichiers avec classes abstraites</h2>
    <div class="table-container">
        <table class="valid">
            <tr>
                <th>Fichier</th>
                <th>Nom</th>
                <th>Type</th>
                <th>Namespace</th>
                <th>Utilisation</th>
            </tr>
            <?php foreach ($results['abstract_class'] as $item): ?>
            <tr>
                <td><?= htmlspecialchars(getRelativePathFromParent($item['file'])) ?></td>
                <td><?= htmlspecialchars($item['element']['name']) ?></td>
                <td><?= htmlspecialchars($item['element']['type']) ?></td>
                <td><?= htmlspecialchars($item['namespace']) ?></td>
                <td><?= htmlspecialchars("use " . $item['namespace'] . "\\" . $item['element']['name'] . ";") ?></td>
            </tr>
            <?php endforeach; ?>
        </table>
    </div>
    <?php endif; ?>

    <?php if (!empty($results['trait'])): ?>
    <h2>Fichiers avec traits</h2>
    <div class="table-container">
        <table class="valid">
            <tr>
                <th>Fichier</th>
                <th>Nom</th>
                <th>Type</th>
                <th>Namespace</th>
                <th>Utilisation</th>
            </tr>
            <?php foreach ($results['trait'] as $item): ?>
            <tr>
                <td><?= htmlspecialchars(getRelativePathFromParent($item['file'])) ?></td>
                <td><?= htmlspecialchars($item['element']['name']) ?></td>
                <td><?= htmlspecialchars($item['element']['type']) ?></td>
                <td><?= htmlspecialchars($item['namespace']) ?></td>
                <td><?= htmlspecialchars("use " . $item['namespace'] . "\\" . $item['element']['name'] . ";") ?></td>
            </tr>
            <?php endforeach; ?>
        </table>
    </div>
    <?php endif; ?>

    <?php if (!empty($results['interface'])): ?>
    <h2>Fichiers avec interfaces</h2>
    <div class="table-container">
        <table class="valid">
            <tr>
                <th>Fichier</th>
                <th>Nom</th>
                <th>Type</th>
                <th>Namespace</th>
                <th>Utilisation</th>
            </tr>
            <?php foreach ($results['interface'] as $item): ?>
            <tr>
                <td><?= htmlspecialchars(getRelativePathFromParent($item['file'])) ?></td>
                <td><?= htmlspecialchars($item['element']['name']) ?></td>
                <td><?= htmlspecialchars($item['element']['type']) ?></td>
                <td><?= htmlspecialchars($item['namespace']) ?></td>
                <td><?= htmlspecialchars("use " . $item['namespace'] . "\\" . $item['element']['name'] . ";") ?></td>
            </tr>
            <?php endforeach; ?>
        </table>
    </div>
    <?php endif; ?>

    <?php if (!empty($results['multiple_elements'])): ?>
    <h2>Fichiers avec plusieurs éléments (classes/interfaces/traits)</h2>
    <ul>
        <?php foreach ($results['multiple_elements'] as $file): ?>
        <li><?= htmlspecialchars($file) ?></li>
        <?php endforeach; ?>
    </ul>
    <?php endif; ?>

    <?php if (!empty($results['mixed_content'])): ?>
    <h2>Fichiers à contenu mixte (élément et fonctions globales)</h2>
    <ul>
        <?php foreach ($results['mixed_content'] as $file): ?>
        <li><?= htmlspecialchars($file) ?></li>
        <?php endforeach; ?>
    </ul>
    <?php endif; ?>

    <?php if (!empty($results['procedural'])): ?>
    <h2>Fichiers procéduraux</h2>
    <div class="table-container">
        <table class="valid">
            <tr>
                <th>Fichier</th>
                <th>Raison</th>
            </tr>
            <?php foreach ($results['procedural'] as $item): ?>
            <tr>
                <td><?= htmlspecialchars(is_array($item) ? $item['file'] : $item) ?></td>
                <td><?= htmlspecialchars(is_array($item) && isset($item['reason']) ? $item['reason'] : '') ?></td>
            </tr>
            <?php endforeach; ?>
        </table>
    </div>
    <?php endif; ?>

    <!-- Nouveau tableau : Liste des "use" par fichier -->
<?php if (!empty($results['use_statements'])): ?>
<h2>Liste des "use" nécessaires par fichier</h2>
<div class="table-container">
    <table>
        <tr>
            <th>Fichier</th>
            <th>Use statements à inclure</th>
        </tr>
        <?php foreach ($useStatementsNew as $file => $useList): ?>
        <tr>
            <td><?= htmlspecialchars($file) ?></td>
            <td>
                <?php if (!empty($useList)): ?>
                    <ul>
                        <?php foreach ($useList as $use): ?>
                        <li><?= htmlspecialchars($use) ?></li>
                        <?php endforeach; ?>
                    </ul>
                <?php else: ?>
                    Aucun
                <?php endif; ?>
            </td>
        </tr>
        <?php endforeach; ?>
    </table>
</div>
<?php endif; ?>

</body>
</html>

<?php
// ---------------------- Fonctions d'analyse et de traitement ----------------------

/**
 * Extrait les éléments autoloadables (classes, interfaces et traits) à partir des tokens.
 * On y détecte si la classe est abstraite et/ou si elle utilise "extends".
 * En cas d'extension, le nom du parent est extrait et, s'il correspond à une classe native PHP,
 * le type sera modifié en "Class (extends native)".
 */
function getElementsInfo($tokens) {
    $elements = [];
    $tokenCount = count($tokens);
    $typeMapping = [
        T_CLASS     => 'Class',
        T_INTERFACE => 'Interface',
        T_TRAIT     => 'Trait'
    ];
    for ($i = 0; $i < $tokenCount; $i++) {
        if (is_array($tokens[$i]) && in_array($tokens[$i][0], [T_CLASS, T_INTERFACE, T_TRAIT])) {
            $isAbstract = false;
            // Pour les classes, vérifier si le token précédent est T_ABSTRACT
            $j = $i - 1;
            while ($j >= 0) {
                if (is_array($tokens[$j])) {
                    if ($tokens[$j][0] === T_ABSTRACT) {
                        $isAbstract = true;
                        break;
                    } elseif (in_array($tokens[$j][0], [T_WHITESPACE, T_COMMENT, T_DOC_COMMENT])) {
                        $j--;
                        continue;
                    } else {
                        break;
                    }
                } else {
                    break;
                }
            }
            // Récupération du nom de l'élément
            $j = $i + 1;
            while ($j < $tokenCount && is_array($tokens[$j]) && $tokens[$j][0] === T_WHITESPACE) {
                $j++;
            }
            if ($j < $tokenCount && is_array($tokens[$j]) && $tokens[$j][0] === T_STRING) {
                $elementName = $tokens[$j][1];
            } else {
                $elementName = 'Inconnu';
            }
            // Pour les classes, détecter un éventuel "extends"
            $extends = false;
            $parentName = '';
            $k = $j + 1;
            while ($k < $tokenCount) {
                if (is_array($tokens[$k])) {
                    if ($tokens[$k][0] === T_EXTENDS) {
                        $extends = true;
                        // On récupère ensuite le nom de la classe parente
                        $k++;
                        while ($k < $tokenCount && is_array($tokens[$k]) && $tokens[$k][0] === T_WHITESPACE) {
                            $k++;
                        }
                        if ($k < $tokenCount && is_array($tokens[$k]) && $tokens[$k][0] === T_STRING) {
                            $parentName = $tokens[$k][1];
                        }
                        break;
                    }
                } elseif ($tokens[$k] === '{') {
                    break;
                }
                $k++;
            }
            // Détermination du type d'élément en fonction des informations recueillies
            $elementType = $typeMapping[$tokens[$i][0]];
            if ($tokens[$i][0] === T_CLASS) {
                if ($isAbstract) {
                    $elementType = 'Abstract Class';
                } elseif ($extends) {
                    // Si le parent est natif, on précise dans le type
                    if (isNativePhpClass($parentName)) {
                        $elementType = 'Class (extends native)';
                    } else {
                        $elementType = 'Class (extends)';
                    }
                } else {
                    $elementType = 'Class';
                }
            }
            $elements[] = [
                'name'   => $elementName,
                'type'   => $elementType,
                'parent' => $parentName
            ];
        }
    }
    return $elements;
}
// ==================== NOUVELLES FONCTIONS UTILITAIRES ====================
function findBestMatch($candidates, $currentNamespace) {
    // Priorité 1 : Même espace de noms
    foreach ($candidates as $candidate) {
        if (strpos($candidate, $currentNamespace) === 0) {
            return $candidate;
        }
    }

    // Priorité 2 : Plus court chemin
    usort($candidates, function($a, $b) {
        return substr_count($a, '\\') - substr_count($b, '\\');
    });

    return $candidates[0];
}

function generateAlias($fqcn, $originalName, &$imports) {
    $baseName = $originalName;
    $attempt = 1;

    while (in_array(strtolower($baseName), array_map('strtolower', array_merge(
        array_column($imports['classes'], 'alias'),
        array_column($imports['functions'], 'alias'),
        array_column($imports['constants'], 'alias')
    )))) {
        $parts = explode('\\', $fqcn);
        $baseName = $parts[count($parts)-2] . ucfirst($originalName);
        $baseName = preg_replace('/[^a-zA-Z0-9_]/', '', $baseName);
        $baseName .= $attempt++;
    }

    return $baseName;
}

/**
 * Analyse un fichier PHP et retourne les informations sur les éléments, le namespace et les fonctions globales.
 */
function analyzePhpFile($filePath) {
    $content = file_get_contents($filePath);
    $tokens = token_get_all($content);
    $elements = getElementsInfo($tokens);
    $namespace = getNamespace($tokens);
    $globalFunctions = getGlobalFunctionsFromTokens($tokens);
       // Détection des constantes
    $constants = [];
    $tokenCount = count($tokens);
    for ($i = 0; $i < $tokenCount; $i++) {
        if (is_array($tokens[$i]) && $tokens[$i][0] === T_CONST) {
            while ($i++ < $tokenCount) {
                if (is_array($tokens[$i]) && $tokens[$i][0] === T_STRING) {
                    $constants[] = $tokens[$i][1];
                    break;
                }
            }
        }
    }
    return [
        'elements' => $elements,
        'namespace' => $namespace,
        'element_count' => count($elements),
        'functions' => $globalFunctions,
        'function_count' => count($globalFunctions),
        'constants' => $constants,
        'constant_count' => count($constants)
    ];
}

/**
 * Vérifie la conformité PSR‑4 d'un fichier en comparant le chemin attendu et le chemin réel.
 */
function checkFilePsr4Compliance($analysis, $filePath) {
    $namespaceRoots = [
        'Common'     => realpath(__DIR__ . '/Common') . DIRECTORY_SEPARATOR,
        'Plugins' => realpath(__DIR__ . '/Plugins') . DIRECTORY_SEPARATOR
    ];

    $result = [
        'file' => $filePath,
        'is_valid' => true,
        'reason' => '',
        'expected_path' => '',
        'element' => null,
        'namespace' => $analysis['namespace']
    ];

    if ($analysis['element_count'] !== 1) {
        $result['is_valid'] = false;
        $result['reason'] = $analysis['element_count'] . ' éléments trouvés';
        return $result;
    }

    $element = $analysis['elements'][0];
    $result['element'] = $element;
    $elementName = $element['name'];

    $namespaceParts = explode('\\', $analysis['namespace']);
    $baseNamespace = array_shift($namespaceParts);

    if (!isset($namespaceRoots[$baseNamespace])) {
        $result['is_valid'] = false;
        $result['reason'] = 'Namespace non reconnu';
        return $result;
    }

    $expectedPath = rtrim($namespaceRoots[$baseNamespace] . implode(DIRECTORY_SEPARATOR, $namespaceParts), DIRECTORY_SEPARATOR)
                    . DIRECTORY_SEPARATOR . $elementName . '.php';
    $result['expected_path'] = $expectedPath;

    $realFilePath = realpath($filePath);
    if ($expectedPath !== $realFilePath) {
        $result['is_valid'] = false;
        $result['reason'] = 'Chemin incorrect';
    }

    return $result;
}

/**
 * Extrait le namespace déclaré dans un fichier à partir des tokens.
 */
function getNamespace($tokens) {
    $namespace = '';
    foreach ($tokens as $key => $token) {
        if (is_array($token) && $token[0] === T_NAMESPACE) {
            for ($i = $key + 1; $i < count($tokens); $i++) {
                if ($tokens[$i] === ';' || $tokens[$i] === '{') break;
                if (is_array($tokens[$i])) {
                    $namespace .= $tokens[$i][1];
                }
            }
            break;
        }
    }
    return trim($namespace);
}

/**
 * Extrait les fonctions globales (hors classes, interfaces ou traits) à partir des tokens.
 */
function getGlobalFunctionsFromTokens($tokens) {
    $functions = [];
    $inClass = false;
    $braceLevel = 0;
    $tokenCount = count($tokens);
    for ($i = 0; $i < $tokenCount; $i++) {
        $token = $tokens[$i];
        if (is_array($token)) {
            if ($token[0] === T_CLASS || $token[0] === T_INTERFACE || $token[0] === T_TRAIT) {
                $inClass = true;
            }
            if (!$inClass && $token[0] === T_FUNCTION) {
                $j = $i + 1;
                while ($j < $tokenCount && is_array($tokens[$j]) && $tokens[$j][0] === T_WHITESPACE) {
                    $j++;
                }
                if ($j < $tokenCount && $tokens[$j] === '&') {
                    $j++;
                    while ($j < $tokenCount && is_array($tokens[$j]) && $tokens[$j][0] === T_WHITESPACE) {
                        $j++;
                    }
                }
                if ($j < $tokenCount && is_array($tokens[$j]) && $tokens[$j][0] === T_STRING) {
                    $functions[] = $tokens[$j][1];
                }
            }
        } else {
            if ($token === '{') {
                $braceLevel++;
            } elseif ($token === '}') {
                $braceLevel--;
                if ($braceLevel === 0) {
                    $inClass = false;
                }
            }
        }
    }
    return $functions;
}

/**
 * Propose le namespace correct basé sur l'emplacement du fichier
 * ET convertit chaque segment en PascalCase (première lettre en majuscule).
 *
 * IMPORTANT : Le namespace proposé n'inclut PAS le nom du fichier.
 *
 * @param string $filePath Chemin absolu du fichier.
 * @return string Le namespace attendu ou une chaîne vide si non déterminé.
 */
function getExpectedNamespace($filePath) {
    $namespaceRoots = [
        'App'     => realpath(__DIR__ . '/Common') . DIRECTORY_SEPARATOR,
        'Plugins' => realpath(__DIR__ . '/Plugins') . DIRECTORY_SEPARATOR
    ];

    $realFilePath = realpath($filePath);
    $fileDir = dirname($realFilePath) . DIRECTORY_SEPARATOR;
    // Ne pas récupérer le nom du fichier car il correspond à la classe

    // Fonction anonyme pour convertir une chaîne en PascalCase.
    $toPascalCase = function($string) {
        if (strpos($string, '-') !== false || strpos($string, '_') !== false) {
            $parts = preg_split('/[-_]+/', $string);
            $converted = '';
            foreach ($parts as $part) {
                $converted .= ucfirst(strtolower($part));
            }
            return $converted;
        }
        return ucfirst($string);
    };

    foreach ($namespaceRoots as $baseNamespace => $basePath) {
        if (strpos($fileDir, $basePath) === 0) {
            $relativeDir = substr($fileDir, strlen($basePath));
            $relativeDir = trim($relativeDir, DIRECTORY_SEPARATOR);
            $namespaceParts = [$baseNamespace];
            if (!empty($relativeDir)) {
                $parts = explode(DIRECTORY_SEPARATOR, $relativeDir);
                foreach ($parts as $part) {
                    $namespaceParts[] = $toPascalCase($part);
                }
            }
            return implode('\\', $namespaceParts);
        }
    }

    return '';
}

function toPascalCase(string $name): string {
    return str_replace(['_', '-'], '', ucwords($name, '_-.'));
}

function toCamelCase(string $name): string {
    return lcfirst(toPascalCase($name));
}

?>