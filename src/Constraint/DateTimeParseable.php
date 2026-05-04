<?php

declare(strict_types=1);

namespace StrongType\Constraint;

use StrongType\Util\ShortClassName;

#[\Attribute(\Attribute::TARGET_CLASS)]
final readonly class DateTimeParseable implements ConstraintInterface
{
    #[\Override]
    public function validate(mixed $value, string $className): ?string
    {
        $short = ShortClassName::of($className);

        if (\is_string($value)) {
            try {
                new \DateTimeImmutable($value);
            } catch (\Exception) {
                return "{$short} type must be a parseable date/time string, got {$value}";
            }
        }

        return null;
    }

    #[\Override]
    public function priority(): int
    {
        return 150;
    }
}
