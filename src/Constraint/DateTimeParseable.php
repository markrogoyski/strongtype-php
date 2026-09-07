<?php

declare(strict_types=1);

namespace StrongType\Constraint;

use StrongType\Util\ShortClassName;

#[\Attribute(\Attribute::TARGET_CLASS)]
final readonly class DateTimeParseable implements ConstraintInterface
{
    use StringValueType;

    #[\Override]
    public function validate(mixed $value, string $className): ?string
    {
        $short = ShortClassName::of($className);

        if (\is_string($value)) {
            // new \DateTimeImmutable() tolerates leading/trailing whitespace — not just
            // ASCII space/tab/newline but Unicode separators like U+00A0 (no-break space)
            // and U+202F (narrow no-break space) — and would otherwise store it verbatim.
            // \trim() only strips ASCII, so match \p{Z} (separator) and \p{C} (control)
            // at either edge instead. A null return (invalid UTF-8) never equals $value,
            // so it falls through to the same rejection.
            if (\preg_replace('/\A[\p{C}\p{Z}]+|[\p{C}\p{Z}]+\z/u', '', $value) !== $value) {
                return "{$short} type must be a parseable date/time string, got {$value}";
            }
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
