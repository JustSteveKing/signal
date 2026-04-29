<?php

declare(strict_types=1);

namespace JustSteveKing\Signal\Tests\Fixtures;

use JustSteveKing\Signal\Attributes\DependsOn;
use JustSteveKing\Signal\Attributes\Service;

#[Service(description: 'Handles order processing and fulfilment', tags: ['orders', 'fulfilment'])]
#[DependsOn(class: PaymentService::class, description: 'Used to charge the customer.')]
#[DependsOn(class: InventoryService::class, description: 'Used to reserve stock.')]
final class OrderService {}
