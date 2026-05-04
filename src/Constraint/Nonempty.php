<?php

declare(strict_types=1);

namespace StrongType\Constraint;

use StrongType\Util\ShortClassName;

#[\Attribute(\Attribute::TARGET_CLASS)]
final readonly class Nonempty implements ConstraintInterface
{
    #[\Override]
    public function validate(mixed $value, string $className): ?string
    {
        $short = ShortClassName::of($className);

        if (\is_string($value) && \strlen($value) === 0) {
            return "{$short} type must not be empty, got {$value}";
        }

        if (\is_array($value) && \count($value) === 0) {
            return "{$short} type must not be empty, got " . \print_r($value, true);
        }

        return null;
    }

    #[\Override]
    public function priority(): int
    {
        return 50;
    }
}
