<?php

declare(strict_types=1);

namespace JustSteveKing\Signal\Console;

use JustSteveKing\Signal\Commands\GenerateCommand;
use Symfony\Component\Console\Application as BaseApplication;

final class Application extends BaseApplication
{
    private const string NAME = 'Signal';
    private const string VERSION = '1.0.0';

    public function __construct()
    {
        parent::__construct(
            name: self::NAME,
            version: self::VERSION,
        );

        $this->addCommand(new GenerateCommand());
        $this->setDefaultCommand('generate');
    }
}
