<?php

declare(strict_types=1);

namespace JustSteveKing\Signal\Tests\Attributes;

use JustSteveKing\Signal\Attributes\Query;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

#[CoversClass(Query::class)]
final class QueryTest extends TestCase
{
    #[Test]
    public function it_stores_a_description(): void
    {
        $attribute = new Query(description: 'Retrieves an order by ID');

        $this->assertSame('Retrieves an order by ID', $attribute->description);
    }

    #[Test]
    public function it_defaults_to_empty_tags(): void
    {
        $attribute = new Query(description: 'Retrieves an order by ID');

        $this->assertSame([], $attribute->tags);
    }

    #[Test]
    public function it_stores_tags(): void
    {
        $attribute = new Query(description: 'Retrieves an order by ID', tags: ['orders', 'cqrs']);

        $this->assertSame(['orders', 'cqrs'], $attribute->tags);
    }
}
