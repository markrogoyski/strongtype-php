<?php

declare(strict_types=1);

namespace StrongType\Constraint;

use StrongType\Util\ShortClassName;

#[\Attribute(\Attribute::TARGET_CLASS)]
final readonly class Base64 implements ConstraintInterface
{
    #[\Override]
    public function validate(mixed $value, string $className): ?string
    {
        if (!\is_string($value)) {
            return null;
        }

        $short = ShortClassName::of($className);
        $error = "{$short} type must be valid base64, got {$value}";

        // Canonical RFC 4648 §4: standard alphabet, length is a multiple of 4,
        // 0–2 padding chars at the end only. Base64URL alphabet (-/_) is rejected.
        if (\preg_match('#\A(?:[A-Za-z0-9+/]{4})*(?:[A-Za-z0-9+/]{2}==|[A-Za-z0-9+/]{3}=)?\z#', $value) !== 1) {
            return $error;
        }

        $decoded = \base64_decode($value, true);
        if ($decoded === false || \base64_encode($decoded) !== $value) {
            return $error;
        }

        return null;
    }

    #[\Override]
    public function priority(): int
    {
        return 100;
    }
}
