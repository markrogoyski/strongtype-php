<?php

declare(strict_types=1);

namespace StrongType\Constraint;

#[\Attribute(\Attribute::TARGET_CLASS)]
final readonly class Finite implements ConstraintInterface
{
    #[\Override]
    public function validate(mixed $value, string $className): ?string
    {
        $short = \substr($className, (int) \strrpos($className, '\\') + 1);

        if (\is_float($value) && !\is_finite($value)) {
            $display = \is_nan($value) ? 'NAN' : (string) $value;
            return "{$short} type must be a finite number, got {$display}";
        }

        return null;
    }

    #[\Override]
    public function priority(): int
    {
        return 40;
    }
}
