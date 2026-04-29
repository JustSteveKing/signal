<?php

declare(strict_types=1);

namespace JustSteveKing\Signal\Tests\Fixtures;

use JustSteveKing\Signal\Attributes\Authorize;
use JustSteveKing\Signal\Attributes\Cached;
use JustSteveKing\Signal\Attributes\Controller;
use JustSteveKing\Signal\Attributes\Route;
use JustSteveKing\Signal\Attributes\Validates;

#[Controller(description: 'API endpoints for order management', tags: ['orders', 'api'])]
final class ApiOrderController
{
    #[Route(method: 'GET', path: '/api/orders', description: 'List all orders.')]
    #[Authorize(ability: 'orders.viewAny', description: 'User must be able to view orders.')]
    #[Cached(ttl: 300, key: 'orders.index', description: 'Cached for 5 minutes.')]
    public function index(): void {}

    #[Route(method: 'POST', path: '/api/orders', description: 'Create a new order.')]
    #[Authorize(ability: 'orders.create', description: 'User must be able to create orders.')]
    #[Validates(field: 'customer_id', rules: 'required|integer', description: 'The customer placing the order.')]
    #[Validates(field: 'items', rules: 'required|array|min:1', description: 'The order line items.')]
    public function store(): void {}
}
