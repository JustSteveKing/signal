<?php

declare(strict_types=1);

namespace JustSteveKing\Signal\Tests\Fixtures;

use JustSteveKing\Signal\Attributes\ValueObject;

#[ValueObject(description: 'Represents a monetary amount with currency', tags: ['billing', 'ddd'])]
final class MoneyValueObject {}
