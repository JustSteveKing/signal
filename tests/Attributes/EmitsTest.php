<?php

declare(strict_types=1);

namespace JustSteveKing\Signal\Tests\Attributes;

use JustSteveKing\Signal\Attributes\Emits;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

#[CoversClass(Emits::class)]
final class EmitsTest extends TestCase
{
    #[Test]
    public function it_stores_an_event_name(): void
    {
        $attribute = new Emits(event: 'PaymentProcessed');

        $this->assertSame('PaymentProcessed', $attribute->event);
    }

    #[Test]
    public function it_defaults_to_empty_description(): void
    {
        $attribute = new Emits(event: 'PaymentProcessed');

        $this->assertSame('', $attribute->description);
    }

    #[Test]
    public function it_defaults_to_empty_tags(): void
    {
        $attribute = new Emits(event: 'PaymentProcessed');

        $this->assertSame([], $attribute->tags);
    }

    #[Test]
    public function it_stores_a_description(): void
    {
        $attribute = new Emits(event: 'PaymentProcessed', description: 'Fired when a payment is successful.');

        $this->assertSame('Fired when a payment is successful.', $attribute->description);
    }

    #[Test]
    public function it_stores_tags(): void
    {
        $attribute = new Emits(event: 'PaymentProcessed', tags: ['payments', 'async']);

        $this->assertSame(['payments', 'async'], $attribute->tags);
    }
}
