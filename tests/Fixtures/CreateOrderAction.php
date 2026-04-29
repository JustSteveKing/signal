<?php

declare(strict_types=1);

namespace JustSteveKing\Signal\Tests\Fixtures;

use JustSteveKing\Signal\Attributes\Action;
use JustSteveKing\Signal\Attributes\Emits;
use JustSteveKing\Signal\Attributes\SideEffect;

#[Action(description: 'Creates a new order and triggers fulfilment', tags: ['orders'])]
final class CreateOrderAction
{
    #[Emits(event: 'OrderCreated', description: 'Fired when the order is persisted.')]
    #[SideEffect(description: 'Sends a confirmation email to the customer.')]
    public function handle(): void {}
}
