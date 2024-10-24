# php-qpdf-ffi

> __WORK IN PROGRESS / PROOF OF CONCEPT__

A really simple PHP integration for [qpdf](https://github.com/qpdf/qpdf) leveraging its JSON job file functionality
 with PHP-FFI to C.

The name really says it all.

## Requirements

- At least PHP `>=7.4` is required
  - Extensions `ext-json` and `ext-ffi` are required
- A compatible `qpdf` binary, version `11.9`
  - Some earlier versions might also work, as long as the C API is untouched

The PHP-FFI extension is supported since PHP version 7.4,
 so that's why it is the minimum PHP version this library supports
 and will support indefinitely.

## Installation

```bash
composer require machinateur/php-qpdf-ffi
```

The path to the C library should be specified by means of the `LIB_QPDF_PATH` constant.
 If not defined, a default value of `qpdf.so.11.9` will be used.

### Where to get the `qpdf` binaries

Since qpdf is a separate C library, the binaries are not included here (yet).
 The [Apache 2 license](http://www.apache.org/licenses/LICENSE-2.0) allows redistribution.

> https://github.com/qpdf/qpdf/blob/11.9/README-what-to-download.md

## Advantages

Use the full set of features provided by qpdf JSON job files directly from within PHP,
 without the need to write any C glue code for a custom PHP extension.

No need `\exec()` shenanigans. The PHP-FFI layer handles the direct interaction.
 This library only integrates one single function, therefor very little can break.

The JSON format can be dynamically generated based on the requirements of your application.
 As JSON is supported in a wide range of programming languages, the input does not necessarily have to come from PHP. 

> https://qpdf.readthedocs.io/en/latest/qpdf-job.html#qpdf-job

## Usage

```php
\define('LIB_QPDF_PATH', '/path/to/your/qpdf');

require_once __DIR__ . '/vendor/autoload.php';

$result = \qpdfjob_run_from_json(
    \file_get_contents(__DIR__ . '/example.json')
);

switch ($result)
{
    case \qpdf_exit_code_e::QPDF_EXIT_SUCCESS:
        echo 'Success';
        return;
    case \qpdf_exit_code_e::QPDF_EXIT_WARNING:
    case \qpdf_exit_code_e::QPDF_EXIT_IS_NOT_ENCRYPTED:
        echo 'Warning / IS_NOT_ENCRYPTED';
        return;
    case \qpdf_exit_code_e::QPDF_EXIT_ERROR:
    case \qpdf_exit_code_e::QPDF_EXIT_CORRECT_PASSWORD:
        echo 'Error   / CORRECT_PASSWORD';
        return;
}
```

## License

It's MIT.
