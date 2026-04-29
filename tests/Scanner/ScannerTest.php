<?php

declare(strict_types=1);

namespace JustSteveKing\Signal\Tests\Scanner;

use JustSteveKing\Signal\Scanner\Scanner;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use RuntimeException;

#[CoversClass(Scanner::class)]
final class ScannerTest extends TestCase
{
    private Scanner $scanner;

    protected function setUp(): void
    {
        $this->scanner = new Scanner();
    }

    #[Test]
    public function it_throws_for_an_invalid_directory(): void
    {
        $this->expectException(RuntimeException::class);

        $this->scanner->scan(path: '/non/existent/path');
    }

    #[Test]
    public function it_finds_php_files_recursively(): void
    {
        $files = $this->scanner->scan(
            path: __DIR__ . '/../Fixtures',
        );

        $this->assertNotEmpty($files);

        foreach ($files as $file) {
            $this->assertStringEndsWith('.php', $file);
        }
    }

    #[Test]
    public function it_excludes_specified_paths(): void
    {
        $excludePath = __DIR__ . '/../Fixtures';

        $files = $this->scanner->scan(
            path: __DIR__ . '/..',
            exclude: [$excludePath],
        );

        foreach ($files as $file) {
            $this->assertStringNotContainsString('Fixtures', $file);
        }
    }

    #[Test]
    public function it_only_returns_php_files(): void
    {
        $files = $this->scanner->scan(
            path: __DIR__ . '/../Fixtures',
        );

        foreach ($files as $file) {
            $this->assertSame('php', pathinfo($file, PATHINFO_EXTENSION));
        }
    }
}
