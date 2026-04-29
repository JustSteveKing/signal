<?php

declare(strict_types=1);

namespace JustSteveKing\Signal\Tests\Attributes;

use JustSteveKing\Signal\Attributes\Event;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

#[CoversClass(Event::class)]
final class EventTest extends TestCase
{
    #[Test]
    public function it_stores_a_description(): void
    {
        $attribute = new Event(description: 'Fired when an order is placed');

        $this->assertSame('Fired when an order is placed', $attribute->description);
    }

    #[Test]
    public function it_defaults_to_empty_tags(): void
    {
        $attribute = new Event(description: 'Fired when an order is placed');

        $this->assertSame([], $attribute->tags);
    }

    #[Test]
    public function it_stores_tags(): void
    {
        $attribute = new Event(description: 'Fired when an order is placed', tags: ['orders', 'events']);

        $this->assertSame(['orders', 'events'], $attribute->tags);
    }
}
