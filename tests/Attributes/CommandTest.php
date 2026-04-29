<?php

declare(strict_types=1);

namespace JustSteveKing\Signal\Tests\Attributes;

use JustSteveKing\Signal\Attributes\Command;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

#[CoversClass(Command::class)]
final class CommandTest extends TestCase
{
    #[Test]
    public function it_stores_a_description(): void
    {
        $attribute = new Command(description: 'Places a new customer order');

        $this->assertSame('Places a new customer order', $attribute->description);
    }

    #[Test]
    public function it_defaults_to_empty_tags(): void
    {
        $attribute = new Command(description: 'Places a new customer order');

        $this->assertSame([], $attribute->tags);
    }

    #[Test]
    public function it_stores_tags(): void
    {
        $attribute = new Command(description: 'Places a new customer order', tags: ['orders', 'cqrs']);

        $this->assertSame(['orders', 'cqrs'], $attribute->tags);
    }
}
