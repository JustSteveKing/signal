<?php

declare(strict_types=1);

namespace JustSteveKing\Signal\Tests\Attributes;

use JustSteveKing\Signal\Attributes\Internal;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

#[CoversClass(Internal::class)]
final class InternalTest extends TestCase
{
    #[Test]
    public function it_stores_a_reason(): void
    {
        $attribute = new Internal(reason: 'Kept for backwards compatibility only.');

        $this->assertSame('Kept for backwards compatibility only.', $attribute->reason);
    }
}
