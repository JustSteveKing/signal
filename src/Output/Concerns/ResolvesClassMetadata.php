<?php

declare(strict_types=1);

namespace JustSteveKing\Signal\Output\Concerns;

use JustSteveKing\Signal\Attributes\Action;
use JustSteveKing\Signal\Attributes\Aggregate;
use JustSteveKing\Signal\Attributes\Command;
use JustSteveKing\Signal\Attributes\Controller;
use JustSteveKing\Signal\Attributes\Deprecated;
use JustSteveKing\Signal\Attributes\Event;
use JustSteveKing\Signal\Attributes\Internal;
use JustSteveKing\Signal\Attributes\Job;
use JustSteveKing\Signal\Attributes\Listener;
use JustSteveKing\Signal\Attributes\Middleware;
use JustSteveKing\Signal\Attributes\Module;
use JustSteveKing\Signal\Attributes\Query;
use JustSteveKing\Signal\Attributes\Repository;
use JustSteveKing\Signal\Attributes\Service;
use JustSteveKing\Signal\Attributes\ValueObject;
use JustSteveKing\Signal\Data\ClassDefinition;

/**
 * @phpstan-type ClassMetadataAttribute Action|Aggregate|Command|Controller|Event|Job|Listener|Middleware|Module|Query|Repository|Service|ValueObject
 */
trait ResolvesClassMetadata
{
    private function resolveType(ClassDefinition $definition): string
    {
        return match (true) {
            null !== $definition->attributeOfType(Module::class) => 'module',
            null !== $definition->attributeOfType(Service::class) => 'service',
            null !== $definition->attributeOfType(Repository::class) => 'repository',
            null !== $definition->attributeOfType(Action::class) => 'action',
            null !== $definition->attributeOfType(Controller::class) => 'controller',
            null !== $definition->attributeOfType(Event::class) => 'event',
            null !== $definition->attributeOfType(Listener::class) => 'listener',
            null !== $definition->attributeOfType(Middleware::class) => 'middleware',
            null !== $definition->attributeOfType(Job::class) => 'job',
            null !== $definition->attributeOfType(Command::class) => 'command',
            null !== $definition->attributeOfType(Query::class) => 'query',
            null !== $definition->attributeOfType(Aggregate::class) => 'aggregate',
            null !== $definition->attributeOfType(ValueObject::class) => 'value_object',
            default => 'unknown',
        };
    }

    /**
     * @return ClassMetadataAttribute|null
     */
    private function resolvePrimaryAttribute(ClassDefinition $definition): ?object
    {
        return $definition->attributeOfType(Module::class)
            ?? $definition->attributeOfType(Service::class)
            ?? $definition->attributeOfType(Repository::class)
            ?? $definition->attributeOfType(Action::class)
            ?? $definition->attributeOfType(Controller::class)
            ?? $definition->attributeOfType(Event::class)
            ?? $definition->attributeOfType(Listener::class)
            ?? $definition->attributeOfType(Middleware::class)
            ?? $definition->attributeOfType(Job::class)
            ?? $definition->attributeOfType(Command::class)
            ?? $definition->attributeOfType(Query::class)
            ?? $definition->attributeOfType(Aggregate::class)
            ?? $definition->attributeOfType(ValueObject::class);
    }

    private function resolveDescription(ClassDefinition $definition): string
    {
        $attribute = $this->resolvePrimaryAttribute($definition);

        if (null === $attribute) {
            return '';
        }

        return $attribute->description;
    }

    /**
     * @return list<string>
     */
    private function resolveTags(ClassDefinition $definition): array
    {
        $attribute = $this->resolvePrimaryAttribute($definition);

        if (null === $attribute) {
            return [];
        }

        return $attribute->tags;
    }

    private function isDeprecated(ClassDefinition $definition): bool
    {
        return null !== $definition->attributeOfType(Deprecated::class);
    }

    private function isInternal(ClassDefinition $definition): bool
    {
        return null !== $definition->attributeOfType(Internal::class);
    }
}
