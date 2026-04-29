<?php

declare(strict_types=1);

namespace JustSteveKing\Signal\Tests\Fixtures;

use JustSteveKing\Signal\Attributes\Event;

#[Event(description: 'Fired when an order is successfully placed', tags: ['orders', 'events'])]
final class OrderPlacedEvent {}
