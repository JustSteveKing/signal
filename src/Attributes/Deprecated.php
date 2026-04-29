<?php

declare(strict_types=1);

namespace JustSteveKing\Signal\Attributes;

use Attribute;

#[Attribute(Attribute::TARGET_CLASS | Attribute::TARGET_METHOD)]
final readonly class Deprecated
{
    public function __construct(
        public string $reason,
        public string $since = '',
    ) {}
}
