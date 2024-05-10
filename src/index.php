<?php
require 'vendor/autoload.php';
$config = require 'config.php';

// Using the router (if necessary)
$dispatcher = FastRoute\simpleDispatcher(function(FastRoute\RouteCollector $r) use ($config) {
    $r->addRoute('GET', '/folders', function() use ($config) {
        return getFoldersList($config, 3);
    });

    $r->addRoute('GET', '/images/{folderName:\w+}', function($vars) use ($config) {
        return getImagesFromFolderList($config, $vars['folderName']);
    });

    $r->addRoute('GET', '/swagger', function() {
        header('Content-Type: application/yaml');
        readfile('./swagger.yaml');
        exit();
    });
});

// Retrieve the HTTP method and URI of the request
$httpMethod = $_SERVER['REQUEST_METHOD'];
$uri = $_SERVER['REQUEST_URI'];

// Route handling
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
        // call the handler function
        call_user_func($handler, $vars);
        break;
}

// Method to retrieve the folder tree
function getFoldersList($config, $maxDepth) {
    $baseDir = $config['path']['optim'];

    if (!file_exists($baseDir) || !is_dir($baseDir) || !is_readable($baseDir)) {
        return [];
    }

    $tree = [];
    $queue = [[$baseDir, 0]]; // Queue with pairs [path, depth]

    while (!empty($queue)) {
        list($currentDir, $currentDepth) = array_shift($queue);

        // Check the depth
        if ($currentDepth >= $maxDepth) {
            continue;
        }

        // Open the directory
        $dirHandle = opendir($currentDir);
        if (!$dirHandle) {
            continue;
        }

        while (($item = readdir($dirHandle)) !== false) {
            // Ignore the '.' and '..' items
            if ($item == '.' || $item == '..') {
                continue;
            }

            $path = $currentDir . DIRECTORY_SEPARATOR . $item;
            if (is_dir($path)) {
                // Add the folder to the tree
                $relativePath = substr($path, strlen($baseDir) + 1);
                $tree[] = $relativePath;

                // Add subfolders to the queue
                array_push($queue, [$path, $currentDepth + 1]);
            }
        }

        closedir($dirHandle);
        sort($tree);
    }

    echo json_encode($tree);
}

// Method for retrieving images contained in a folder
function getImagesFromFolderList($config, $folderName) {
    if (!$folderName) {
        return;
    }

    $baseDir = $config['path']['optim'] . '/' . $folderName;

    $images = [];

    // Check if the folder exists and can be read
    if (file_exists($baseDir) && is_dir($baseDir) && is_readable($baseDir)) {
        // Open the folder
        $dirHandle = opendir($baseDir);

        if ($dirHandle) {
            while (($file = readdir($dirHandle)) !== false) {
                // Ignore the '.' and '..' folders
                if ($file != '.' && $file != '..') {
                    // Build the complete file path
                    $filePath = $baseDir . DIRECTORY_SEPARATOR . $file;

                    // Check if it's a file and if the extension matches an image
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
