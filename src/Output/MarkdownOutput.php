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

final readonly class MarkdownOutput
{
    use ResolvesClassMetadata;

    /**
     * @param list<ClassDefinition> $definitions
     */
    public function generate(array $definitions): string
    {
        $lines = [];

        $lines[] = '# Signal Documentation';
        $lines[] = '';
        $lines[] = '_Generated at ' . date('Y-m-d H:i:s') . '_';
        $lines[] = '';
        $lines[] = '---';
        $lines[] = '';

        $grouped = $this->groupByType($definitions);

        foreach ($grouped as $type => $group) {
            if (empty($group)) {
                continue;
            }

            $lines[] = '## ' . $this->typeLabel($type);
            $lines[] = '';

            foreach ($group as $definition) {
                $lines = array_merge($lines, $this->renderClass($definition));
            }
        }

        return implode("\n", $lines);
    }

    /**
     * @return list<string>
     */
    private function renderClass(ClassDefinition $definition): array
    {
        $lines = [];

        $lines[] = '### ' . $definition->name;
        $lines[] = '';
        $lines[] = '**Namespace:** `' . $definition->namespace . '`';
        $lines[] = '';

        $description = $this->resolveDescription($definition);

        if ('' !== $description) {
            $lines[] = $description;
            $lines[] = '';
        }

        $tags = $this->resolveTags($definition);

        if ( ! empty($tags)) {
            $lines[] = '**Tags:** ' . implode(', ', array_map(
                callback: fn(string $tag): string => '`' . $tag . '`',
                array: $tags,
            ));
            $lines[] = '';
        }

        $middleware = $definition->attributeOfType(Middleware::class);

        if (null !== $middleware) {
            $lines[] = '**Priority:** `' . $middleware->priority . '`';
            $lines[] = '';
        }

        $job = $definition->attributeOfType(Job::class);

        if (null !== $job) {
            $lines[] = '**Queue:** `' . $job->queue . '`';
            $lines[] = '';
        }

        $deprecated = $definition->attributeOfType(Deprecated::class);

        if (null !== $deprecated) {
            $since = '' !== $deprecated->since ? ' since ' . $deprecated->since : '';
            $lines[] = '> **Deprecated**' . $since . ': ' . $deprecated->reason;
            $lines[] = '';
        }

        $internal = $definition->attributeOfType(Internal::class);

        if (null !== $internal) {
            $lines[] = '> **Internal**: ' . $internal->reason;
            $lines[] = '';
        }

        $dependsOn = $definition->attributesOfType(DependsOn::class);

        if ( ! empty($dependsOn)) {
            $lines[] = '**Dependencies:**';
            $lines[] = '';
            $lines[] = '| Class | Description |';
            $lines[] = '|-------|-------------|';

            foreach ($dependsOn as $dependency) {
                $desc = '' !== $dependency->description ? $dependency->description : '—';
                $lines[] = '| `' . $dependency->class . '` | ' . $desc . ' |';
            }

            $lines[] = '';
        }

        $listensTo = $definition->attributesOfType(ListensTo::class);

        if ( ! empty($listensTo)) {
            $lines[] = '**Listens To:**';
            $lines[] = '';
            $lines[] = '| Event | Description | Tags |';
            $lines[] = '|-------|-------------|------|';

            foreach ($listensTo as $listener) {
                $desc = '' !== $listener->description ? $listener->description : '—';
                $tags = $this->tagsCell($listener->tags);
                $lines[] = '| `' . $listener->event . '` | ' . $desc . ' | ' . $tags . ' |';
            }

            $lines[] = '';
        }

        if ($definition->hasMethod()) {
            $lines[] = '**Methods:**';
            $lines[] = '';

            foreach ($definition->methods as $method) {
                $lines = array_merge($lines, $this->renderMethod($method));
            }
        }

        $lines[] = '---';
        $lines[] = '';

        return $lines;
    }

    /**
     * @return list<string>
     */
    private function renderMethod(MethodDefinition $method): array
    {
        $lines = [];

        $lines[] = '#### `' . $method->name . '()`';
        $lines[] = '';

        $route = $method->attributeOfType(Route::class);

        if (null !== $route) {
            $lines[] = '**Route:** `' . mb_strtoupper($route->method) . ' ' . $route->path . '`';
            $lines[] = '';

            if ('' !== $route->description) {
                $lines[] = $route->description;
                $lines[] = '';
            }
        }

        $deprecated = $method->attributeOfType(Deprecated::class);

        if (null !== $deprecated) {
            $since = '' !== $deprecated->since ? ' since ' . $deprecated->since : '';
            $lines[] = '> **Deprecated**' . $since . ': ' . $deprecated->reason;
            $lines[] = '';
        }

        $cached = $method->attributeOfType(Cached::class);

        if (null !== $cached) {
            $key = '' !== $cached->key ? ', key `' . $cached->key . '`' : '';
            $desc = '' !== $cached->description ? ' — ' . $cached->description : '';
            $lines[] = '**Cached:** TTL `' . $cached->ttl . 's`' . $key . $desc;
            $lines[] = '';
        }

        $authorize = $method->attributesOfType(Authorize::class);

        if ( ! empty($authorize)) {
            $lines[] = '**Requires Authorization:**';
            $lines[] = '';
            $lines[] = '| Ability | Description |';
            $lines[] = '|---------|-------------|';

            foreach ($authorize as $auth) {
                $desc = '' !== $auth->description ? $auth->description : '—';
                $lines[] = '| `' . $auth->ability . '` | ' . $desc . ' |';
            }

            $lines[] = '';
        }

        $validates = $method->attributesOfType(Validates::class);

        if ( ! empty($validates)) {
            $lines[] = '**Validates:**';
            $lines[] = '';
            $lines[] = '| Field | Rules | Description |';
            $lines[] = '|-------|-------|-------------|';

            foreach ($validates as $validation) {
                $rules = '' !== $validation->rules ? '`' . $validation->rules . '`' : '—';
                $desc = '' !== $validation->description ? $validation->description : '—';
                $lines[] = '| `' . $validation->field . '` | ' . $rules . ' | ' . $desc . ' |';
            }

            $lines[] = '';
        }

        $sideEffects = $method->attributesOfType(SideEffect::class);

        if ( ! empty($sideEffects)) {
            $lines[] = '**Side Effects:**';
            $lines[] = '';
            $lines[] = '| Description | Tags |';
            $lines[] = '|-------------|------|';

            foreach ($sideEffects as $sideEffect) {
                $lines[] = '| ' . $sideEffect->description . ' | ' . $this->tagsCell($sideEffect->tags) . ' |';
            }

            $lines[] = '';
        }

        $emits = $method->attributesOfType(Emits::class);

        if ( ! empty($emits)) {
            $lines[] = '**Emits:**';
            $lines[] = '';
            $lines[] = '| Event | Description | Tags |';
            $lines[] = '|-------|-------------|------|';

            foreach ($emits as $emit) {
                $desc = '' !== $emit->description ? $emit->description : '—';
                $lines[] = '| `' . $emit->event . '` | ' . $desc . ' | ' . $this->tagsCell($emit->tags) . ' |';
            }

            $lines[] = '';
        }

        $throws = $method->attributesOfType(Throws::class);

        if ( ! empty($throws)) {
            $lines[] = '**Throws:**';
            $lines[] = '';
            $lines[] = '| Exception | Description |';
            $lines[] = '|-----------|-------------|';

            foreach ($throws as $throw) {
                $desc = '' !== $throw->description ? $throw->description : '—';
                $lines[] = '| `' . $throw->exception . '` | ' . $desc . ' |';
            }

            $lines[] = '';
        }

        return $lines;
    }

    /**
     * @param list<ClassDefinition> $definitions
     * @return array<string, list<ClassDefinition>>
     */
    private function groupByType(array $definitions): array
    {
        $groups = [
            'module' => [],
            'service' => [],
            'repository' => [],
            'action' => [],
            'controller' => [],
            'event' => [],
            'listener' => [],
            'middleware' => [],
            'job' => [],
            'command' => [],
            'query' => [],
            'aggregate' => [],
            'value_object' => [],
            'unknown' => [],
        ];

        foreach ($definitions as $definition) {
            $groups[$this->resolveType($definition)][] = $definition;
        }

        return $groups;
    }

    private function typeLabel(string $type): string
    {
        return match ($type) {
            'module' => 'Modules',
            'service' => 'Services',
            'repository' => 'Repositories',
            'action' => 'Actions',
            'controller' => 'Controllers',
            'event' => 'Events',
            'listener' => 'Listeners',
            'middleware' => 'Middleware',
            'job' => 'Jobs',
            'command' => 'Commands',
            'query' => 'Queries',
            'aggregate' => 'Aggregates',
            'value_object' => 'Value Objects',
            default => 'Unknown',
        };
    }

    /**
     * @param list<string> $tags
     */
    private function tagsCell(array $tags): string
    {
        if (empty($tags)) {
            return '—';
        }

        return implode(', ', array_map(
            callback: fn(string $tag): string => '`' . $tag . '`',
            array: $tags,
        ));
    }
}
