<?php

declare(strict_types=1);

namespace JustSteveKing\Signal\Tests\Attributes;

use JustSteveKing\Signal\Attributes\Module;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

#[CoversClass(Module::class)]
final class ModuleTest extends TestCase
{
    #[Test]
    public function it_stores_a_description(): void
    {
        $attribute = new Module(description: 'Test module');

        $this->assertSame('Test module', $attribute->description);
    }

    #[Test]
    public function it_defaults_to_empty_tags(): void
    {
        $attribute = new Module(description: 'Test module');

        $this->assertSame([], $attribute->tags);
    }

    #[Test]
    public function it_stores_tags(): void
    {
        $attribute = new Module(description: 'Test module', tags: ['billing', 'invoices']);

        $this->assertSame(['billing', 'invoices'], $attribute->tags);
    }
}
