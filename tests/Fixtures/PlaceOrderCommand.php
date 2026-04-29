<?php

declare(strict_types=1);

namespace JustSteveKing\Signal\Tests\Fixtures;

use JustSteveKing\Signal\Attributes\Command;

#[Command(description: 'Command to place a new customer order', tags: ['orders', 'cqrs'])]
final class PlaceOrderCommand {}
