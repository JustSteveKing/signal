<?php

declare(strict_types=1);

namespace JustSteveKing\Signal\Tests\Attributes;

use JustSteveKing\Signal\Attributes\Controller;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

#[CoversClass(Controller::class)]
final class ControllerTest extends TestCase
{
    #[Test]
    public function it_stores_a_description(): void
    {
        $attribute = new Controller(description: 'Handles order HTTP endpoints');

        $this->assertSame('Handles order HTTP endpoints', $attribute->description);
    }

    #[Test]
    public function it_defaults_to_empty_tags(): void
    {
        $attribute = new Controller(description: 'Handles order HTTP endpoints');

        $this->assertSame([], $attribute->tags);
    }

    #[Test]
    public function it_stores_tags(): void
    {
        $attribute = new Controller(description: 'Handles order HTTP endpoints', tags: ['orders', 'http']);

        $this->assertSame(['orders', 'http'], $attribute->tags);
    }
}
