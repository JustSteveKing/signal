<?php

declare(strict_types=1);

namespace JustSteveKing\Signal\Data;

final readonly class MethodDefinition
{
    /**
     * @param list<object> $attributes
     */
    public function __construct(
        public string $name,
        public array $attributes,
    ) {}

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
