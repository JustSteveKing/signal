<?php

declare(strict_types=1);

namespace JustSteveKing\Signal\Reflector;

use JustSteveKing\Signal\Data\ClassDefinition;
use JustSteveKing\Signal\Data\MethodDefinition;
use ReflectionAttribute;
use ReflectionClass;

/**
 * @phpstan-type PhpToken array{int, string, int}
 */
final readonly class Reflector
{
    public function reflect(string $file): ?ClassDefinition
    {
        $className = $this->classFromFile($file);

        if (null === $className || ! class_exists($className)) {
            return null;
        }

        $reflection = new ReflectionClass($className);

        $classAttributes = $this->extractAttributes($reflection->getAttributes());

        if (empty($classAttributes)) {
            return null;
        }

        $methods = [];

        foreach ($reflection->getMethods() as $method) {
            $methodAttributes = $this->extractAttributes($method->getAttributes());

            if (empty($methodAttributes)) {
                continue;
            }

            $methods[] = new MethodDefinition(
                name: $method->getName(),
                attributes: $methodAttributes,
            );
        }

        return new ClassDefinition(
            name: $reflection->getShortName(),
            namespace: $reflection->getNamespaceName(),
            file: $file,
            attributes: $classAttributes,
            methods: $methods,
        );
    }

    /**
     * @param list<ReflectionAttribute<object>> $reflectionAttributes
     * @return list<object>
     */
    private function extractAttributes(array $reflectionAttributes): array
    {
        $attributes = [];

        foreach ($reflectionAttributes as $attribute) {
            $name = $attribute->getName();

            if ( ! str_starts_with($name, 'JustSteveKing\Signal\Attributes\\')) {
                continue;
            }

            $attributes[] = $attribute->newInstance();
        }

        return $attributes;
    }

    private function classFromFile(string $file): ?string
    {
        $contents = file_get_contents($file);

        if (false === $contents) {
            return null;
        }

        /** @var list<PhpToken|string> $tokens */
        $tokens = token_get_all($contents);
        $namespace = null;
        $class = null;

        foreach ($tokens as $index => $token) {
            if ( ! is_array($token)) {
                continue;
            }

            if (T_NAMESPACE === $token[0]) {
                $namespace = $this->readTokenSequenceFromTokens($tokens, $index);
            }

            if (T_CLASS === $token[0]) {
                // Skip ::class constant syntax
                if ($this->isPrecededByDoubleColon($tokens, $index)) {
                    continue;
                }
                // Skip class: named argument syntax
                if ($this->isFollowedByColon($tokens, $index)) {
                    continue;
                }

                $class = $this->readTokenSequenceFromTokens($tokens, $index);
                break;
            }
        }

        if (null === $namespace || null === $class || '' === $class) {
            return null;
        }

        return $namespace . '\\' . $class;
    }

    /**
     * @param list<PhpToken|string> $tokens
     */
    private function isPrecededByDoubleColon(array $tokens, int $index): bool
    {
        for ($i = $index - 1; $i >= 0; $i--) {
            if (is_array($tokens[$i]) && T_WHITESPACE === $tokens[$i][0]) {
                continue;
            }

            return is_array($tokens[$i]) && T_DOUBLE_COLON === $tokens[$i][0];
        }

        return false;
    }

    /**
     * @param list<PhpToken|string> $tokens
     */
    private function isFollowedByColon(array $tokens, int $index): bool
    {
        for ($i = $index + 1, $count = count($tokens); $i < $count; $i++) {
            if (is_array($tokens[$i]) && T_WHITESPACE === $tokens[$i][0]) {
                continue;
            }

            return is_string($tokens[$i]) && ':' === $tokens[$i];
        }

        return false;
    }

    /**
     * @param list<PhpToken|string> $tokens
     */
    private function readTokenSequenceFromTokens(array $tokens, int $startIndex): string
    {
        $result = '';

        for ($i = $startIndex + 1, $count = count($tokens); $i < $count; $i++) {
            $token = $tokens[$i];

            if (is_array($token) && in_array($token[0], [T_STRING, T_NS_SEPARATOR, T_NAME_QUALIFIED], strict: true)) {
                $result .= $token[1];
                continue;
            }

            if (is_string($token) && '{' === $token) {
                break;
            }

            if (is_array($token) && T_WHITESPACE === $token[0]) {
                if ('' !== $result) {
                    break;
                }
                continue;
            }
        }

        return mb_trim($result);
    }
}
