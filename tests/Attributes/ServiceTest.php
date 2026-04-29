<?php

declare(strict_types=1);

namespace JustSteveKing\Signal\Tests\Attributes;

use JustSteveKing\Signal\Attributes\Service;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

#[CoversClass(Service::class)]
final class ServiceTest extends TestCase
{
    #[Test]
    public function it_stores_a_description(): void
    {
        $attribute = new Service(description: 'Test service');

        $this->assertSame('Test service', $attribute->description);
    }

    #[Test]
    public function it_defaults_to_empty_tags(): void
    {
        $attribute = new Service(description: 'Test service');

        $this->assertSame([], $attribute->tags);
    }

    #[Test]
    public function it_stores_tags(): void
    {
        $attribute = new Service(description: 'Test service', tags: ['orders']);

        $this->assertSame(['orders'], $attribute->tags);
    }
}
