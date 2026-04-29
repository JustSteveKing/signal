<?php

declare(strict_types=1);

namespace JustSteveKing\Signal\Scanner;

use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;
use RuntimeException;
use SplFileInfo;

final readonly class Scanner
{
    /**
     * @param list<string> $exclude
     * @return list<string>
     */
    public function scan(string $path, array $exclude = []): array
    {
        if ( ! is_dir($path)) {
            throw new RuntimeException(
                message: "Input path [{$path}] is not a valid directory.",
            );
        }

        $iterator = new RecursiveIteratorIterator(
            iterator: new RecursiveDirectoryIterator(
                directory: $path,
                flags: RecursiveDirectoryIterator::SKIP_DOTS,
            ),
        );

        $files = [];

        /** @var SplFileInfo $file */
        foreach ($iterator as $file) {
            if ('php' !== $file->getExtension()) {
                continue;
            }

            $realPath = $file->getRealPath();

            if ($this->isExcluded($realPath, $exclude)) {
                continue;
            }

            $files[] = $realPath;
        }

        return $files;
    }

    /**
     * @param list<string> $exclude
     */
    private function isExcluded(string $path, array $exclude): bool
    {
        foreach ($exclude as $excludedPath) {
            $resolvedExcludedPath = realpath($excludedPath);

            if (false !== $resolvedExcludedPath && str_starts_with($path, $resolvedExcludedPath)) {
                return true;
            }
        }

        return false;
    }
}
