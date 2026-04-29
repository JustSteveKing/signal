<?php

declare(strict_types=1);

namespace JustSteveKing\Signal\Config;

final readonly class Config
{
    /**
     * @param list<'markdown'|'json'> $formats
     * @param list<string> $exclude
     */
    public function __construct(
        public string $input,
        public string $outputPath,
        public array $formats,
        public array $exclude = [],
    ) {}
}
