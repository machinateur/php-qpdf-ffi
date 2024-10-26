<?php

declare(strict_types=1);

require_once \dirname(__DIR__) . '/vendor/symfony/process/ExecutableFinder.php';

use Symfony\Component\Process\ExecutableFinder;

$executablePath = (new ExecutableFinder())
    ->find('qpdf');

if (!$executablePath) {
    throw new \RuntimeException('Could not find "qpdf" binary.');
}

$path =  $executablePath;
while (\is_link($path)) {
    $linkPath = @\readlink($path);

    if ($linkPath) {
        $linkPath = \dirname($linkPath);
    }

    // Relative link.
    if ($linkPath && '/' !== $linkPath[0]) {
        $path = \dirname($path) . '/' . $linkPath;

        continue;
    }

    // Absolute link or unresolved (null).
    $path = $linkPath;
}

if (!$path) {
    throw new \RuntimeException(\sprintf('Could not resolve "qpdf" binary symlink (from "%s").', $executablePath));
}

// windows = .dll; linux = .so; mac = .dylib
$path = \dirname($path) . '/lib/libqpdf.dylib';

if (!\is_file($path)) {
    throw new \RuntimeException(\sprintf('Unable to locate "libqpdf" library (tried "%s").', $path));
}

if (\defined('LIB_QPDF_PATH')) {
    die(LIB_QPDF_PATH);
}
\define('LIB_QPDF_PATH', $path);
