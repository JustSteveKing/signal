# Signal

**Signal** is a PHP library that turns PHP attributes into living documentation. Annotate your classes and methods with Signal's attributes, then run a single command to generate Markdown and JSON docs that always reflect your actual code.

No more docs that drift. No more manually maintained API references. Signal reads your annotations and writes the documentation for you.

---

## Requirements

- PHP 8.5+
- Symfony Console

---

## Installation

```bash
composer require juststeveking/signal
```

---

## Quick Start

**1. Create a `signal.json` config at your project root:**

```json
{
    "input": "src/",
    "output": {
        "format": ["markdown", "json"],
        "path": "docs/"
    },
    "exclude": [
        "src/Attributes/"
    ]
}
```

**2. Annotate your classes:**

```php
use JustSteveKing\Signal\Attributes\Service;
use JustSteveKing\Signal\Attributes\Deprecated;
use JustSteveKing\Signal\Attributes\DependsOn;
use JustSteveKing\Signal\Attributes\Route;
use JustSteveKing\Signal\Attributes\Authorize;
use JustSteveKing\Signal\Attributes\Throws;

#[Service(
    description: 'Handles order processing and payment workflows',
    tags: ['orders', 'payments'],
)]
#[DependsOn(class: PaymentGateway::class, description: 'Required for processing payments')]
#[DependsOn(class: OrderRepository::class)]
final class OrderService
{
    #[Route(method: 'POST', path: '/orders', description: 'Create a new order')]
    #[Authorize(ability: 'orders.create')]
    #[Validates(field: 'items', rules: 'required|array|min:1')]
    #[Validates(field: 'payment_method', rules: 'required|string')]
    #[Emits(event: 'OrderPlaced', description: 'Fired after successful order creation')]
    #[Throws(exception: PaymentFailedException::class, description: 'When payment processing fails')]
    public function store(Request $request): OrderResource
    {
        // ...
    }
}
```

**3. Generate documentation:**

```bash
php vendor/bin/signal generate
```

Signal scans your `src/` directory, reflects on every annotated class, and writes `docs/signal.md` and `docs/signal.json`.

---

## Configuration

| Key | Type | Description |
|-----|------|-------------|
| `input` | `string` | Directory to scan (relative to config file) |
| `output.format` | `string[]` | Output formats: `markdown`, `json`, or both |
| `output.path` | `string` | Directory to write generated files |
| `exclude` | `string[]` | Paths to skip during scanning |

---

## Attributes

Signal provides 24 attributes split into three groups: **class type** attributes that identify what a class is, **class metadata** attributes that describe relationships and status, and **method** attributes that document individual methods.

---

### Class Type Attributes

These attributes appear on a class declaration and tell Signal what kind of class it is. Each accepts a `description` string and a `tags` array.

#### `#[Module]`

Top-level application module grouping related functionality.

```php
use JustSteveKing\Signal\Attributes\Module;

#[Module(description: 'Handles everything related to billing', tags: ['billing'])]
final class BillingModule {}
```

#### `#[Service]`

Application or domain service containing business logic.

```php
use JustSteveKing\Signal\Attributes\Service;

#[Service(description: 'Processes subscription renewals', tags: ['subscriptions', 'billing'])]
final class SubscriptionRenewalService {}
```

#### `#[Repository]`

Data access layer class that wraps persistence.

```php
use JustSteveKing\Signal\Attributes\Repository;

#[Repository(description: 'Reads and writes Order records', tags: ['orders', 'persistence'])]
final class OrderRepository {}
```

#### `#[Action]`

Single-purpose action class (use case / interactor).

```php
use JustSteveKing\Signal\Attributes\Action;

#[Action(description: 'Places a new customer order', tags: ['orders'])]
final class PlaceOrderAction {}
```

#### `#[Controller]`

HTTP controller that handles incoming requests.

```php
use JustSteveKing\Signal\Attributes\Controller;

#[Controller(description: 'Manages order CRUD endpoints', tags: ['orders', 'api'])]
final class OrderController {}
```

#### `#[Event]`

Domain event representing something that happened.

```php
use JustSteveKing\Signal\Attributes\Event;

#[Event(description: 'Fired when a customer places an order', tags: ['orders', 'events'])]
final class OrderPlacedEvent {}
```

#### `#[Listener]`

