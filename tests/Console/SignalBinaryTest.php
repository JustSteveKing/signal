<?php

declare(strict_types=1);

namespace JustSteveKing\Signal\Tests\Console;

use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

final class SignalBinaryTest extends TestCase
{
    private const string MARKER = 'SIGNAL_AUTOLOADER_LOADED';

    private string $tempDir;

    protected function setUp(): void
    {
        $this->tempDir = sys_get_temp_dir() . '/signal-bin-' . uniqid();

        mkdir($this->tempDir, 0o777, true);
    }

    protected function tearDown(): void
    {
        $this->removeDirectory($this->tempDir);
    }

    #[Test]
    public function it_locates_the_autoloader_when_installed_as_a_dependency(): void
    {
        // Mirror the path a host project sees: vendor/juststeveking/signal/bin/signal
        // with the project's autoloader at vendor/autoload.php.
        $binPath = $this->stageBinary('vendor/juststeveking/signal/bin');
        $this->stageAutoloader('vendor/autoload.php');

        $result = $this->execute($binPath);

        $this->assertSame(0, $result['exitCode'], $result['stderr']);
        $this->assertStringContainsString(self::MARKER, $result['stdout']);
    }

    #[Test]
    public function it_locates_the_autoloader_for_a_standalone_checkout(): void
    {
        // A local checkout of the package itself: bin/signal with the
        // autoloader at vendor/autoload.php alongside it.
        $binPath = $this->stageBinary('bin');
        $this->stageAutoloader('vendor/autoload.php');

        $result = $this->execute($binPath);

        $this->assertSame(0, $result['exitCode'], $result['stderr']);
        $this->assertStringContainsString(self::MARKER, $result['stdout']);
    }

    /**
     * Copy the real bin/signal under test into a simulated directory layout.
     */
    private function stageBinary(string $relativeBinDir): string
    {
        $binDir = $this->tempDir . '/' . $relativeBinDir;

        mkdir($binDir, 0o777, true);

        $target = $binDir . '/signal';

        copy(__DIR__ . '/../../bin/signal', $target);

        return $target;
    }

    /**
     * Write a stub autoloader that defines just enough of the Application
     * class for the binary to boot and print a marker, proving the binary
     * required this file.
     */
    private function stageAutoloader(string $relativePath): void
    {
        $path = $this->tempDir . '/' . $relativePath;
        $directory = dirname($path);

        if ( ! is_dir($directory)) {
            mkdir($directory, 0o777, true);
        }

        file_put_contents($path, <<<PHP
            <?php

            namespace JustSteveKing\Signal\Console;

            final class Application
            {
                public function run(): int
                {
                    fwrite(STDOUT, '{$this->marker()}');

                    return 0;
                }
            }
            PHP);
    }

    /**
     * Run the binary in an isolated subprocess. Passing the command as an
     * array makes proc_open bypass the shell, so there is nothing to escape,
     * and the separate pipes let us assert on stdout, stderr and exit code.
     *
     * @return array{stdout: string, stderr: string, exitCode: int}
     */
    private function execute(string $binPath): array
    {
        $descriptors = [
            0 => ['pipe', 'r'], // stdin
            1 => ['pipe', 'w'], // stdout
            2 => ['pipe', 'w'], // stderr
        ];

        $process = proc_open([PHP_BINARY, $binPath], $descriptors, $pipes);

        $this->assertIsResource($process, 'Failed to start the signal binary.');

        fclose($pipes[0]);

        $stdout = (string) stream_get_contents($pipes[1]);
        $stderr = (string) stream_get_contents($pipes[2]);

        fclose($pipes[1]);
        fclose($pipes[2]);

        return [
            'stdout' => $stdout,
            'stderr' => $stderr,
            'exitCode' => proc_close($process),
        ];
    }

    private function marker(): string
    {
        return self::MARKER;
    }

    private function removeDirectory(string $directory): void
    {
        if ( ! is_dir($directory)) {
            return;
        }

        foreach (scandir($directory) as $entry) {
            if ('.' === $entry || '..' === $entry) {
                continue;
            }

            $path = $directory . '/' . $entry;

            is_dir($path) ? $this->removeDirectory($path) : unlink($path);
        }

        rmdir($directory);
    }
}
