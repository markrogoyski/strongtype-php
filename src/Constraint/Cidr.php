<?php

declare(strict_types=1);

namespace StrongType\Constraint;

use StrongType\Util\ShortClassName;

#[\Attribute(\Attribute::TARGET_CLASS)]
final readonly class Cidr implements ConstraintInterface
{
    #[\Override]
    public function validate(mixed $value, string $className): ?string
    {
        $short = ShortClassName::of($className);

        if (\is_string($value) && !$this->isValidCidr($value)) {
            return "{$short} type must be valid CIDR notation, got {$value}";
        }

        return null;
    }

    private function isValidCidr(string $value): bool
    {
        $parts = \explode('/', $value);
        if (\count($parts) !== 2) {
            return false;
        }

        [$ip, $prefix] = $parts;

        // Canonical decimal only: "0" or a leading-zero-free run of digits. A leading
        // zero ("/08", "/00") is not a distinct prefix length, just a second spelling of
        // one, so this gives each prefix length exactly one accepted form. Scope is the
        // prefix length only -- Cidr is a format constraint, so the address half keeps
        // whatever spelling the caller used and host bits are not required to be zero.
        if (\preg_match('/\A(0|[1-9]\d*)\z/', $prefix) !== 1) {
            return false;
        }

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

    /**
     * @return non-empty-list<ValueType>
     */
    #[\Override]
    public function acceptedValueTypes(): array
    {
        return [ValueType::String];
    }
}
