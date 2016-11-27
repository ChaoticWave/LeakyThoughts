<?php
define('LARAVEL_START', microtime(true));

require __DIR__ . '/../vendor/autoload.php';

/** @noinspection PhpIncludeInspection */
file_exists($compiledPath = __DIR__ . '/cache/compiled.php') and require $compiledPath;
