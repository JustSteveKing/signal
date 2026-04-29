<?php

declare(strict_types=1);

namespace JustSteveKing\Signal\Attributes;

use Attribute;

#[Attribute(Attribute::TARGET_METHOD | Attribute::IS_REPEATABLE)]
final readonly class Authorize
{
    public function __construct(
        public string $ability,
        public string $description = '',
    ) {}
}
