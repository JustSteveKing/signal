<?php

declare(strict_types=1);

namespace JustSteveKing\Signal\Tests\Attributes;

use JustSteveKing\Signal\Attributes\Deprecated;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

#[CoversClass(Deprecated::class)]
final class DeprecatedTest extends TestCase
{
    #[Test]
    public function it_stores_a_reason(): void
    {
        $attribute = new Deprecated(reason: 'Use NewService instead.');

        $this->assertSame('Use NewService instead.', $attribute->reason);
    }

    #[Test]
    public function it_defaults_to_empty_since(): void
    {
        $attribute = new Deprecated(reason: 'Use NewService instead.');

        $this->assertSame('', $attribute->since);
    }

    #[Test]
    public function it_stores_a_since_version(): void
    {
        $attribute = new Deprecated(reason: 'Use NewService instead.', since: '2.0.0');

        $this->assertSame('2.0.0', $attribute->since);
    }
}
