<?php

declare(strict_types=1);

namespace JustSteveKing\Signal\Config;

use RuntimeException;

final readonly class ConfigLoader
{
    public function load(string $path): Config
    {
        if ( ! file_exists($path)) {
            throw new RuntimeException(
                message: "Signal config file not found at [{$path}].",
            );
        }

        $contents = file_get_contents($path);

        if (false === $contents) {
            throw new RuntimeException(
                message: "Unable to read Signal config file at [{$path}].",
            );
        }

        $data = json_decode(
            json: $contents,
            associative: true,
            flags: JSON_THROW_ON_ERROR,
        );

        if ( ! is_array($data)) {
            throw new RuntimeException(
                message: "Signal config file at [{$path}] did not decode to an object.",
            );
        }

        /** @var array<string, mixed> $data */
        $data = $data;

        return $this->validate($data);
    }

    /**
     * @param array<string, mixed> $data
     */
    private function validate(array $data): Config
    {
        $input = $data['input'] ?? null;

        if ( ! is_string($input) || '' === $input) {
            throw new RuntimeException(
                message: 'Signal config is missing required field [input].',
            );
        }

        $output = $data['output'] ?? null;

        if ( ! is_array($output)) {
            throw new RuntimeException(
                message: 'Signal config is missing required field [output.path].',
            );
        }

        $outputPath = $output['path'] ?? null;

        if ( ! is_string($outputPath) || '' === $outputPath) {
            throw new RuntimeException(
                message: 'Signal config is missing required field [output.path].',
            );
        }

        $outputFormat = $output['format'] ?? null;

        if (null === $outputFormat || '' === $outputFormat || [] === $outputFormat) {
            throw new RuntimeException(
                message: 'Signal config is missing required field [output.format].',
            );
        }

        $formats = is_array($outputFormat) ? array_values($outputFormat) : [$outputFormat];

        foreach ($formats as $format) {
            if ( ! is_string($format)) {
                throw new RuntimeException(
                    message: 'Unsupported output format type. Supported formats are: markdown, json.',
                );
            }

            if ( ! in_array($format, ['markdown', 'json'], strict: true)) {
                throw new RuntimeException(
                    message: "Unsupported output format [{$format}]. Supported formats are: markdown, json.",
                );
            }
        }

        $exclude = $data['exclude'] ?? [];

        if ( ! is_array($exclude)) {
            throw new RuntimeException(
                message: 'Signal config field [exclude] must be an array.',
            );
        }

        foreach ($exclude as $excludedPath) {
            if ( ! is_string($excludedPath)) {
                throw new RuntimeException(
                    message: 'Signal config field [exclude] must only contain strings.',
                );
            }
        }

        /** @var list<'markdown'|'json'> $formats */
        $formats = $formats;

        /** @var list<string> $exclude */
        $exclude = array_values($exclude);

        return new Config(
            input: $input,
            outputPath: $outputPath,
            formats: $formats,
            exclude: $exclude,
        );
    }
}
