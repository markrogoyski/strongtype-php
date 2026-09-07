<?php

declare(strict_types=1);

namespace StrongType\Constraint;

use StrongType\Util\ShortClassName;

#[\Attribute(\Attribute::TARGET_CLASS)]
final readonly class InFuture implements ConstraintInterface
{
    // Compares against a Unix timestamp, so only integer types qualify.
    use IntValueType;

    #[\Override]
    public function validate(mixed $value, string $className): ?string
    {
        $short = ShortClassName::of($className);

        if (\is_int($value) && $value <= \time()) {
            return "{$short} type must be in the future, got {$value}";
        }

        return null;
    }

    #[\Override]
    public function priority(): int
    {
        return 100;
    }
}
