<?php

declare(strict_types=1);

namespace StrongType\Constraint;

use StrongType\Util\ShortClassName;

#[\Attribute(\Attribute::TARGET_CLASS)]
final readonly class IsList implements ConstraintInterface
{
    #[\Override]
    public function validate(mixed $value, string $className): ?string
    {
        $short = ShortClassName::of($className);

        if (\is_array($value) && !\array_is_list($value)) {
            return "{$short} type must be a list (sequential integer keys starting from 0), got a non-list array (" . \count($value) . " element(s))";
        }

        return null;
    }

    #[\Override]
    public function priority(): int
    {
        return 50;
    }
}