Event listener that reacts to one or more domain events.

```php
use JustSteveKing\Signal\Attributes\Listener;
use JustSteveKing\Signal\Attributes\ListensTo;

#[Listener(description: 'Sends a confirmation email after order is placed', tags: ['orders', 'email'])]
#[ListensTo(event: 'OrderPlaced', description: 'Triggers the confirmation email')]
final class SendOrderConfirmationListener {}
```

#### `#[Middleware]`

HTTP middleware with an optional execution priority.

```php
use JustSteveKing\Signal\Attributes\Middleware;

#[Middleware(description: 'Validates the Bearer token on every request', tags: ['auth'], priority: 10)]
final class AuthMiddleware {}
```

The `priority` integer controls rendering order in the generated docs (lower = earlier).

#### `#[Job]`

Queueable background job with an optional target queue name.

```php
use JustSteveKing\Signal\Attributes\Job;

#[Job(description: 'Sends the customer invoice PDF via email', tags: ['billing'], queue: 'invoices')]
final class SendInvoiceJob {}
```

#### `#[Command]`

Console / CLI command.

```php
use JustSteveKing\Signal\Attributes\Command;

#[Command(description: 'Recalculates all open subscription invoices', tags: ['billing', 'cli'])]
final class RecalculateInvoicesCommand {}
```

#### `#[Query]`

Read-only query class (CQRS query side).

```php
use JustSteveKing\Signal\Attributes\Query;

#[Query(description: 'Fetches a paginated list of orders for the current user', tags: ['orders', 'cqrs'])]
final class GetUserOrdersQuery {}
```

#### `#[Aggregate]`

DDD aggregate root that owns a consistency boundary.

```php
use JustSteveKing\Signal\Attributes\Aggregate;

#[Aggregate(description: 'Order aggregate root managing the full order lifecycle', tags: ['orders', 'ddd'])]
final class OrderAggregate {}
```

#### `#[ValueObject]`

DDD value object — immutable, identity-less.

```php
use JustSteveKing\Signal\Attributes\ValueObject;

#[ValueObject(description: 'Represents a monetary amount with currency', tags: ['money', 'ddd'])]
final class Money {}
```

---

### Class Metadata Attributes

These attributes provide additional context about a class — its dependencies, the events it handles, and whether it's deprecated or internal-only.

#### `#[DependsOn]` _(repeatable)_

Declares an explicit dependency on another class.

```php
use JustSteveKing\Signal\Attributes\Service;
use JustSteveKing\Signal\Attributes\DependsOn;

#[Service(description: 'Handles payment processing')]
#[DependsOn(class: StripeGateway::class, description: 'Primary payment provider')]
#[DependsOn(class: FraudDetectionService::class)]
final class PaymentService {}
```

#### `#[ListensTo]` _(repeatable)_

Declares which domain events a listener class handles.

```php
use JustSteveKing\Signal\Attributes\Listener;
use JustSteveKing\Signal\Attributes\ListensTo;

#[Listener(description: 'Handles post-order events')]
#[ListensTo(event: 'OrderPlaced', description: 'Sends confirmation email', tags: ['email'])]
#[ListensTo(event: 'OrderCancelled', description: 'Sends cancellation notice', tags: ['email'])]
final class OrderNotificationListener {}
```

#### `#[Deprecated]`

Marks a class or method as deprecated with an optional version and reason.

```php
use JustSteveKing\Signal\Attributes\Service;
use JustSteveKing\Signal\Attributes\Deprecated;

#[Service(description: 'Legacy order sync service')]
#[Deprecated(reason: 'Replaced by OrderSyncV2Service', since: '2.0.0')]
final class OrderSyncService {}
```

`#[Deprecated]` works on methods too:

```php
#[Deprecated(reason: 'Use createOrder() instead', since: '1.5.0')]
public function placeOrder(array $data): Order {}
```

#### `#[Internal]`

Marks a class or method as internal — not part of the public API.

```php
use JustSteveKing\Signal\Attributes\Internal;

#[Internal(reason: 'Framework bootstrap only — do not use directly')]
final class KernelBootstrapper {}
```

---

### Method Attributes

These attributes live on individual methods and document the HTTP layer, access control, validation, caching, events, exceptions, and side effects.

#### `#[Route]`

Binds a method to an HTTP verb and path.

