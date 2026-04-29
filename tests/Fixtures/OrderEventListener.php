<?php

declare(strict_types=1);

namespace JustSteveKing\Signal\Tests\Fixtures;

use JustSteveKing\Signal\Attributes\Listener;
use JustSteveKing\Signal\Attributes\ListensTo;

#[Listener(description: 'Handles order lifecycle events', tags: ['orders'])]
#[ListensTo(event: 'OrderPlaced', description: 'Triggers post-placement workflow.')]
#[ListensTo(event: 'OrderCancelled', description: 'Cleans up after cancellation.')]
final class OrderEventListener {}
