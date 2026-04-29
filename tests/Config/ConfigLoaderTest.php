<?php

declare(strict_types=1);

namespace JustSteveKing\Signal\Tests\Config;

use JustSteveKing\Signal\Config\Config;
use JustSteveKing\Signal\Config\ConfigLoader;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use RuntimeException;

#[CoversClass(ConfigLoader::class)]
#[CoversClass(Config::class)]
final class ConfigLoaderTest extends TestCase
{
    private ConfigLoader $loader;
    private string $tempDir;

    protected function setUp(): void
    {
        $this->loader = new ConfigLoader();
        $this->tempDir = sys_get_temp_dir() . '/signal-tests-' . uniqid();
        mkdir($this->tempDir);
    }

    protected function tearDown(): void
    {
        array_map('unlink', glob($this->tempDir . '/*'));
        rmdir($this->tempDir);
    }

    #[Test]
    public function it_throws_when_config_file_does_not_exist(): void
    {
        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessageMatches('/not found/');

        $this->loader->load('/non/existent/signal.json');
    }

    #[Test]
    public function it_throws_when_input_is_missing(): void
    {
        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessageMatches('/input/');

        $path = $this->writeConfig([
            'output' => ['format' => ['markdown'], 'path' => 'docs/'],
        ]);

        $this->loader->load($path);
    }

    #[Test]
    public function it_throws_when_output_path_is_missing(): void
    {
        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessageMatches('/output.path/');

        $path = $this->writeConfig([
            'input' => 'src/',
            'output' => ['format' => ['markdown']],
        ]);

        $this->loader->load($path);
    }

    #[Test]
    public function it_throws_for_an_unsupported_format(): void
    {
        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessageMatches('/Unsupported output format/');

        $path = $this->writeConfig([
            'input' => 'src/',
            'output' => ['format' => ['xml'], 'path' => 'docs/'],
        ]);

        $this->loader->load($path);
    }

    #[Test]
    public function it_loads_a_valid_config(): void
    {
        $path = $this->writeConfig([
            'input' => 'src/',
            'output' => ['format' => ['markdown', 'json'], 'path' => 'docs/'],
            'exclude' => ['src/Attributes/'],
        ]);

        $config = $this->loader->load($path);

        $this->assertInstanceOf(Config::class, $config);
        $this->assertSame('src/', $config->input);
        $this->assertSame('docs/', $config->outputPath);
        $this->assertSame(['markdown', 'json'], $config->formats);
        $this->assertSame(['src/Attributes/'], $config->exclude);
    }

    #[Test]
    public function it_accepts_a_single_format_string(): void
    {
        $path = $this->writeConfig([
            'input' => 'src/',
            'output' => ['format' => 'markdown', 'path' => 'docs/'],
        ]);

        $config = $this->loader->load($path);

        $this->assertSame(['markdown'], $config->formats);
    }

    #[Test]
    public function it_defaults_exclude_to_empty_array(): void
    {
        $path = $this->writeConfig([
            'input' => 'src/',
            'output' => ['format' => ['markdown'], 'path' => 'docs/'],
        ]);

        $config = $this->loader->load($path);

        $this->assertSame([], $config->exclude);
    }

    private function writeConfig(array $data): string
    {
        $path = $this->tempDir . '/signal.json';

        file_put_contents($path, json_encode($data));

        return $path;
    }
}
