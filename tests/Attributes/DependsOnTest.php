<?php

declare(strict_types=1);

namespace JustSteveKing\Signal\Tests\Attributes;

use JustSteveKing\Signal\Attributes\DependsOn;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

#[CoversClass(DependsOn::class)]
final class DependsOnTest extends TestCase
{
    #[Test]
    public function it_stores_a_class_name(): void
    {
        $attribute = new DependsOn(class: 'App\Services\PaymentService');

        $this->assertSame('App\Services\PaymentService', $attribute->class);
    }

    #[Test]
    public function it_defaults_to_empty_description(): void
    {
        $attribute = new DependsOn(class: 'App\Services\PaymentService');

        $this->assertSame('', $attribute->description);
    }

    #[Test]
    public function it_stores_a_description(): void
    {
        $attribute = new DependsOn(
            class: 'App\Services\PaymentService',
            description: 'Used to charge the customer.',
        );

        $this->assertSame('Used to charge the customer.', $attribute->description);
    }
}
