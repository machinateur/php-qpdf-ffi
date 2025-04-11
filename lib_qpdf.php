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

/**
 * This function runs QPDFJob from a job JSON file. See the "QPDF Job" section of the manual for
 *  details. The JSON string must be UTF8-encoded. It returns the error code that qpdf would
 *  return with the equivalent command-line invocation. Exit code values are defined in
 *  `Constants.h` in the `qpdf_exit_code_e` type.
 *
 * @see https://github.com/qpdf/qpdf/blob/11.9/include/qpdf/qpdfjob-c.h#L73
 * @see https://qpdf.readthedocs.io/en/latest/qpdf-job.html#qpdf-job
 *
 * @see \qpdf_exit_code_e                       (exit code constants)
 *
 * @param string $json                          The json content of a qpdf job file
 *
 * @return int<0, 3>                            The qpdf CLI exit code
 *
 * @throws \CompileError                        when the `LIB_QPDF_PATH` is not defined
 * @throws \InvalidArgumentException            when the json input is invalid
 */
function qpdfjob_run_from_json(string $json): int
{
    // On first call within thread this precondition has to be checked.
    static $warmup = true;
    if ($warmup && !\defined('LIB_QPDF_PATH')) {
        // qpdf 11.9 is expected in the path
        throw new \CompileError('Constant LIB_QPDF_PATH is not defined.');
    }
    $warmup = false;

    // Ensure json is encoded in valid UTF-8. This is an old trick to avoid a dependency on `ext-mbstring`.
    if (!\preg_match('!!u', $json)) {
        throw new \InvalidArgumentException('Malformed UTF-8 characters, possibly incorrectly encoded.', \JSON_ERROR_UTF8);
    }

    // Ensure the json is not empty. Also trim the input from whitespace characters.
    $json = \trim($json);
    if (empty($json)) {
        throw new InvalidArgumentException('Empty input.', \JSON_ERROR_UTF8);
    }

    // Ensure the json string is correctly formatted. The `json_validate()` is only available since PHP 8.3, so keep a fallback.
    if (\function_exists('json_validate') && !\json_validate($json)) {
        throw new \InvalidArgumentException('Syntax error.', \JSON_ERROR_SYNTAX);
    } else {
        try {
            \json_decode($json, true, 512, \JSON_OBJECT_AS_ARRAY | \JSON_THROW_ON_ERROR);
        } catch (\JsonException $e) {
            throw new \InvalidArgumentException($e->getMessage(), $e->getCode() ?: \json_last_error());
        }
    }

    // Declare static variable.
    static $qpdfffi;

    if (!isset($qpdfffi)) {
        // Bind the static variable to the FFI, if not set yet.
        $qpdfffi = FFI::cdef('int qpdfjob_run_from_json(char const* json);', LIB_QPDF_PATH);
    }

    /** @var qpdfffi $qpdfffi */

    // Call the method to process the job file.
    return $qpdfffi->qpdfjob_run_from_json($json);
}

if (false) {
    /**
     * Interface typehints for `$qpdfffi` {@see \FFI} instance in {@see \qpdfjob_run_from_json()}.
     *
     * @internal unused; only used for typehints proper
     */
    interface qpdfffi
    {
        /**
         * @see \qpdfjob_run_from_json()
         * @see \qpdf_exit_code_e
         */
        function qpdfjob_run_from_json(string $json): int;
    }
}

/**
 * Exit Codes from QPDFJob and the qpdf CLI.
 *
 * @see https://github.com/qpdf/qpdf/blob/11.9/include/qpdf/Constants.h#L72
 */
final class qpdf_exit_code_e
{
    /**
     * Empty constructor, to avoid instantiation.
     */
    private function __construct()
    {}

    /**
     * Normal exit codes (success)
     */
    const QPDF_EXIT_SUCCESS = 0;

    /**
     * Normal exit codes (error)
     */
    const QPDF_EXIT_ERROR = 2;

    /**
     * Normal exit codes (warning)
     */
    const QPDF_EXIT_WARNING = 3;

    /**
     * For `--is-encrypted` check
     */
    const QPDF_EXIT_IS_NOT_ENCRYPTED = 2;

    /**
     * For `--requires-password` check
     */
    const QPDF_EXIT_CORRECT_PASSWORD = 3;
}
