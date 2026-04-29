<?php

declare(strict_types=1);

namespace JustSteveKing\Signal\Tests\Fixtures;

use JustSteveKing\Signal\Attributes\Job;

#[Job(description: 'Sends an invoice PDF to the customer via email', tags: ['billing'], queue: 'invoices')]
final class SendInvoiceJob {}
