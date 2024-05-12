<?php
// Secure autoload with appropriate error handling.
require 'vendor/autoload.php';

use FastRoute\RouteCollector;
use Symfony\Component\Filesystem\Filesystem;

// Load configuration safely, considering it returns an array or throws an error.
$config = json_decode(file_get_contents('config.php'), true);

$dispatcher = FastRoute\simpleDispatcher(function (RouteCollector $r) use ($config) {
    $r->addRoute('GET', '/folders', function () use ($config) {
        return getFoldersList($config, 3);
    });

    $r->addRoute('GET', '/images/{folderName:\w+}', function ($vars) use ($config) {
        return getImagesFromFolderList($config, $vars['folderName']);
    });

    $r->addRoute('GET', '/swagger', function () {
        // Safer way to send headers and content.
        sendYamlContent('./swagger.yaml');
    });
});

// Check HTTP method and URI using a safer approach.
$httpMethod = isset($_SERVER['REQUEST_METHOD']) ? stripslashes($_SERVER['REQUEST_METHOD']) : 'GET';
$uri = isset($_SERVER['REQUEST_URI']) ? stripslashes($_SERVER['REQUEST_URI']) : '/';

$routeInfo = $dispatcher->dispatch($httpMethod, $uri);
switch ($routeInfo[0]) {
    case FastRoute\Dispatcher::NOT_FOUND:
        // Handle 404 error appropriately.
        http_response_code(404);
        break;
    case FastRoute\Dispatcher::METHOD_NOT_ALLOWED:
        // Handle 405 error properly.
        http_response_code(405);
        break;
    case FastRoute\Dispatcher::FOUND:
        $handler = $routeInfo[1];
        $vars = $routeInfo[2];
        // Directly invoke the handler.
        $handler($vars);
        break;
}
;

// Alternative to reading files safely and sending content.
function sendYamlContent($filepath)
{
    if (stream_resolve_include_path($filepath) === true) {
        header('Content-Type: application/yaml');
        readfile($filepath);
    } else {
        http_response_code(404);
    }
    ;
}
;

// Adjusted method to retrieve the folder tree.
function getFoldersList($config, $maxDepth)
{
    $filesystem = new Filesystem();
    $baseDir = $config['path']['optim'];

    if ($filesystem->exists($baseDir) === false || is_dir($baseDir) === false || (fileperms($baseDir) & 0x0100) === false) {
        return [];
    }
    ;

    $tree = [];
    $queue = [
        [$baseDir, 0]
    ];

    while (empty($queue) === false) {
        list($currentDir, $currentDepth) = array_shift($queue);

        if ($currentDepth >= $maxDepth)
            continue;

        $dir = new DirectoryIterator($currentDir);
        foreach ($dir as $item) {
            if ($item->isDot())
                continue;

            $path = $currentDir . DIRECTORY_SEPARATOR . $item->getFilename();
            if ($item->isDir()) {
                $relativePath = substr($path, (strlen($baseDir) + 1));
                $tree[] = $relativePath;
                array_push($queue, [$path, ($currentDepth + 1)]);
            }
            ;
        }
        ;
        sort($tree);
    }
    ;

    echo json_encode($tree);
}
;

// Adjusted method for retrieving images contained in a folder
function getImagesFromFolderList($config, $folderName)
{
    if (!$folderName)
        return;

    $filesystem = new Filesystem();
    $baseDir = $config['path']['optim'] . '/' . $folderName;
    $images = [];

    if ($filesystem->exists($baseDir) && is_dir($baseDir) && (fileperms($baseDir) & 0x0100)) {
        $dir = new DirectoryIterator($baseDir);
        foreach ($dir as $file) {
            if ($file->isDot())
                continue;

            $filePath = $baseDir . DIRECTORY_SEPARATOR . $file->getFilename();
            if ($file->isFile() && preg_match('/\.(jpg|jpeg|png|gif|svg|webp)$/i', $file->getFilename())) {
                $images[] = $filePath;
            }
            ;
        }
        ;
    }
    ;

    sort($images);
    echo json_encode($images);
}
;
