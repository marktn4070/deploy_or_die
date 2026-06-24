<?php
declare(strict_types=1);

// Headers
header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization');

$replaceFrom = 'https://127.0.0.1';

$protocol = (empty($_SERVER['HTTPS']) ? 'http' : 'https');
$host = $_SERVER['HTTP_HOST'];
$replaceTo = $protocol . '://' . $host;

$jsonPath = __DIR__ . '/../json/workoder.json';
$jsonContent = file_get_contents($jsonPath);

$jsonReplaced = str_replace($replaceFrom, $replaceTo, $jsonContent);

echo $jsonReplaced;
exit;
