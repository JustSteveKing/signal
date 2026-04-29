<?php

declare(strict_types=1);

namespace JustSteveKing\Signal\Commands;

use JustSteveKing\Signal\Config\ConfigLoader;
use JustSteveKing\Signal\Data\ClassDefinition;
use JustSteveKing\Signal\Output\JsonOutput;
use JustSteveKing\Signal\Output\MarkdownOutput;
use JustSteveKing\Signal\Reflector\Reflector;
use JustSteveKing\Signal\Scanner\Scanner;
use RuntimeException;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand(
    name: 'generate',
    description: 'Generate documentation from Signal attributes.',
)]
final class GenerateCommand extends Command
{
    private const string DEFAULT_CONFIG = 'signal.json';

    protected function configure(): void
    {
        $this->addOption(
            name: 'config',
            shortcut: 'c',
            mode: InputOption::VALUE_OPTIONAL,
            description: 'Path to the Signal config file.',
            default: self::DEFAULT_CONFIG,
        );
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        $io->title('Signal - Documentation Generator');

        $configPath = $input->getOption('config');

        if ( ! is_string($configPath)) {
            throw new RuntimeException('Signal config option [config] must be a string.');
        }

        $io->text("Loading config from [{$configPath}].");

        try {
            $config = (new ConfigLoader())->load($configPath);
        } catch (RuntimeException $e) {
            $io->error($e->getMessage());

            return Command::FAILURE;
        }

        $io->text("Scanning [{$config->input}] for Signal attributes.");

        $files = (new Scanner())->scan(
            path: $config->input,
            exclude: $config->exclude,
        );

        $io->text('Found ' . count($files) . ' PHP files.');

        $reflector = new Reflector();

        /** @var list<ClassDefinition> $definitions */
        $definitions = [];

        foreach ($files as $file) {
            $definition = $reflector->reflect($file);

            if (null !== $definition) {
                $definitions[] = $definition;
            }
        }

        $io->text('Resolved ' . count($definitions) . ' annotated classes.');

        if (empty($definitions)) {
            $io->warning('No Signal attributes found. Nothing to generate.');

            return Command::SUCCESS;
        }

        if ( ! is_dir($config->outputPath)) {
            mkdir($config->outputPath, 0755, recursive: true);
        }

        foreach ($config->formats as $format) {
            match ($format) {
                'markdown' => $this->writeMarkdown($io, $definitions, $config->outputPath),
                'json' => $this->writeJson($io, $definitions, $config->outputPath),
            };
        }

        $io->success('Documentation generated successfully.');

        return Command::SUCCESS;
    }

    /**
     * @param list<ClassDefinition> $definitions
     */
    private function writeMarkdown(SymfonyStyle $io, array $definitions, string $outputPath): void
    {
        $content = (new MarkdownOutput())->generate($definitions);
        $path = mb_rtrim($outputPath, '/') . '/signal.md';

        file_put_contents($path, $content);

        $io->text("Markdown written to [{$path}].");
    }

    /**
     * @param list<ClassDefinition> $definitions
     */
    private function writeJson(SymfonyStyle $io, array $definitions, string $outputPath): void
    {
        $content = (new JsonOutput())->generate($definitions);
        $path = mb_rtrim($outputPath, '/') . '/signal.json';

        file_put_contents($path, $content);

        $io->text("JSON written to [{$path}].");
    }
}
