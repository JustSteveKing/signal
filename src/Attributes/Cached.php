<?php

declare(strict_types=1);

namespace JustSteveKing\Signal\Attributes;

use Attribute;

#[Attribute(Attribute::TARGET_METHOD)]
final readonly class Cached
{
    public function __construct(
        public int $ttl = 3600,
        public string $key = '',
        public string $description = '',
    ) {}
}
