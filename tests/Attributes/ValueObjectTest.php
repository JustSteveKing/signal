<?php

declare(strict_types=1);

namespace JustSteveKing\Signal\Tests\Attributes;

use JustSteveKing\Signal\Attributes\ValueObject;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

#[CoversClass(ValueObject::class)]
final class ValueObjectTest extends TestCase
{
    #[Test]
    public function it_stores_a_description(): void
    {
        $attribute = new ValueObject(description: 'Represents a monetary amount');

        $this->assertSame('Represents a monetary amount', $attribute->description);
    }

    #[Test]
    public function it_defaults_to_empty_tags(): void
    {
        $attribute = new ValueObject(description: 'Represents a monetary amount');

        $this->assertSame([], $attribute->tags);
    }

    #[Test]
    public function it_stores_tags(): void
    {
        $attribute = new ValueObject(description: 'Represents a monetary amount', tags: ['billing', 'ddd']);

        $this->assertSame(['billing', 'ddd'], $attribute->tags);
    }
}
