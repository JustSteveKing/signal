<?php

declare(strict_types=1);

namespace JustSteveKing\Signal\Tests\Reflector;

use JustSteveKing\Signal\Attributes\Action;
use JustSteveKing\Signal\Attributes\Aggregate;
use JustSteveKing\Signal\Attributes\Authorize;
use JustSteveKing\Signal\Attributes\Cached;
use JustSteveKing\Signal\Attributes\Command;
use JustSteveKing\Signal\Attributes\Controller;
use JustSteveKing\Signal\Attributes\DependsOn;
use JustSteveKing\Signal\Attributes\Deprecated;
use JustSteveKing\Signal\Attributes\Emits;
use JustSteveKing\Signal\Attributes\Event;
use JustSteveKing\Signal\Attributes\Internal;
use JustSteveKing\Signal\Attributes\Job;
use JustSteveKing\Signal\Attributes\Listener;
use JustSteveKing\Signal\Attributes\ListensTo;
use JustSteveKing\Signal\Attributes\Middleware;
use JustSteveKing\Signal\Attributes\Module;
use JustSteveKing\Signal\Attributes\Query;
use JustSteveKing\Signal\Attributes\Repository;
use JustSteveKing\Signal\Attributes\Service;
use JustSteveKing\Signal\Attributes\SideEffect;
use JustSteveKing\Signal\Attributes\Throws;
use JustSteveKing\Signal\Attributes\Validates;
use JustSteveKing\Signal\Attributes\ValueObject;
use JustSteveKing\Signal\Data\ClassDefinition;
use JustSteveKing\Signal\Reflector\Reflector;
use JustSteveKing\Signal\Tests\Fixtures\ApiOrderController;
use JustSteveKing\Signal\Tests\Fixtures\AuthMiddleware;
use JustSteveKing\Signal\Tests\Fixtures\BillingModule;
use JustSteveKing\Signal\Tests\Fixtures\CreateOrderAction;
use JustSteveKing\Signal\Tests\Fixtures\GetOrderQuery;
use JustSteveKing\Signal\Tests\Fixtures\InvoiceRepository;
use JustSteveKing\Signal\Tests\Fixtures\LegacyPaymentService;
use JustSteveKing\Signal\Tests\Fixtures\MoneyValueObject;
use JustSteveKing\Signal\Tests\Fixtures\OrderAggregate;
use JustSteveKing\Signal\Tests\Fixtures\OrderController;
use JustSteveKing\Signal\Tests\Fixtures\OrderEventListener;
use JustSteveKing\Signal\Tests\Fixtures\OrderPlacedEvent;
use JustSteveKing\Signal\Tests\Fixtures\OrderPlacedListener;
use JustSteveKing\Signal\Tests\Fixtures\OrderService;
use JustSteveKing\Signal\Tests\Fixtures\PaymentService;
use JustSteveKing\Signal\Tests\Fixtures\PlaceOrderCommand;
use JustSteveKing\Signal\Tests\Fixtures\SendInvoiceJob;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use ReflectionClass;

#[CoversClass(Reflector::class)]
final class ReflectorTest extends TestCase
{
    private Reflector $reflector;

    protected function setUp(): void
    {
        $this->reflector = new Reflector();
    }

    #[Test]
    public function it_returns_null_for_a_file_with_no_signal_attributes(): void
    {
        $result = $this->reflector->reflect(__FILE__);

        $this->assertNull($result);
    }

    #[Test]
    public function it_reflects_a_module(): void
    {
        $result = $this->reflector->reflect(
            file: $this->fixtureFile(BillingModule::class),
        );

        $this->assertInstanceOf(ClassDefinition::class, $result);
        $this->assertSame('BillingModule', $result->name);
        $this->assertNotNull($result->attributeOfType(Module::class));
    }

    #[Test]
    public function it_reflects_a_service_with_multiple_depends_on(): void
    {
        $result = $this->reflector->reflect(
            file: $this->fixtureFile(OrderService::class),
        );

        $this->assertInstanceOf(ClassDefinition::class, $result);
        $this->assertNotNull($result->attributeOfType(Service::class));

        $dependencies = $result->attributesOfType(DependsOn::class);

        $this->assertCount(2, $dependencies);
    }

