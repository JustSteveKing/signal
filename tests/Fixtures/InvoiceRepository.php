<?php

declare(strict_types=1);

namespace JustSteveKing\Signal\Tests\Fixtures;

use JustSteveKing\Signal\Attributes\Repository;

#[Repository(description: 'Handles invoice persistence and retrieval', tags: ['invoices'])]
final class InvoiceRepository {}
