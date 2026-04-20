<?php

declare(strict_types=1);

namespace StrongType\Constraint;

#[\Attribute(\Attribute::TARGET_CLASS)]
final readonly class Cidr implements ConstraintInterface
{
    #[\Override]
    public function validate(mixed $value, string $className): ?string
    {
        $short = \substr($className, (int) \strrpos($className, '\\') + 1);

        if (\is_string($value) && !$this->isValidCidr($value)) {
            return "{$short} type must be valid CIDR notation, got {$value}";
        }

        return null;
    }

    private function isValidCidr(string $value): bool
    {
        $parts = \explode('/', $value);
        if (\count($parts) !== 2 || !\ctype_digit($parts[1])) {
            return false;
        }

        [$ip, $prefix] = $parts;
        $prefixInt = (int) $prefix;

        $isIpv4 = \filter_var($ip, \FILTER_VALIDATE_IP, \FILTER_FLAG_IPV4) !== false;
        $isIpv6 = \filter_var($ip, \FILTER_VALIDATE_IP, \FILTER_FLAG_IPV6) !== false;
        $maxPrefix = $isIpv4 ? 32 : ($isIpv6 ? 128 : -1);

        return $maxPrefix >= 0 && $prefixInt >= 0 && $prefixInt <= $maxPrefix;
    }

    #[\Override]
    public function priority(): int
    {
        return 150;
    }
}