```php
use JustSteveKing\Signal\Attributes\Route;

#[Route(method: 'GET', path: '/orders', description: 'List all orders for the authenticated user')]
public function index(): JsonResponse {}

#[Route(method: 'POST', path: '/orders')]
public function store(Request $request): JsonResponse {}
```

#### `#[Authorize]` _(repeatable)_

Declares the authorization ability required to call this method.

```php
use JustSteveKing\Signal\Attributes\Authorize;

#[Authorize(ability: 'orders.view', description: 'User must own the order or be an admin')]
public function show(Order $order): JsonResponse {}

// Multiple abilities:
#[Authorize(ability: 'orders.update')]
#[Authorize(ability: 'orders.approve')]
public function approve(Order $order): JsonResponse {}
```

#### `#[Validates]` _(repeatable)_

Documents the validation rules applied to request fields.

```php
use JustSteveKing\Signal\Attributes\Validates;

#[Validates(field: 'email', rules: 'required|email', description: 'Customer email address')]
#[Validates(field: 'items', rules: 'required|array|min:1')]
#[Validates(field: 'coupon_code', rules: 'nullable|string|max:20')]
public function store(Request $request): JsonResponse {}
```

#### `#[Cached]`

Documents that a method's result is cached, with optional TTL and cache key.

```php
use JustSteveKing\Signal\Attributes\Cached;

#[Cached(ttl: 300, key: 'orders.user.{userId}', description: 'Cached per user for 5 minutes')]
public function forUser(int $userId): Collection {}

// Default TTL is 3600 seconds
#[Cached]
public function all(): Collection {}
```

#### `#[Emits]` _(repeatable)_

Documents domain events dispatched by this method.

```php
use JustSteveKing\Signal\Attributes\Emits;

#[Emits(event: 'OrderPlaced', description: 'Fired after the order is persisted', tags: ['orders'])]
#[Emits(event: 'StockReserved', tags: ['inventory'])]
public function store(Request $request): JsonResponse {}
```

#### `#[Throws]` _(repeatable)_

Documents exceptions this method may throw.

```php
use JustSteveKing\Signal\Attributes\Throws;

#[Throws(exception: PaymentFailedException::class, description: 'When the payment gateway rejects the charge')]
#[Throws(exception: InsufficientStockException::class)]
public function checkout(Cart $cart): Order {}
```

#### `#[SideEffect]` _(repeatable)_

Documents observable side effects beyond the return value.

```php
use JustSteveKing\Signal\Attributes\SideEffect;

#[SideEffect(description: 'Sends an order confirmation email to the customer', tags: ['email'])]
#[SideEffect(description: 'Decrements inventory for each item in the order', tags: ['inventory'])]
public function store(Request $request): JsonResponse {}
```

---

## A Complete Example

Here is a realistic controller with Signal annotations showing how the attributes compose together:

```php
<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use JustSteveKing\Signal\Attributes\Controller;
use JustSteveKing\Signal\Attributes\DependsOn;
use JustSteveKing\Signal\Attributes\Route;
use JustSteveKing\Signal\Attributes\Authorize;
use JustSteveKing\Signal\Attributes\Validates;
use JustSteveKing\Signal\Attributes\Emits;
use JustSteveKing\Signal\Attributes\Throws;
use JustSteveKing\Signal\Attributes\SideEffect;
use JustSteveKing\Signal\Attributes\Cached;

#[Controller(
    description: 'RESTful API controller for managing customer orders',
    tags: ['orders', 'api', 'v1'],
)]
#[DependsOn(class: OrderService::class, description: 'Handles order business logic')]
#[DependsOn(class: OrderRepository::class)]
final class OrderController
{
    #[Route(method: 'GET', path: '/api/orders', description: 'Paginated list of orders')]
    #[Authorize(ability: 'orders.viewAny')]
    #[Cached(ttl: 60, key: 'orders.index.{page}')]
    public function index(Request $request): JsonResponse
    {
        // ...
    }

    #[Route(method: 'POST', path: '/api/orders', description: 'Create a new order')]
    #[Authorize(ability: 'orders.create')]
    #[Validates(field: 'items', rules: 'required|array|min:1', description: 'Order line items')]
    #[Validates(field: 'items.*.product_id', rules: 'required|integer|exists:products,id')]
    #[Validates(field: 'items.*.quantity', rules: 'required|integer|min:1')]
    #[Validates(field: 'payment_method', rules: 'required|in:card,bank_transfer')]
    #[Emits(event: 'OrderPlaced', description: 'Dispatched after the order is saved')]
    #[SideEffect(description: 'Sends order confirmation email', tags: ['email'])]
    #[SideEffect(description: 'Reserves inventory for each line item', tags: ['inventory'])]
    #[Throws(exception: PaymentFailedException::class, description: 'If the payment gateway rejects the charge')]
    #[Throws(exception: InsufficientStockException::class, description: 'If any item cannot be reserved')]
    public function store(Request $request): JsonResponse
    {
        // ...
    }

    #[Route(method: 'DELETE', path: '/api/orders/{id}', description: 'Cancel an order')]
    #[Authorize(ability: 'orders.cancel', description: 'Only the order owner or an admin')]
    #[Emits(event: 'OrderCancelled')]
    #[SideEffect(description: 'Releases reserved inventory', tags: ['inventory'])]
    #[Throws(exception: OrderAlreadyShippedException::class)]
    public function destroy(int $id): JsonResponse
    {
        // ...
    }
}
```

