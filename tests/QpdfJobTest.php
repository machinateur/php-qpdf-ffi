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

namespace Machinateur\Qpdf\Tests;

use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

/**
 * A test case verifying the example from {@see https://qpdf.readthedocs.io/en/latest/qpdf-job.html#qpdf-job the docs}.
 */
class QpdfJobTest extends TestCase
{
    use ExtensionLoadedBehaviour;
    use PdfComparisonBehaviour;

    public static function setUpBeforeClass(): void
    {
        @\unlink(__DIR__ . '/res/outfile.pdf');
    }

    /**
     * @return \Generator<string|null>
     */
    public static function provideJson(): \Generator
    {
        yield \file_get_contents(__DIR__ . '/res/example.json') ?: '';
    }

    /**
     * @dataProvider provideJson
     */
    public function testRunJobFromJson(string $json): void
    {
        // The string must not be empty.
        self::assertNotEmpty($json, 'File not found');

        // Successful invocation returns 0.
        self::assertSame(\qpdf_exit_code_e::QPDF_EXIT_SUCCESS, \qpdfjob_run_from_json($json));

        // Decode data to pull some information for assertions below.
        $data = \json_decode($json, true);
        // Compare the outfile to the control file.
        $diff = self::getDiff(__DIR__ . '/res/outfile_control.pdf', $data['outputFile']);

        // The diff has to stay in bounds of `int<0, 5>`.
        self::assertGreaterThanOrEqual(0, $diff);
        self::assertLessThanOrEqual(5, $diff);
    }
}
