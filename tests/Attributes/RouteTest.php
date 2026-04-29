<?php

declare(strict_types=1);

namespace JustSteveKing\Signal\Tests\Attributes;

use JustSteveKing\Signal\Attributes\Route;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

#[CoversClass(Route::class)]
final class RouteTest extends TestCase
{
    public static function httpMethods(): array
    {
        return [
            'GET' => ['GET'],
            'POST' => ['POST'],
            'PUT' => ['PUT'],
            'PATCH' => ['PATCH'],
            'DELETE' => ['DELETE'],
        ];
    }
    #[Test]
    public function it_stores_method_and_path(): void
    {
        $attribute = new Route(method: 'POST', path: '/orders');

        $this->assertSame('POST', $attribute->method);
        $this->assertSame('/orders', $attribute->path);
    }

    #[Test]
    public function it_defaults_to_empty_description(): void
    {
        $attribute = new Route(method: 'GET', path: '/orders');

        $this->assertSame('', $attribute->description);
    }

    #[Test]
    public function it_defaults_to_empty_tags(): void
    {
        $attribute = new Route(method: 'GET', path: '/orders');

        $this->assertSame([], $attribute->tags);
    }

    #[Test]
    #[DataProvider('httpMethods')]
    public function it_stores_http_methods(string $method): void
    {
        $attribute = new Route(method: $method, path: '/orders');

        $this->assertSame($method, $attribute->method);
    }
}
