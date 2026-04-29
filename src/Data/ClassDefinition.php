<?php

declare(strict_types=1);

namespace JustSteveKing\Signal\Data;

final readonly class ClassDefinition
{
    /**
     * @param list<object> $attributes
     * @param list<MethodDefinition> $methods
     */
    public function __construct(
        public string $name,
        public string $namespace,
        public string $file,
        public array $attributes,
        public array $methods,
    ) {}

    public function fullyQualifiedName(): string
    {
        return $this->namespace . '\\' . $this->name;
    }

    public function hasMethod(): bool
    {
        return [] !== $this->methods;
    }

    /**
     * @template T of object
     * @param class-string<T> $type
     * @return T|null
     */
    public function attributeOfType(string $type): ?object
    {
        foreach ($this->attributes as $attribute) {
            if ($attribute instanceof $type) {
                return $attribute;
            }
        }

        return null;
    }

    /**
     * @template T of object
     * @param class-string<T> $type
     * @return list<T>
     */
    public function attributesOfType(string $type): array
    {
        return array_values(
            array: array_filter(
                array: $this->attributes,
                callback: fn(object $attribute): bool => $attribute instanceof $type,
            ),
        );
    }
}
