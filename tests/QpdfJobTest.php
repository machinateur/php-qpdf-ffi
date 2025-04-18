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

    /**
     * All outfile files should be removed before the test runs.
     *  To make things easy, use the same outfile for every test.
     */
    public function setUp(): void
    {
        @\unlink(__DIR__ . '/res/outfile.pdf');
    }

    /**
     * @phpstan-type _dataset array<string, array{'json': string}>
     *
     * @return array<_dataset>
     */
    public static function provideJson(): array
    {
        static $dataset;

        if (!isset($dataset)) {
            /**
             * @param string $filename
             * @return _dataset
             */
            $dataset = static function (string $filename): array {
                $filepath = \sprintf(__DIR__ . '/res/%s', $filename);

                return [$filename => [$filepath, 'json' => \file_get_contents($filepath) ?: '']];
            };
        }

        // Declare all the datasets.
        // In future versions this can be done using spread operator, but in 7.4 the function is needed.
        return \array_merge(
            $dataset('example.json'),
            // ... more files
        );
    }

    /**
     * @dataProvider provideJson
     */
    public function testRunJobFromJson(string $file, string $json): void
    {
        self::assertFileExists($file, \sprintf('File not found (%s)', $file));

        // The string must not be empty and valid json.
        self::assertNotEmpty($json, sprintf('File content empty (%s)', $file));
        self::assertJson($json, \sprintf('File content invalid (%s)', $file));

        // Successful invocation returns 0. Please note that paths in the json have to be relative to `\getcwd()` or absolute.
        self::assertSame(\qpdf_exit_code_e::QPDF_EXIT_SUCCESS, \qpdfjob_run_from_json($json), 'Unexpected exit code');

        // Compare the outfile to the control file.
        $diff = self::getDiff($file, $json);

        // The diff has to stay in bounds of `int<0, 5>`.
        self::assertGreaterThanOrEqual(0, $diff, 'Diff value below threshold');
        self::assertLessThanOrEqual(5, $diff, 'Diff value above threshold');
    }
}
