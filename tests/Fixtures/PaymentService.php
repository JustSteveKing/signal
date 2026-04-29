<?php

declare(strict_types=1);

namespace JustSteveKing\Signal\Tests\Fixtures;

use JustSteveKing\Signal\Attributes\Emits;
use JustSteveKing\Signal\Attributes\Service;
use JustSteveKing\Signal\Attributes\SideEffect;
use JustSteveKing\Signal\Attributes\Throws;

#[Service(description: 'Handles payment processing', tags: ['payments'])]
final class PaymentService
{
    #[Emits(event: 'PaymentProcessed', description: 'Fired when a payment is successful.')]
    #[Emits(event: 'PaymentFailed', description: 'Fired when a payment is declined.')]
    #[SideEffect(description: 'Charges the customer payment method.')]
    #[SideEffect(description: 'Logs the transaction to the audit trail.')]
    #[Throws(exception: 'PaymentFailedException', description: 'When the gateway rejects the charge.')]
    #[Throws(exception: 'InvalidCardException', description: 'When the card details are invalid.')]
    public function charge(): void {}
}
