<?php

declare(strict_types=1);

namespace JustSteveKing\Signal\Tests\Attributes;

use JustSteveKing\Signal\Attributes\Validates;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

#[CoversClass(Validates::class)]
final class ValidatesTest extends TestCase
{
    #[Test]
    public function it_stores_a_field_name(): void
    {
        $attribute = new Validates(field: 'customer_id');

        $this->assertSame('customer_id', $attribute->field);
    }

    #[Test]
    public function it_defaults_to_empty_rules(): void
    {
        $attribute = new Validates(field: 'customer_id');

        $this->assertSame('', $attribute->rules);
    }

    #[Test]
    public function it_defaults_to_empty_description(): void
    {
        $attribute = new Validates(field: 'customer_id');

        $this->assertSame('', $attribute->description);
    }

    #[Test]
    public function it_stores_rules(): void
    {
        $attribute = new Validates(field: 'customer_id', rules: 'required|integer');

        $this->assertSame('required|integer', $attribute->rules);
    }

    #[Test]
    public function it_stores_a_description(): void
    {
        $attribute = new Validates(field: 'customer_id', description: 'The customer placing the order.');

        $this->assertSame('The customer placing the order.', $attribute->description);
    }
}