    #[Test]
    public function it_reflects_a_repository(): void
    {
        $result = $this->reflector->reflect(
            file: $this->fixtureFile(InvoiceRepository::class),
        );

        $this->assertInstanceOf(ClassDefinition::class, $result);
        $this->assertNotNull($result->attributeOfType(Repository::class));
    }

    #[Test]
    public function it_reflects_an_action_with_method_attributes(): void
    {
        $result = $this->reflector->reflect(
            file: $this->fixtureFile(CreateOrderAction::class),
        );

        $this->assertInstanceOf(ClassDefinition::class, $result);
        $this->assertNotNull($result->attributeOfType(Action::class));
        $this->assertTrue($result->hasMethod());

        $method = $result->methods[0];

        $this->assertSame('handle', $method->name);

        $emits = array_filter(
            array: $method->attributes,
            callback: fn(object $a): bool => $a instanceof Emits,
        );

        $this->assertCount(1, $emits);
    }

    #[Test]
    public function it_reflects_a_controller_with_routes_and_throws(): void
    {
        $result = $this->reflector->reflect(
            file: $this->fixtureFile(OrderController::class),
        );

        $this->assertInstanceOf(ClassDefinition::class, $result);
        $this->assertNotNull($result->attributeOfType(Controller::class));
        $this->assertCount(2, $result->methods);
    }

    #[Test]
    public function it_reflects_a_deprecated_method(): void
    {
        $result = $this->reflector->reflect(
            file: $this->fixtureFile(OrderController::class),
        );

        $showMethod = array_values(array_filter($result->methods, fn($m) => 'show' === $m->name))[0];

        $deprecated = array_filter(
            array: $showMethod->attributes,
            callback: fn(object $a): bool => $a instanceof Deprecated,
        );

        $this->assertCount(1, $deprecated);
    }

    #[Test]
    public function it_reflects_multiple_throws_on_a_method(): void
    {
        $result = $this->reflector->reflect(
            file: $this->fixtureFile(PaymentService::class),
        );

        $chargeMethod = $result->methods[0];

        $throws = array_filter(
            array: $chargeMethod->attributes,
            callback: fn(object $a): bool => $a instanceof Throws,
        );

        $this->assertCount(2, $throws);
    }

    #[Test]
    public function it_reflects_multiple_side_effects_on_a_method(): void
    {
        $result = $this->reflector->reflect(
            file: $this->fixtureFile(PaymentService::class),
        );

        $chargeMethod = $result->methods[0];

        $sideEffects = array_filter(
            array: $chargeMethod->attributes,
            callback: fn(object $a): bool => $a instanceof SideEffect,
        );

        $this->assertCount(2, $sideEffects);
    }

    #[Test]
    public function it_reflects_multiple_listens_to_on_a_class(): void
    {
        $result = $this->reflector->reflect(
            file: $this->fixtureFile(OrderPlacedListener::class),
        );

        $listensTo = $result->attributesOfType(ListensTo::class);

        $this->assertCount(2, $listensTo);
    }

    #[Test]
    public function it_reflects_deprecated_and_internal_on_a_class(): void
    {
        $result = $this->reflector->reflect(
            file: $this->fixtureFile(LegacyPaymentService::class),
        );

        $this->assertNotNull($result->attributeOfType(Deprecated::class));
        $this->assertNotNull($result->attributeOfType(Internal::class));
    }

    #[Test]
    public function it_reflects_an_event(): void
    {
        $result = $this->reflector->reflect(
            file: $this->fixtureFile(OrderPlacedEvent::class),
        );

        $this->assertInstanceOf(ClassDefinition::class, $result);
        $this->assertNotNull($result->attributeOfType(Event::class));
    }

