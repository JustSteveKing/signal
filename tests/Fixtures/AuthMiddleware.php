<?php

declare(strict_types=1);

namespace JustSteveKing\Signal\Tests\Fixtures;

use JustSteveKing\Signal\Attributes\Middleware;

#[Middleware(description: 'Authenticates incoming HTTP requests', tags: ['auth', 'http'], priority: 10)]
final class AuthMiddleware {}
