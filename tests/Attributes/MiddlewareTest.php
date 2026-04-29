<?php

declare(strict_types=1);

namespace JustSteveKing\Signal\Tests\Attributes;

use JustSteveKing\Signal\Attributes\Middleware;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

#[CoversClass(Middleware::class)]
final class MiddlewareTest extends TestCase
{
    #[Test]
    public function it_stores_a_description(): void
    {
        $attribute = new Middleware(description: 'Authenticates incoming requests');

        $this->assertSame('Authenticates incoming requests', $attribute->description);
    }

    #[Test]
    public function it_defaults_to_empty_tags(): void
    {
        $attribute = new Middleware(description: 'Authenticates incoming requests');

        $this->assertSame([], $attribute->tags);
    }

    #[Test]
    public function it_defaults_priority_to_zero(): void
    {
        $attribute = new Middleware(description: 'Authenticates incoming requests');

        $this->assertSame(0, $attribute->priority);
    }

    #[Test]
    public function it_stores_tags(): void
    {
        $attribute = new Middleware(description: 'Authenticates incoming requests', tags: ['auth', 'http']);

        $this->assertSame(['auth', 'http'], $attribute->tags);
    }

    #[Test]
    public function it_stores_priority(): void
    {
        $attribute = new Middleware(description: 'Authenticates incoming requests', priority: 10);

        $this->assertSame(10, $attribute->priority);
    }
}