    #[Test]
    public function it_reflects_a_listener_with_listens_to(): void
    {
        $result = $this->reflector->reflect(
            file: $this->fixtureFile(OrderEventListener::class),
        );

        $this->assertInstanceOf(ClassDefinition::class, $result);
        $this->assertNotNull($result->attributeOfType(Listener::class));
        $this->assertCount(2, $result->attributesOfType(ListensTo::class));
    }

    #[Test]
    public function it_reflects_middleware_with_priority(): void
    {
        $result = $this->reflector->reflect(
            file: $this->fixtureFile(AuthMiddleware::class),
        );

        $this->assertInstanceOf(ClassDefinition::class, $result);
        $middleware = $result->attributeOfType(Middleware::class);
        $this->assertNotNull($middleware);
        $this->assertSame(10, $middleware->priority);
    }

    #[Test]
    public function it_reflects_a_job_with_queue(): void
    {
        $result = $this->reflector->reflect(
            file: $this->fixtureFile(SendInvoiceJob::class),
        );

        $this->assertInstanceOf(ClassDefinition::class, $result);
        $job = $result->attributeOfType(Job::class);
        $this->assertNotNull($job);
        $this->assertSame('invoices', $job->queue);
    }

    #[Test]
    public function it_reflects_a_command(): void
    {
        $result = $this->reflector->reflect(
            file: $this->fixtureFile(PlaceOrderCommand::class),
        );

        $this->assertInstanceOf(ClassDefinition::class, $result);
        $this->assertNotNull($result->attributeOfType(Command::class));
    }

    #[Test]
    public function it_reflects_a_query(): void
    {
        $result = $this->reflector->reflect(
            file: $this->fixtureFile(GetOrderQuery::class),
        );

        $this->assertInstanceOf(ClassDefinition::class, $result);
        $this->assertNotNull($result->attributeOfType(Query::class));
    }

    #[Test]
    public function it_reflects_an_aggregate(): void
    {
        $result = $this->reflector->reflect(
            file: $this->fixtureFile(OrderAggregate::class),
        );

        $this->assertInstanceOf(ClassDefinition::class, $result);
        $this->assertNotNull($result->attributeOfType(Aggregate::class));
    }

    #[Test]
    public function it_reflects_a_value_object(): void
    {
        $result = $this->reflector->reflect(
            file: $this->fixtureFile(MoneyValueObject::class),
        );

        $this->assertInstanceOf(ClassDefinition::class, $result);
        $this->assertNotNull($result->attributeOfType(ValueObject::class));
    }

    #[Test]
    public function it_reflects_authorize_on_a_method(): void
    {
        $result = $this->reflector->reflect(
            file: $this->fixtureFile(ApiOrderController::class),
        );

        $indexMethod = array_values(array_filter($result->methods, fn($m) => 'index' === $m->name))[0];

        $authorize = array_filter(
            array: $indexMethod->attributes,
            callback: fn(object $a): bool => $a instanceof Authorize,
        );

        $this->assertCount(1, $authorize);
    }

    #[Test]
    public function it_reflects_multiple_validates_on_a_method(): void
    {
        $result = $this->reflector->reflect(
            file: $this->fixtureFile(ApiOrderController::class),
        );

        $storeMethod = array_values(array_filter($result->methods, fn($m) => 'store' === $m->name))[0];

        $validates = array_filter(
            array: $storeMethod->attributes,
            callback: fn(object $a): bool => $a instanceof Validates,
        );

        $this->assertCount(2, $validates);
    }

    #[Test]
    public function it_reflects_cached_on_a_method(): void
    {
        $result = $this->reflector->reflect(
            file: $this->fixtureFile(ApiOrderController::class),
        );

        $indexMethod = array_values(array_filter($result->methods, fn($m) => 'index' === $m->name))[0];

        $cached = null;

        foreach ($indexMethod->attributes as $attribute) {
            if ($attribute instanceof Cached) {
                $cached = $attribute;
                break;
            }
        }

        $this->assertNotNull($cached);
        $this->assertSame(300, $cached->ttl);
        $this->assertSame('orders.index', $cached->key);
    }

    private function fixtureFile(string $class): string
    {
        $reflection = new ReflectionClass($class);

        return $reflection->getFileName();
    }
}
