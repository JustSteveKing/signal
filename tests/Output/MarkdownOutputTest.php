<?php

declare(strict_types=1);

namespace JustSteveKing\Signal\Tests\Output;

use JustSteveKing\Signal\Output\MarkdownOutput;
use JustSteveKing\Signal\Reflector\Reflector;
use JustSteveKing\Signal\Tests\Fixtures\ApiOrderController;
use JustSteveKing\Signal\Tests\Fixtures\AuthMiddleware;
use JustSteveKing\Signal\Tests\Fixtures\BillingModule;
use JustSteveKing\Signal\Tests\Fixtures\CreateOrderAction;
use JustSteveKing\Signal\Tests\Fixtures\LegacyPaymentService;
use JustSteveKing\Signal\Tests\Fixtures\OrderAggregate;
use JustSteveKing\Signal\Tests\Fixtures\OrderController;
use JustSteveKing\Signal\Tests\Fixtures\OrderEventListener;
use JustSteveKing\Signal\Tests\Fixtures\OrderPlacedEvent;
use JustSteveKing\Signal\Tests\Fixtures\OrderPlacedListener;
use JustSteveKing\Signal\Tests\Fixtures\OrderService;
use JustSteveKing\Signal\Tests\Fixtures\PaymentService;
use JustSteveKing\Signal\Tests\Fixtures\SendInvoiceJob;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use ReflectionClass;

#[CoversClass(MarkdownOutput::class)]
final class MarkdownOutputTest extends TestCase
{
    private MarkdownOutput $output;
    private Reflector $reflector;

    protected function setUp(): void
    {
        $this->output = new MarkdownOutput();
        $this->reflector = new Reflector();
    }

    #[Test]
    public function it_generates_a_markdown_string(): void
    {
        $definitions = $this->reflect(BillingModule::class);

        $result = $this->output->generate($definitions);

        $this->assertIsString($result);
    }

    #[Test]
    public function it_includes_the_signal_title(): void
    {
        $definitions = $this->reflect(BillingModule::class);

        $result = $this->output->generate($definitions);

        $this->assertStringContainsString('# Signal Documentation', $result);
    }

    #[Test]
    public function it_includes_the_class_name(): void
    {
        $definitions = $this->reflect(BillingModule::class);

        $result = $this->output->generate($definitions);

        $this->assertStringContainsString('BillingModule', $result);
    }

    #[Test]
    public function it_includes_the_description(): void
    {
        $definitions = $this->reflect(BillingModule::class);

        $result = $this->output->generate($definitions);

        $this->assertStringContainsString('Handles all billing and invoice processing', $result);
    }

    #[Test]
    public function it_groups_by_type(): void
    {
        $definitions = $this->reflect(BillingModule::class, OrderService::class);

        $result = $this->output->generate($definitions);

        $this->assertStringContainsString('## Modules', $result);
        $this->assertStringContainsString('## Services', $result);
    }

    #[Test]
    public function it_renders_route_information(): void
    {
        $definitions = $this->reflect(OrderController::class);

        $result = $this->output->generate($definitions);

        $this->assertStringContainsString('POST /orders', $result);
        $this->assertStringContainsString('GET /orders/{id}', $result);
    }

    #[Test]
    public function it_renders_deprecated_class_notice(): void
    {
        $definitions = $this->reflect(LegacyPaymentService::class);

        $result = $this->output->generate($definitions);

        $this->assertStringContainsString('Deprecated', $result);
        $this->assertStringContainsString('Use PaymentService instead.', $result);
        $this->assertStringContainsString('1.5.0', $result);
    }

    #[Test]
    public function it_renders_internal_notice(): void
    {
        $definitions = $this->reflect(LegacyPaymentService::class);

        $result = $this->output->generate($definitions);

        $this->assertStringContainsString('Internal', $result);
        $this->assertStringContainsString('Kept for backwards compatibility only.', $result);
    }

    #[Test]
    public function it_renders_deprecated_method_notice(): void
    {
        $definitions = $this->reflect(OrderController::class);

        $result = $this->output->generate($definitions);

        $this->assertStringContainsString('Use OrderV2Controller instead.', $result);
    }

    #[Test]
    public function it_renders_emits(): void
    {
        $definitions = $this->reflect(PaymentService::class);

        $result = $this->output->generate($definitions);

        $this->assertStringContainsString('PaymentProcessed', $result);
        $this->assertStringContainsString('PaymentFailed', $result);
    }

    #[Test]
    public function it_renders_side_effects(): void
    {
        $definitions = $this->reflect(PaymentService::class);

        $result = $this->output->generate($definitions);

        $this->assertStringContainsString('Charges the customer payment method.', $result);
        $this->assertStringContainsString('Logs the transaction to the audit trail.', $result);
    }

    #[Test]
    public function it_renders_throws(): void
    {
        $definitions = $this->reflect(PaymentService::class);

        $result = $this->output->generate($definitions);

        $this->assertStringContainsString('PaymentFailedException', $result);
        $this->assertStringContainsString('InvalidCardException', $result);
    }

