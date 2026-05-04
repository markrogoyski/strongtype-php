<?php

declare(strict_types=1);

namespace StrongType\Constraint;

use StrongType\Util\ShortClassName;

#[\Attribute(\Attribute::TARGET_CLASS)]
final readonly class Alphanumeric implements ConstraintInterface
{
    #[\Override]
    public function validate(mixed $value, string $className): ?string
    {
        $short = ShortClassName::of($className);

        if (\is_string($value) && !\ctype_alnum($value)) {
            return "{$short} type must only contain alphanumeric characters, got {$value}";
        }

        return null;
    }

    #[\Override]
    public function priority(): int
    {
        return 100;
    }
}
