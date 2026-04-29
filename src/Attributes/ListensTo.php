<?php

declare(strict_types=1);

namespace JustSteveKing\Signal\Attributes;

use Attribute;

#[Attribute(Attribute::TARGET_CLASS | Attribute::IS_REPEATABLE)]
final readonly class ListensTo
{
    /**
     * @param list<string> $tags
     */
    public function __construct(
        public string $event,
        public string $description = '',
        public array $tags = [],
    ) {}
}
