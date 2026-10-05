<?php

declare(strict_types=1);

namespace WebSK\Console\Tests;

use Exception;
use GetOpt\Arguments;
use GetOpt\Command;
use GetOpt\GetOpt;
use InvalidArgumentException;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use Psr\Container\ContainerInterface;
use RuntimeException;
use UnexpectedValueException;
use WebSK\Console\ConsoleApp;

final class ConsoleAppTest extends TestCase
{
    public function testCanBeCreatedInCliEnvironment(): void
    {
        self::assertInstanceOf(ConsoleApp::class, $this->createApp());
    }

    public function testRunDirectsCallerToExecute(): void
    {
        $app = $this->createApp();

        $this->expectException(Exception::class);
        $this->expectExceptionMessage('use execute()');

        $app->run();
    }

    /**
     * @param array<string>|string|Arguments $arguments
     */
    #[DataProvider('argumentsProvider')]
    public function testExecutesRegisteredCommand(array|string|Arguments $arguments): void
    {
        $app = $this->createApp();
        $receivedGetOpt = null;

        $app->addCommand(new Command(
            'probe',
            static function (GetOpt $getOpt) use (&$receivedGetOpt): void {
                $receivedGetOpt = $getOpt;
            }
        ));

        $app->execute($arguments);

        self::assertInstanceOf(GetOpt::class, $receivedGetOpt);
    }

    /**
     * @return iterable<string, array{array<string>|string|Arguments}>
     */
    public static function argumentsProvider(): iterable
    {
        yield 'array' => [['probe']];
        yield 'string' => ['probe'];
        yield 'Arguments object' => [new Arguments(['probe'])];
    }

    public function testPropagatesCommandHandlerException(): void
    {
        $app = $this->createApp();
        $app->addCommand(new Command(
            'fail',
            static function (): void {
                throw new RuntimeException('handler failed');
            }
        ));

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('handler failed');

        $app->execute(['fail']);
    }

    public function testRejectsNonCallableCommandHandler(): void
    {
        $app = $this->createApp();
        $app->addCommand(new Command('invalid', 'not_a_callable'));

        $this->expectException(UnexpectedValueException::class);
        $this->expectExceptionMessage('Command handler must be callable');

        $app->execute(['invalid']);
    }

    public function testPrintsHelpAndExitsWhenCommandIsMissing(): void
    {
        $command = escapeshellarg(PHP_BINARY)
            . ' -d error_reporting=E_ALL -d display_errors=1 '
            . escapeshellarg(__DIR__ . '/Fixtures/no-command.php');

        $pipes = [];
        $process = proc_open(
            $command,
            [
                1 => ['pipe', 'w'],
                2 => ['pipe', 'w'],
            ],
            $pipes
        );

        self::assertIsResource($process);

        $stdout = stream_get_contents($pipes[1]);
        $stderr = stream_get_contents($pipes[2]);
        fclose($pipes[1]);
        fclose($pipes[2]);
        $exitCode = proc_close($process);

        self::assertSame(0, $exitCode, $stderr === false ? '' : $stderr);
        self::assertIsString($stdout);
        self::assertStringContainsString('Usage:', $stdout);
        self::assertSame('', $stderr);
    }

    private function createApp(): ConsoleApp
    {
        $container = new class implements ContainerInterface {
            public function get(string $id): mixed
            {
                throw new InvalidArgumentException(sprintf('Unknown service: %s', $id));
            }

            public function has(string $id): bool
            {
                return false;
            }
        };

        return new ConsoleApp($container);
    }
}
