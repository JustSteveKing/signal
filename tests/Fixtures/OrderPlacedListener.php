<?php

declare(strict_types=1);

namespace JustSteveKing\Signal\Tests\Fixtures;

use JustSteveKing\Signal\Attributes\ListensTo;

#[ListensTo(event: 'OrderCreated', description: 'Sends confirmation email to the customer.')]
#[ListensTo(event: 'OrderUpdated', description: 'Notifies the customer of changes.')]
final class OrderPlacedListener {}
