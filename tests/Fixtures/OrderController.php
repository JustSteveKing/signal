<?php

declare(strict_types=1);

namespace JustSteveKing\Signal\Tests\Fixtures;

use JustSteveKing\Signal\Attributes\Controller;
use JustSteveKing\Signal\Attributes\Deprecated;
use JustSteveKing\Signal\Attributes\Route;
use JustSteveKing\Signal\Attributes\Throws;

#[Controller(description: 'Handles order HTTP endpoints', tags: ['orders'])]
final class OrderController
{
    #[Route(method: 'POST', path: '/orders', description: 'Create a new order.')]
    #[Throws(exception: 'ValidationException', description: 'When the request payload is invalid.')]
    public function store(): void {}

    #[Route(method: 'GET', path: '/orders/{id}', description: 'Retrieve a single order.')]
    #[Deprecated(reason: 'Use OrderV2Controller instead.', since: '2.0.0')]
    public function show(): void {}
}