    #[Test]
    public function it_renders_listens_to(): void
    {
        $definitions = $this->reflect(OrderPlacedListener::class);

        $result = $this->output->generate($definitions);

        $this->assertStringContainsString('OrderCreated', $result);
        $this->assertStringContainsString('OrderUpdated', $result);
    }

    #[Test]
    public function it_renders_depends_on(): void
    {
        $definitions = $this->reflect(OrderService::class);

        $result = $this->output->generate($definitions);

        $this->assertStringContainsString('PaymentService', $result);
        $this->assertStringContainsString('InventoryService', $result);
    }

    #[Test]
    public function it_renders_tags(): void
    {
        $definitions = $this->reflect(OrderService::class);

        $result = $this->output->generate($definitions);

        $this->assertStringContainsString('orders', $result);
        $this->assertStringContainsString('fulfilment', $result);
    }

    #[Test]
    public function it_renders_action_method(): void
    {
        $definitions = $this->reflect(CreateOrderAction::class);

        $result = $this->output->generate($definitions);

        $this->assertStringContainsString('handle()', $result);
        $this->assertStringContainsString('OrderCreated', $result);
    }

    #[Test]
    public function it_renders_event_type(): void
    {
        $definitions = $this->reflect(OrderPlacedEvent::class);

        $result = $this->output->generate($definitions);

        $this->assertStringContainsString('## Events', $result);
        $this->assertStringContainsString('OrderPlacedEvent', $result);
        $this->assertStringContainsString('Fired when an order is successfully placed', $result);
    }

    #[Test]
    public function it_renders_listener_type(): void
    {
        $definitions = $this->reflect(OrderEventListener::class);

        $result = $this->output->generate($definitions);

        $this->assertStringContainsString('## Listeners', $result);
        $this->assertStringContainsString('OrderPlaced', $result);
        $this->assertStringContainsString('OrderCancelled', $result);
    }

    #[Test]
    public function it_renders_middleware_priority(): void
    {
        $definitions = $this->reflect(AuthMiddleware::class);

        $result = $this->output->generate($definitions);

        $this->assertStringContainsString('## Middleware', $result);
        $this->assertStringContainsString('Priority', $result);
        $this->assertStringContainsString('10', $result);
    }

    #[Test]
    public function it_renders_job_queue(): void
    {
        $definitions = $this->reflect(SendInvoiceJob::class);

        $result = $this->output->generate($definitions);

        $this->assertStringContainsString('## Jobs', $result);
        $this->assertStringContainsString('Queue', $result);
        $this->assertStringContainsString('invoices', $result);
    }

    #[Test]
    public function it_renders_aggregate_type(): void
    {
        $definitions = $this->reflect(OrderAggregate::class);

        $result = $this->output->generate($definitions);

        $this->assertStringContainsString('## Aggregates', $result);
        $this->assertStringContainsString('OrderAggregate', $result);
    }

    #[Test]
    public function it_renders_authorize_as_table(): void
    {
        $definitions = $this->reflect(ApiOrderController::class);

        $result = $this->output->generate($definitions);

        $this->assertStringContainsString('Requires Authorization', $result);
        $this->assertStringContainsString('orders.viewAny', $result);
        $this->assertStringContainsString('orders.create', $result);
    }

    #[Test]
    public function it_renders_validates_as_table(): void
    {
        $definitions = $this->reflect(ApiOrderController::class);

        $result = $this->output->generate($definitions);

        $this->assertStringContainsString('Validates', $result);
        $this->assertStringContainsString('customer_id', $result);
        $this->assertStringContainsString('items', $result);
        $this->assertStringContainsString('required|integer', $result);
    }

    #[Test]
    public function it_renders_cached_inline(): void
    {
        $definitions = $this->reflect(ApiOrderController::class);

        $result = $this->output->generate($definitions);

        $this->assertStringContainsString('Cached', $result);
        $this->assertStringContainsString('300s', $result);
        $this->assertStringContainsString('orders.index', $result);
    }

    #[Test]
    public function it_renders_depends_on_as_table(): void
    {
        $definitions = $this->reflect(OrderService::class);

        $result = $this->output->generate($definitions);

        $this->assertStringContainsString('| `', $result);
        $this->assertStringContainsString('PaymentService', $result);
        $this->assertStringContainsString('InventoryService', $result);
    }

    #[Test]
    public function it_renders_throws_as_table(): void
    {
        $definitions = $this->reflect(PaymentService::class);

        $result = $this->output->generate($definitions);

        $this->assertStringContainsString('| `PaymentFailedException`', $result);
        $this->assertStringContainsString('| `InvalidCardException`', $result);
    }

    #[Test]
    public function it_renders_emits_as_table(): void
    {
        $definitions = $this->reflect(PaymentService::class);

        $result = $this->output->generate($definitions);

        $this->assertStringContainsString('| `PaymentProcessed`', $result);
        $this->assertStringContainsString('| `PaymentFailed`', $result);
    }

    private function reflect(string ...$classes): array
    {
        return array_filter(
            array: array_map(
                callback: fn(string $class): mixed => $this->reflector->reflect(
                    file: (new ReflectionClass($class))->getFileName(),
                ),
                array: $classes,
            ),
        );
    }
}
