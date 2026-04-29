<?php

declare(strict_types=1);

namespace JustSteveKing\Signal\Tests\Attributes;

use JustSteveKing\Signal\Attributes\Action;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

#[CoversClass(Action::class)]
final class ActionTest extends TestCase
{
    #[Test]
    public function it_stores_a_description(): void
    {
        $attribute = new Action(description: 'Creates a new order');

        $this->assertSame('Creates a new order', $attribute->description);
    }

    #[Test]
    public function it_defaults_to_empty_tags(): void
    {
        $attribute = new Action(description: 'Creates a new order');

        $this->assertSame([], $attribute->tags);
    }

    #[Test]
    public function it_stores_tags(): void
    {
        $attribute = new Action(description: 'Creates a new order', tags: ['orders', 'write']);

        $this->assertSame(['orders', 'write'], $attribute->tags);
    }
}
