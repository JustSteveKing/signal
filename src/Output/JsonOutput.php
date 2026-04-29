<?php

declare(strict_types=1);

namespace JustSteveKing\Signal\Output;

use JustSteveKing\Signal\Attributes\Authorize;
use JustSteveKing\Signal\Attributes\Cached;
use JustSteveKing\Signal\Attributes\DependsOn;
use JustSteveKing\Signal\Attributes\Deprecated;
use JustSteveKing\Signal\Attributes\Emits;
use JustSteveKing\Signal\Attributes\Internal;
use JustSteveKing\Signal\Attributes\Job;
use JustSteveKing\Signal\Attributes\ListensTo;
use JustSteveKing\Signal\Attributes\Middleware;
use JustSteveKing\Signal\Attributes\Route;
use JustSteveKing\Signal\Attributes\SideEffect;
use JustSteveKing\Signal\Attributes\Throws;
use JustSteveKing\Signal\Attributes\Validates;
use JustSteveKing\Signal\Data\ClassDefinition;
use JustSteveKing\Signal\Data\MethodDefinition;
use JustSteveKing\Signal\Output\Concerns\ResolvesClassMetadata;

final readonly class JsonOutput
{
    use ResolvesClassMetadata;

    /**
     * @param list<ClassDefinition> $definitions
     */
    public function generate(array $definitions): string
    {
        $output = [
            'generated_at' => date('c'),
            'classes' => array_map(
                callback: fn(ClassDefinition $definition): array => $this->serializeClass($definition),
                array: $definitions,
            ),
        ];

        return json_encode(
            value: $output,
            flags: JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_THROW_ON_ERROR,
        );
    }

    /**
     * @return array<string, mixed>
     */
    private function serializeClass(ClassDefinition $definition): array
    {
        $middleware = $definition->attributeOfType(Middleware::class);
        $job = $definition->attributeOfType(Job::class);

        return [
            'name' => $definition->name,
            'namespace' => $definition->namespace,
            'fully_qualified_name' => $definition->fullyQualifiedName(),
            'file' => $definition->file,
            'type' => $this->resolveType($definition),
            'description' => $this->resolveDescription($definition),
            'tags' => $this->resolveTags($definition),
            'priority' => $middleware?->priority,
            'queue' => $job?->queue,
            'depends_on' => array_map(
                callback: fn(DependsOn $attribute): array => [
                    'class' => $attribute->class,
                    'description' => $attribute->description,
                ],
                array: $definition->attributesOfType(DependsOn::class),
            ),
            'listens_to' => array_map(
                callback: fn(ListensTo $attribute): array => [
                    'event' => $attribute->event,
                    'description' => $attribute->description,
                    'tags' => $attribute->tags,
                ],
                array: $definition->attributesOfType(ListensTo::class),
            ),
            'deprecated' => $this->serializeDeprecatedAttribute($definition->attributeOfType(Deprecated::class)),
            'internal' => $this->serializeInternalAttribute($definition->attributeOfType(Internal::class)),
            'methods' => array_map(
                callback: fn(MethodDefinition $method): array => $this->serializeMethod($method),
                array: $definition->methods,
            ),
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function serializeMethod(MethodDefinition $method): array
    {
        $route = $method->attributeOfType(Route::class);
        $deprecated = $method->attributeOfType(Deprecated::class);
        $cached = $method->attributeOfType(Cached::class);

        return [
            'name' => $method->name,
            'route' => null === $route ? null : [
                'method' => $route->method,
                'path' => $route->path,
                'description' => $route->description,
                'tags' => $route->tags,
            ],
            'deprecated' => null === $deprecated ? null : [
                'reason' => $deprecated->reason,
                'since' => $deprecated->since,
            ],
            'cached' => null === $cached ? null : [
                'ttl' => $cached->ttl,
                'key' => $cached->key,
                'description' => $cached->description,
            ],
            'authorize' => array_map(
                callback: fn(Authorize $attribute): array => [
                    'ability' => $attribute->ability,
                    'description' => $attribute->description,
                ],
                array: $method->attributesOfType(Authorize::class),
            ),
            'validates' => array_map(
                callback: fn(Validates $attribute): array => [
                    'field' => $attribute->field,
                    'rules' => $attribute->rules,
                    'description' => $attribute->description,
                ],
                array: $method->attributesOfType(Validates::class),
            ),
            'side_effects' => array_map(
                callback: fn(SideEffect $attribute): array => [
                    'description' => $attribute->description,
                    'tags' => $attribute->tags,
                ],
                array: $method->attributesOfType(SideEffect::class),
            ),
            'emits' => array_map(
                callback: fn(Emits $attribute): array => [
                    'event' => $attribute->event,
                    'description' => $attribute->description,
                    'tags' => $attribute->tags,
                ],
                array: $method->attributesOfType(Emits::class),
            ),
            'throws' => array_map(
                callback: fn(Throws $attribute): array => [
                    'exception' => $attribute->exception,
                    'description' => $attribute->description,
                ],
                array: $method->attributesOfType(Throws::class),
            ),
        ];
    }

    /**
     * @return array{reason: string, since: string}|null
     */
    private function serializeDeprecatedAttribute(?Deprecated $attribute): ?array
    {
        if (null === $attribute) {
            return null;
        }

        return ['reason' => $attribute->reason, 'since' => $attribute->since];
    }

    /**
     * @return array{reason: string}|null
     */
    private function serializeInternalAttribute(?Internal $attribute): ?array
    {
        if (null === $attribute) {
            return null;
        }

        return ['reason' => $attribute->reason];
    }
}
