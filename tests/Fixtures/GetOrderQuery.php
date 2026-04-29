<?php

declare(strict_types=1);

namespace JustSteveKing\Signal\Tests\Fixtures;

use JustSteveKing\Signal\Attributes\Query;

#[Query(description: 'Retrieves a single order by ID', tags: ['orders', 'cqrs'])]
final class GetOrderQuery {}
