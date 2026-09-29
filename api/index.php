<?php
$projectRoot = dirname(__DIR__);
chdir($projectRoot);

$requestPath = parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH);
$relativePath = ltrim(is_string($requestPath) ? rawurldecode($requestPath) : '/', '/');
if ($relativePath === '') {
    $relativePath = 'index.php';
} elseif (is_dir($projectRoot . DIRECTORY_SEPARATOR . $relativePath)) {
    $relativePath = rtrim($relativePath, '/') . '/index.php';
}

$pagePath = realpath($projectRoot . DIRECTORY_SEPARATOR . $relativePath);
$adminRoot = realpath($projectRoot . DIRECTORY_SEPARATOR . 'admin');
$isPage = $pagePath
    && pathinfo($pagePath, PATHINFO_EXTENSION) === 'php'
    && (dirname($pagePath) === $projectRoot || dirname($pagePath) === $adminRoot);

if (!$isPage) {
    http_response_code(404);
    $pagePath = $projectRoot . DIRECTORY_SEPARATOR . '404.php';
}

$_SERVER['PHP_SELF'] = '/' . ltrim(str_replace($projectRoot, '', $pagePath), DIRECTORY_SEPARATOR);
$_SERVER['SCRIPT_NAME'] = $_SERVER['PHP_SELF'];
$_SERVER['SCRIPT_FILENAME'] = $pagePath;
require $pagePath;