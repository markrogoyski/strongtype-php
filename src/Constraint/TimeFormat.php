<?php

declare(strict_types=1);

namespace StrongType\Constraint;

#[\Attribute(\Attribute::TARGET_CLASS)]
final readonly class TimeFormat implements ConstraintInterface
{
    public function validate(mixed $value, string $className): ?string
    {
        $short = \substr($className, \strrpos($className, '\\') + 1);

        if (\is_string($value) && !\preg_match('/^([01]\d|2[0-3]):([0-5]\d):([0-5]\d)$/', $value)) {
            return "{$short} type must be a valid time (HH:MM:SS), got {$value}";
        }

        return null;
    }

    public function priority(): int
    {
        return 100;
    }
}
