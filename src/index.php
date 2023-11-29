<?php
require 'vendor/autoload.php';
$config = require 'config.php';


// Utilisation du routeur (si nécessaire)
$dispatcher = FastRoute\simpleDispatcher(function(FastRoute\RouteCollector $r) use ($config) {
    $r->addRoute('GET', '/folders', function() use ($config) {
        return getFoldersList($config, 3);
    });

    $r->addRoute('GET', '/images/{folderName:\w+}', function($vars) use ($config) {
        return getImagesFromFolderList($config, $vars['folderName']);
    });

    $r->addRoute('GET', '/swagger', function() {
        // header('Content-Type: application/yaml');
        readfile('./swagger.yaml');
        exit();
    });
});

// Récupérer la méthode HTTP et l'URI de la requête
$httpMethod = $_SERVER['REQUEST_METHOD'];
$uri = $_SERVER['REQUEST_URI'];

// Gestion des routes
$routeInfo = $dispatcher->dispatch($httpMethod, $uri);
switch ($routeInfo[0]) {
    case FastRoute\Dispatcher::NOT_FOUND:
        // 404 Not Found
        break;
    case FastRoute\Dispatcher::METHOD_NOT_ALLOWED:
        // 405 Method Not Allowed
        break;
    case FastRoute\Dispatcher::FOUND:
        $handler = $routeInfo[1];
        $vars = $routeInfo[2];
        // appeler la fonction de gestion
        call_user_func($handler, $vars);
        break;
}

// Méthode permettant de récupérer l'arbre des dossiers
function getFoldersList($config, $maxDepth) {
    $baseDir = $config['path']['optim'];

    if (!file_exists($baseDir) || !is_dir($baseDir) || !is_readable($baseDir)) {
        return [];
    }

    $tree = [];
    $queue = [[$baseDir, 0]]; // Queue avec des paires [chemin, profondeur]

    while (!empty($queue)) {
        list($currentDir, $currentDepth) = array_shift($queue);

        // Vérifier la profondeur
        if ($currentDepth >= $maxDepth) {
            continue;
        }

        // Ouvrir le répertoire
        $dirHandle = opendir($currentDir);
        if (!$dirHandle) {
            continue;
        }

        while (($item = readdir($dirHandle)) !== false) {
            // Ignorer les éléments '.' et '..'
            if ($item == '.' || $item == '..') {
                continue;
            }

            $path = $currentDir . DIRECTORY_SEPARATOR . $item;
            if (is_dir($path)) {
                // Ajouter le dossier à l'arbre
                $relativePath = substr($path, strlen($baseDir) + 1);
                $tree[] = $relativePath;

                // Ajouter les sous-dossiers à la queue
                array_push($queue, [$path, $currentDepth + 1]);
            }
        }

        closedir($dirHandle);
        sort($tree);
    }

    echo json_encode($tree);
}

function getImagesFromFolderList($config, $folderName) {
    if (!$folderName) {
        return;
    }

    $baseDir = $config['path']['optim'] . '/' . $folderName;

    $images = [];

    // Vérifie si le dossier existe et peut être lu
    if (file_exists($baseDir) && is_dir($baseDir) && is_readable($baseDir)) {
        // Ouvre le dossier
        $dirHandle = opendir($baseDir);

        if ($dirHandle) {
            while (($file = readdir($dirHandle)) !== false) {
                // Ignore les dossiers '.' et '..'
                if ($file != '.' && $file != '..') {
                    // Construit le chemin complet du fichier
                    $filePath = $baseDir . DIRECTORY_SEPARATOR . $file;

                    // Vérifie si c'est un fichier et si l'extension correspond à une image
                    if (is_file($filePath) && preg_match('/\.(jpg|jpeg|png|gif|svg|webp)$/i', $file)) {
                        $images[] = $filePath;
                    }
                }
            }
            closedir($dirHandle);
        }
    }

    sort($images);

    echo json_encode($images);
}