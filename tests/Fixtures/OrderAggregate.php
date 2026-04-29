<?php

declare(strict_types=1);

namespace JustSteveKing\Signal\Tests\Fixtures;

use JustSteveKing\Signal\Attributes\Aggregate;

#[Aggregate(description: 'Order aggregate root managing order lifecycle', tags: ['orders', 'ddd'])]
final class OrderAggregate {}
