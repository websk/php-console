<?php

namespace WebSK\Console;

use GetOpt\Arguments;
use GetOpt\Command;
use GetOpt\GetOpt;
use Slim\App;

/**
 * Class ConsoleApp
 * @package VitrinaTV\Console
 */
class ConsoleApp extends App
{
    protected GetOpt $get_opt;

    /**
     * ConsoleApp constructor.
     * @param array $config
     */
    public function __construct(array $config = [])
    {
        if (PHP_SAPI !== 'cli') {
            throw new \Exception(
                'Only cli application!'
            );
        }

        parent::__construct($config);

        $this->get_opt = new GetOpt();
    }

    /**
     * @param bool $silent
     * @throws \Exception
     */
    public function run($silent = false)
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
    public function addCommand(Command $command)
    {
        $this->get_opt->addCommand($command);
    }
}
