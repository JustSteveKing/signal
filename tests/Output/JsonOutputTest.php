<?php

declare(strict_types=1);

namespace JustSteveKing\Signal\Tests\Output;

use JustSteveKing\Signal\Output\JsonOutput;
use JustSteveKing\Signal\Reflector\Reflector;
use JustSteveKing\Signal\Tests\Fixtures\ApiOrderController;
use JustSteveKing\Signal\Tests\Fixtures\AuthMiddleware;
use JustSteveKing\Signal\Tests\Fixtures\BillingModule;
use JustSteveKing\Signal\Tests\Fixtures\CreateOrderAction;
use JustSteveKing\Signal\Tests\Fixtures\LegacyPaymentService;
use JustSteveKing\Signal\Tests\Fixtures\OrderController;
use JustSteveKing\Signal\Tests\Fixtures\OrderPlacedListener;
use JustSteveKing\Signal\Tests\Fixtures\OrderService;
use JustSteveKing\Signal\Tests\Fixtures\PaymentService;
use JustSteveKing\Signal\Tests\Fixtures\SendInvoiceJob;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use ReflectionClass;

#[CoversClass(JsonOutput::class)]
final class JsonOutputTest extends TestCase
{
    private JsonOutput $output;
    private Reflector $reflector;

    protected function setUp(): void
    {
        $this->output = new JsonOutput();
        $this->reflector = new Reflector();
    }

    #[Test]
    public function it_generates_valid_json(): void
    {
        $definitions = $this->reflect(BillingModule::class);

        $result = $this->output->generate($definitions);

        $this->assertJson($result);
    }

    #[Test]
    public function it_includes_generated_at_timestamp(): void
    {
        $definitions = $this->reflect(BillingModule::class);

        $result = json_decode($this->output->generate($definitions), associative: true);

        $this->assertArrayHasKey('generated_at', $result);
    }

    #[Test]
    public function it_serializes_a_module(): void
    {
        $definitions = $this->reflect(BillingModule::class);

        $result = json_decode($this->output->generate($definitions), associative: true);

        $class = $result['classes'][0];

        $this->assertSame('BillingModule', $class['name']);
        $this->assertSame('module', $class['type']);
        $this->assertSame('Handles all billing and invoice processing', $class['description']);
    }

    #[Test]
    public function it_serializes_multiple_depends_on(): void
    {
        $definitions = $this->reflect(OrderService::class);

        $result = json_decode($this->output->generate($definitions), associative: true);

        $class = $result['classes'][0];

        $this->assertCount(2, $class['depends_on']);
    }

    #[Test]
    public function it_serializes_multiple_listens_to(): void
    {
        $definitions = $this->reflect(OrderPlacedListener::class);

        $result = json_decode($this->output->generate($definitions), associative: true);

        $class = $result['classes'][0];

        $this->assertCount(2, $class['listens_to']);
    }

    #[Test]
    public function it_serializes_method_routes(): void
    {
        $definitions = $this->reflect(OrderController::class);

        $result = json_decode($this->output->generate($definitions), associative: true);

        $methods = $result['classes'][0]['methods'];

        $storeMethod = array_values(array_filter($methods, fn($m) => 'store' === $m['name']))[0];

        $this->assertSame('POST', $storeMethod['route']['method']);
        $this->assertSame('/orders', $storeMethod['route']['path']);
    }

    #[Test]
    public function it_serializes_multiple_throws_on_a_method(): void
    {
        $definitions = $this->reflect(PaymentService::class);

        $result = json_decode($this->output->generate($definitions), associative: true);

        $chargeMethod = $result['classes'][0]['methods'][0];

        $this->assertCount(2, $chargeMethod['throws']);
    }

    #[Test]
    public function it_serializes_multiple_emits_on_a_method(): void
    {
        $definitions = $this->reflect(PaymentService::class);

        $result = json_decode($this->output->generate($definitions), associative: true);

        $chargeMethod = $result['classes'][0]['methods'][0];

        $this->assertCount(2, $chargeMethod['emits']);
    }

    #[Test]
    public function it_serializes_multiple_side_effects_on_a_method(): void
    {
        $definitions = $this->reflect(PaymentService::class);

        $result = json_decode($this->output->generate($definitions), associative: true);

        $chargeMethod = $result['classes'][0]['methods'][0];

        $this->assertCount(2, $chargeMethod['side_effects']);
    }

