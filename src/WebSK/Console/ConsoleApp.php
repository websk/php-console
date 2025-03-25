<?php

namespace WebSK\Console;

use GetOpt\Arguments;
use GetOpt\Command;
use GetOpt\GetOpt;
use Psr\Container\ContainerInterface;
use Psr\Http\Message\ServerRequestInterface;
use Slim\App;
use Slim\Psr7\Factory\ResponseFactory;

/**
 * Class ConsoleApp
 * @package VitrinaTV\Console
 */
class ConsoleApp extends App
{
    protected GetOpt $get_opt;

    /**
     * ConsoleApp constructor.
     * @param ContainerInterface $container
     */
    public function __construct(ContainerInterface $container)
    {
        if (PHP_SAPI !== 'cli') {
            throw new \Exception(
                'Only cli application!'
            );
        }

        parent::__construct(new ResponseFactory(), $container);

        $this->get_opt = new GetOpt();
    }

    /**
     * @param ServerRequestInterface|null $request
     * @throws \Exception
     */
    public function run(?ServerRequestInterface $request = null): void
    {
        throw new \Exception(
            'use execute()'
        );
    }

    /**
     * @param array|string|Arguments|null $arguments
     */
    public function execute($arguments = null): void
    {
        $this->get_opt->process($arguments);

        $command = $this->get_opt->getCommand();
        if (!$command) {
            echo $this->get_opt->getHelpText();
            exit;
        }

        call_user_func($command->getHandler(), $this->get_opt);
    }

    /**
     * @param Command $command
     */
    public function addCommand(Command $command): void
    {
        $this->get_opt->addCommand($command);
    }
}
