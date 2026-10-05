<?php

declare(strict_types=1);

use Psr\Container\ContainerInterface;
use WebSK\Console\ConsoleApp;

require dirname(__DIR__, 2) . '/vendor/autoload.php';

$container = new class implements ContainerInterface {
    public function get(string $id): mixed
    {
        throw new \InvalidArgumentException(sprintf('Unknown service: %s', $id));
    }

    public function has(string $id): bool
    {
        return false;
    }
};

$app = new ConsoleApp($container);
$app->execute([]);
