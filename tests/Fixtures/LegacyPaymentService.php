<?php

declare(strict_types=1);

namespace JustSteveKing\Signal\Tests\Fixtures;

use JustSteveKing\Signal\Attributes\Deprecated;
use JustSteveKing\Signal\Attributes\Internal;
use JustSteveKing\Signal\Attributes\Service;

#[Service(description: 'Legacy payment processing service')]
#[Deprecated(reason: 'Use PaymentService instead.', since: '1.5.0')]
#[Internal(reason: 'Kept for backwards compatibility only.')]
final class LegacyPaymentService {}
