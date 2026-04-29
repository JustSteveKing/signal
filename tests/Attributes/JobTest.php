<?php

declare(strict_types=1);

namespace JustSteveKing\Signal\Tests\Attributes;

use JustSteveKing\Signal\Attributes\Job;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

#[CoversClass(Job::class)]
final class JobTest extends TestCase
{
    #[Test]
    public function it_stores_a_description(): void
    {
        $attribute = new Job(description: 'Sends invoice email to customer');

        $this->assertSame('Sends invoice email to customer', $attribute->description);
    }

    #[Test]
    public function it_defaults_to_empty_tags(): void
    {
        $attribute = new Job(description: 'Sends invoice email to customer');

        $this->assertSame([], $attribute->tags);
    }

    #[Test]
    public function it_defaults_queue_to_default(): void
    {
        $attribute = new Job(description: 'Sends invoice email to customer');

        $this->assertSame('default', $attribute->queue);
    }

    #[Test]
    public function it_stores_tags(): void
    {
        $attribute = new Job(description: 'Sends invoice email to customer', tags: ['billing', 'email']);

        $this->assertSame(['billing', 'email'], $attribute->tags);
    }

    #[Test]
    public function it_stores_a_queue_name(): void
    {
        $attribute = new Job(description: 'Sends invoice email to customer', queue: 'invoices');

        $this->assertSame('invoices', $attribute->queue);
    }
}
