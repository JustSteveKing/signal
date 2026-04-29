<?php

declare(strict_types=1);

namespace JustSteveKing\Signal\Attributes;

use Attribute;

#[Attribute(Attribute::TARGET_METHOD)]
final readonly class Route
{
    /**
     * @param list<string> $tags
     */
    public function __construct(
        public string $method,
        public string $path,
        public string $description = '',
        public array $tags = [],
    ) {}
}
