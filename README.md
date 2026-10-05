# WebSK php-console

`websk/php-console` connects [GetOpt.PHP](https://github.com/getopt-php/getopt-php)
commands with a Slim application and a PSR-11 container.

## Requirements

- PHP 8.3 or newer, including PHP 8.5
- Composer 2
- `ext-json` and `ext-mbstring`

## Installation

```shell
composer require websk/php-console
```

## Usage

Create a PSR-11 container, register commands, and call `execute()` with CLI
arguments. Passing no arguments to `execute()` uses `$_SERVER['argv']`.

```php
<?php

use GetOpt\Command;
use GetOpt\GetOpt;
use Psr\Container\ContainerInterface;
use WebSK\Console\ConsoleApp;

require __DIR__ . '/vendor/autoload.php';

$container = new class implements ContainerInterface {
    public function get(string $id): mixed
    {
        throw new InvalidArgumentException("Unknown service: {$id}");
    }

    public function has(string $id): bool
    {
        return false;
    }
};

$app = new ConsoleApp($container);
$app->addCommand(new Command(
    'hello',
    static function (GetOpt $getOpt): void {
        echo "Hello!\n";
    }
));

$app->execute();
```

Run it with:

```shell
php console.php hello
```

`ConsoleApp::run()` is intentionally disabled because this package is for CLI
applications. Use `ConsoleApp::execute()` instead.

## Development

Install all dependencies and run the complete validation suite:

```shell
composer install
composer check
composer validate --strict
composer check-platform-reqs
composer audit --locked
```

The `composer check` command runs PHP lint, PHPUnit, and PHPStan level 8.

## License

This package is available under the [MIT License](LICENSE).
