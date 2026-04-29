<?php

declare(strict_types=1);

namespace JustSteveKing\Signal\Attributes;

use Attribute;

#[Attribute(Attribute::TARGET_CLASS)]
final readonly class Middleware
{
    /**
     * @param list<string> $tags
     */
    public function __construct(
        public string $description,
        public array $tags = [],
        public int $priority = 0,
    ) {}
}
