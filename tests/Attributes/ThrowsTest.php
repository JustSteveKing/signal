<?php

declare(strict_types=1);

namespace JustSteveKing\Signal\Tests\Attributes;

use JustSteveKing\Signal\Attributes\Throws;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

#[CoversClass(Throws::class)]
final class ThrowsTest extends TestCase
{
    #[Test]
    public function it_stores_an_exception_class(): void
    {
        $attribute = new Throws(exception: 'PaymentFailedException');

        $this->assertSame('PaymentFailedException', $attribute->exception);
    }

    #[Test]
    public function it_defaults_to_empty_description(): void
    {
        $attribute = new Throws(exception: 'PaymentFailedException');

        $this->assertSame('', $attribute->description);
    }

    #[Test]
    public function it_stores_a_description(): void
    {
        $attribute = new Throws(
            exception: 'PaymentFailedException',
            description: 'When the gateway rejects the charge.',
        );

        $this->assertSame('When the gateway rejects the charge.', $attribute->description);
    }
}