Running `php vendor/bin/signal generate` against this file produces:

````markdown
## Controllers

### OrderController

**Namespace:** `App\Http\Controllers\Api`

RESTful API controller for managing customer orders

**Tags:** `orders`, `api`, `v1`

**Dependencies:**

| Class | Description |
|-------|-------------|
| `OrderService` | Handles order business logic |
| `OrderRepository` | — |

**Methods:**

#### `index()`

**Route:** `GET /api/orders`

Paginated list of orders

**Requires Authorization:**

| Ability | Description |
|---------|-------------|
| `orders.viewAny` | — |

**Cached:** TTL `60s`, key `orders.index.{page}`

#### `store()`

**Route:** `POST /api/orders`

Create a new order

**Requires Authorization:**

| Ability | Description |
|---------|-------------|
| `orders.create` | — |

**Validates:**

| Field | Rules | Description |
|-------|-------|-------------|
| `items` | `required\|array\|min:1` | Order line items |
| `items.*.product_id` | `required\|integer\|exists:products,id` | — |
| `items.*.quantity` | `required\|integer\|min:1` | — |
| `payment_method` | `required\|in:card,bank_transfer` | — |

**Emits:**

| Event | Description | Tags |
|-------|-------------|------|
| `OrderPlaced` | Dispatched after the order is saved | — |

**Side Effects:**

| Description | Tags |
|-------------|------|
| Sends order confirmation email | `email` |
| Reserves inventory for each line item | `inventory` |

**Throws:**

| Exception | Description |
|-----------|-------------|
| `PaymentFailedException` | If the payment gateway rejects the charge |
| `InsufficientStockException` | If any item cannot be reserved |
````

---

## CLI Reference

```
php vendor/bin/signal generate [--config=signal.json]
```

| Option | Default | Description |
|--------|---------|-------------|
| `--config` | `signal.json` | Path to Signal config file |

---

## Output Formats

### Markdown

Written to `{output.path}/signal.md`. Classes are grouped by type (Controllers, Services, Repositories, etc.) with a table of contents structure.

### JSON

Written to `{output.path}/signal.json`. Machine-readable — useful for feeding into custom tooling, portals, or AI context windows.

```json
{
    "generated_at": "2025-01-01T00:00:00+00:00",
    "classes": [
        {
            "name": "OrderController",
            "namespace": "App\\Http\\Controllers\\Api",
            "fully_qualified_name": "App\\Http\\Controllers\\Api\\OrderController",
            "file": "src/Http/Controllers/Api/OrderController.php",
            "type": "controller",
            "description": "RESTful API controller for managing customer orders",
            "tags": ["orders", "api", "v1"],
            "methods": [
                {
                    "name": "store",
                    "route": {
                        "method": "post",
                        "path": "/api/orders",
                        "description": "Create a new order"
                    },
                    "authorize": [
                        { "ability": "orders.create", "description": "" }
                    ],
                    "emits": [
                        { "event": "OrderPlaced", "description": "Dispatched after the order is saved", "tags": [] }
                    ]
                }
            ]
        }
    ]
}
```

---

## License

MIT — see [LICENSE](LICENSE).
