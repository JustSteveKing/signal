<?php

declare(strict_types=1);

namespace JustSteveKing\Signal\Attributes;

use Attribute;

#[Attribute(Attribute::TARGET_METHOD | Attribute::IS_REPEATABLE)]
final readonly class SideEffect
{
    /**
     * @param list<string> $tags
     */
    public function __construct(
        public string $description,
        public array $tags = [],
    ) {}
}
