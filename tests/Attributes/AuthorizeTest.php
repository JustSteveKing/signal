<?php

declare(strict_types=1);

namespace JustSteveKing\Signal\Tests\Attributes;

use JustSteveKing\Signal\Attributes\Authorize;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

#[CoversClass(Authorize::class)]
final class AuthorizeTest extends TestCase
{
    #[Test]
    public function it_stores_an_ability(): void
    {
        $attribute = new Authorize(ability: 'orders.create');

        $this->assertSame('orders.create', $attribute->ability);
    }

    #[Test]
    public function it_defaults_to_empty_description(): void
    {
        $attribute = new Authorize(ability: 'orders.create');

        $this->assertSame('', $attribute->description);
    }

    #[Test]
    public function it_stores_a_description(): void
    {
        $attribute = new Authorize(ability: 'orders.create', description: 'User must be able to create orders.');

        $this->assertSame('User must be able to create orders.', $attribute->description);
    }
}
