<?php

declare(strict_types=1);

namespace JustSteveKing\Signal\Attributes;

use Attribute;

#[Attribute(Attribute::TARGET_CLASS)]
final readonly class Internal
{
    public function __construct(
        public string $reason,
    ) {}
}
