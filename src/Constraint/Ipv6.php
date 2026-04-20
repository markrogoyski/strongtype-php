<?php

declare(strict_types=1);

namespace StrongType\Constraint;

#[\Attribute(\Attribute::TARGET_CLASS)]
final readonly class Ipv6 implements ConstraintInterface
{
    #[\Override]
    public function validate(mixed $value, string $className): ?string
    {
        $short = \substr($className, (int) \strrpos($className, '\\') + 1);

        if (\is_string($value) && \filter_var($value, \FILTER_VALIDATE_IP, \FILTER_FLAG_IPV6) === false) {
            return "{$short} type must be a valid IPv6 address, got {$value}";
        }

        return null;
    }

    #[\Override]
    public function priority(): int
    {
        return 150;
    }
}
