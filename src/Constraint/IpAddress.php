<?php

declare(strict_types=1);

namespace StrongType\Constraint;

use StrongType\Util\ShortClassName;

#[\Attribute(\Attribute::TARGET_CLASS)]
final readonly class IpAddress implements ConstraintInterface
{
    #[\Override]
    public function validate(mixed $value, string $className): ?string
    {
        $short = ShortClassName::of($className);

        if (\is_string($value) && \filter_var($value, \FILTER_VALIDATE_IP) === false) {
            return "{$short} type must be a valid IP address, got {$value}";
        }

        return null;
    }

    #[\Override]
    public function priority(): int
    {
        return 150;
    }
}
