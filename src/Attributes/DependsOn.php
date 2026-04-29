<?php

declare(strict_types=1);

namespace JustSteveKing\Signal\Attributes;

use Attribute;

#[Attribute(Attribute::TARGET_CLASS | Attribute::IS_REPEATABLE)]
final readonly class DependsOn
{
    public function __construct(
        public string $class,
        public string $description = '',
    ) {}
}
