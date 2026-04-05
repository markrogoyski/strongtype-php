<?php

declare(strict_types=1);

namespace StrongType\Constraint;

#[\Attribute(\Attribute::TARGET_CLASS)]
final readonly class ElementType implements ConstraintInterface
{
    public function __construct(private string $type)
    {
    }

    public function validate(mixed $value, string $className): ?string
    {
        if (!\is_array($value)) {
            return null;
        }

        $short = \substr($className, \strrpos($className, '\\') + 1);

        $checker = match ($this->type) {
            'string'   => fn(mixed $v): bool => \is_string($v),
            'int'      => fn(mixed $v): bool => \is_int($v),
            'float'    => fn(mixed $v): bool => \is_float($v),
            'bool'     => fn(mixed $v): bool => \is_bool($v),
            'array'    => fn(mixed $v): bool => \is_array($v),
            'object'   => fn(mixed $v): bool => \is_object($v),
            'callable' => fn(mixed $v): bool => \is_callable($v),
            'resource' => fn(mixed $v): bool => \is_resource($v),
            'iterable' => fn(mixed $v): bool => \is_iterable($v),
            default    => fn(mixed $v): bool => $v instanceof $this->type,
        };

        if (!\array_all($value, $checker)) {
            return "{$short} type values must be {$this->type}";
        }

        return null;
    }

    public function priority(): int
    {
        return 100;
    }
}
