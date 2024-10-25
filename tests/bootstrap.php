<?php
/*
 * MIT License
 *
 * Copyright (c) 2024 machinateur
 *
 * Permission is hereby granted, free of charge, to any person obtaining a copy
 * of this software and associated documentation files (the "Software"), to deal
 * in the Software without restriction, including without limitation the rights
 * to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
 * copies of the Software, and to permit persons to whom the Software is
 * furnished to do so, subject to the following conditions:
 *
 * The above copyright notice and this permission notice shall be included in all
 * copies or substantial portions of the Software.
 *
 * THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
 * IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
 * FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
 * AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
 * LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
 * OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN THE
 * SOFTWARE.
 */

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

\define('LIB_QPDF_PATH', $path);

// Has to be included after the constant is defined.
require \dirname(__DIR__) . '/vendor/autoload.php';
