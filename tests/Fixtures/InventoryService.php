<?php

declare(strict_types=1);

namespace JustSteveKing\Signal\Tests\Fixtures;

use JustSteveKing\Signal\Attributes\Service;

#[Service(description: 'Manages stock levels and inventory reservation', tags: ['inventory'])]
final class InventoryService {}
