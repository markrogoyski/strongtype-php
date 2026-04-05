<?php

declare(strict_types=1);

namespace StrongType\Constraint;

#[\Attribute(\Attribute::TARGET_CLASS)]
final readonly class DateTimeParseable implements ConstraintInterface
{
    public function validate(mixed $value, string $className): ?string
    {
        $short = \substr($className, \strrpos($className, '\\') + 1);

        if (\is_string($value)) {
            try {
                new \DateTimeImmutable($value);
            } catch (\Exception) {
                return "{$short} type must be a parseable date/time string, got {$value}";
            }
        }

        return null;
    }

    public function priority(): int
    {
        return 150;
    }
}