    #[Test]
    public function it_serializes_a_deprecated_class(): void
    {
        $definitions = $this->reflect(LegacyPaymentService::class);

        $result = json_decode($this->output->generate($definitions), associative: true);

        $class = $result['classes'][0];

        $this->assertNotNull($class['deprecated']);
        $this->assertSame('Use PaymentService instead.', $class['deprecated']['reason']);
        $this->assertSame('1.5.0', $class['deprecated']['since']);
    }

    #[Test]
    public function it_serializes_an_internal_class(): void
    {
        $definitions = $this->reflect(LegacyPaymentService::class);

        $result = json_decode($this->output->generate($definitions), associative: true);

        $class = $result['classes'][0];

        $this->assertNotNull($class['internal']);
        $this->assertSame('Kept for backwards compatibility only.', $class['internal']['reason']);
    }

    #[Test]
    public function it_serializes_a_deprecated_method(): void
    {
        $definitions = $this->reflect(OrderController::class);

        $result = json_decode($this->output->generate($definitions), associative: true);

        $methods = $result['classes'][0]['methods'];

        $showMethod = array_values(array_filter($methods, fn($m) => 'show' === $m['name']))[0];

        $this->assertNotNull($showMethod['deprecated']);
        $this->assertSame('Use OrderV2Controller instead.', $showMethod['deprecated']['reason']);
    }

    #[Test]
    public function it_returns_null_deprecated_for_non_deprecated_methods(): void
    {
        $definitions = $this->reflect(OrderController::class);

        $result = json_decode($this->output->generate($definitions), associative: true);

        $methods = $result['classes'][0]['methods'];

        $storeMethod = array_values(array_filter($methods, fn($m) => 'store' === $m['name']))[0];

        $this->assertNull($storeMethod['deprecated']);
    }

    #[Test]
    public function it_serializes_an_action(): void
    {
        $definitions = $this->reflect(CreateOrderAction::class);

        $result = json_decode($this->output->generate($definitions), associative: true);

        $class = $result['classes'][0];

        $this->assertSame('action', $class['type']);
    }

    #[Test]
    public function it_serializes_middleware_priority(): void
    {
        $definitions = $this->reflect(AuthMiddleware::class);

        $result = json_decode($this->output->generate($definitions), associative: true);

        $class = $result['classes'][0];

        $this->assertSame('middleware', $class['type']);
        $this->assertSame(10, $class['priority']);
        $this->assertNull($class['queue']);
    }

    #[Test]
    public function it_serializes_job_queue(): void
    {
        $definitions = $this->reflect(SendInvoiceJob::class);

        $result = json_decode($this->output->generate($definitions), associative: true);

        $class = $result['classes'][0];

        $this->assertSame('job', $class['type']);
        $this->assertSame('invoices', $class['queue']);
        $this->assertNull($class['priority']);
    }

    #[Test]
    public function it_serializes_authorize_on_a_method(): void
    {
        $definitions = $this->reflect(ApiOrderController::class);

        $result = json_decode($this->output->generate($definitions), associative: true);

        $indexMethod = array_values(array_filter($result['classes'][0]['methods'], fn($m) => 'index' === $m['name']))[0];

        $this->assertCount(1, $indexMethod['authorize']);
        $this->assertSame('orders.viewAny', $indexMethod['authorize'][0]['ability']);
    }

    #[Test]
    public function it_serializes_validates_on_a_method(): void
    {
        $definitions = $this->reflect(ApiOrderController::class);

        $result = json_decode($this->output->generate($definitions), associative: true);

        $storeMethod = array_values(array_filter($result['classes'][0]['methods'], fn($m) => 'store' === $m['name']))[0];

        $this->assertCount(2, $storeMethod['validates']);
        $this->assertSame('customer_id', $storeMethod['validates'][0]['field']);
        $this->assertSame('required|integer', $storeMethod['validates'][0]['rules']);
    }

    #[Test]
    public function it_serializes_cached_on_a_method(): void
    {
        $definitions = $this->reflect(ApiOrderController::class);

        $result = json_decode($this->output->generate($definitions), associative: true);

        $indexMethod = array_values(array_filter($result['classes'][0]['methods'], fn($m) => 'index' === $m['name']))[0];

        $this->assertNotNull($indexMethod['cached']);
        $this->assertSame(300, $indexMethod['cached']['ttl']);
        $this->assertSame('orders.index', $indexMethod['cached']['key']);
    }

    #[Test]
    public function it_returns_null_cached_for_non_cached_methods(): void
    {
        $definitions = $this->reflect(ApiOrderController::class);

        $result = json_decode($this->output->generate($definitions), associative: true);

        $storeMethod = array_values(array_filter($result['classes'][0]['methods'], fn($m) => 'store' === $m['name']))[0];

        $this->assertNull($storeMethod['cached']);
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
