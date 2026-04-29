<?php

declare(strict_types=1);

namespace JustSteveKing\Signal\Tests\Attributes;

use JustSteveKing\Signal\Attributes\Cached;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

#[CoversClass(Cached::class)]
final class CachedTest extends TestCase
{
    #[Test]
    public function it_defaults_ttl_to_3600(): void
    {
        $attribute = new Cached();

        $this->assertSame(3600, $attribute->ttl);
    }

    #[Test]
    public function it_defaults_to_empty_key(): void
    {
        $attribute = new Cached();

        $this->assertSame('', $attribute->key);
    }

    #[Test]
    public function it_defaults_to_empty_description(): void
    {
        $attribute = new Cached();

        $this->assertSame('', $attribute->description);
    }

    #[Test]
    public function it_stores_a_custom_ttl(): void
    {
        $attribute = new Cached(ttl: 300);

        $this->assertSame(300, $attribute->ttl);
    }

    #[Test]
    public function it_stores_a_cache_key(): void
    {
        $attribute = new Cached(key: 'orders.index');

        $this->assertSame('orders.index', $attribute->key);
    }

    #[Test]
    public function it_stores_a_description(): void
    {
        $attribute = new Cached(description: 'Cached for performance.');

        $this->assertSame('Cached for performance.', $attribute->description);
    }
}
