<?php

declare(strict_types=1);

namespace StrongType\Constraint;

use StrongType\Util\ShortClassName;

/**
 * Validates RFC 3339 §5.6 date-time strings.
 *
 * Accepted: full date-time with `T` separator (case-insensitive), optional fractional
 * seconds of any length, and a `Z` / `±HH:MM` offset. The unknown-offset form `-00:00`
 * is also accepted.
 *
 * Fractional seconds are validated by grammar only and stripped before the
 * calendar-validity round-trip — so any digit count is allowed.
 *
 * Leap seconds (`:60`) are accepted syntactically: the seconds field is rewritten to
 * `:59` for parsing, so the constraint does not check that the minute in question is
 * an actual inserted leap second from IERS bulletins.
 *
 * Calendar validity (e.g., Feb 30, hour 25) is rejected via a round-trip comparison
 * against `DATE_RFC3339` after fraction-stripping.
 */
#[\Attribute(\Attribute::TARGET_CLASS)]
final readonly class Rfc3339 implements ConstraintInterface
{
    #[\Override]
    public function validate(mixed $value, string $className): ?string
    {
        if (!\is_string($value)) {
            return null;
        }

        // RFC 3339 §5.6: time-secfrac = "." 1*DIGIT; seconds 00-60 (leap seconds).
        // RFC 3339 §4.3: "-00:00" denotes UTC with unknown local offset.
        $pattern = '/^(\d{4}-\d{2}-\d{2})T(\d{2}:\d{2}):(\d{2})(\.\d+)?(Z|[+-](?:[01]\d|2[0-3]):[0-5]\d)$/i';
        if (\preg_match($pattern, $value, $matches) !== 1 || !$this->roundTrips($matches)) {
            $short = ShortClassName::of($className);
            return "{$short} type must be a valid RFC 3339 datetime, got {$value}";
        }

        return null;
    }

    /** @param array<array-key, string> $matches */
    private function roundTrips(array $matches): bool
    {
        // Substitute :60 with :59 so DateTimeImmutable accepts it for date-validity checking.
        $parseSeconds = ($matches[3] === '60') ? '59' : $matches[3];
        // Z and -00:00 both parse as UTC; use +00:00 for the round-trip comparison.
        $parseOffset = (\strcasecmp($matches[5], 'Z') === 0 || $matches[5] === '-00:00') ? '+00:00' : $matches[5];
        $base = "{$matches[1]}T{$matches[2]}:{$parseSeconds}{$parseOffset}";

        $parsed = \DateTimeImmutable::createFromFormat(\DATE_RFC3339, $base);
        // $base is rebuilt from regex groups in canonical form, so createFromFormat
        // never actually returns false here; the check remains for type narrowing
        // and is folded into the (reachable) calendar-validity gate. getLastErrors()
        // returns false on a clean parse — that is the success path, not a failure.
        $errors = \DateTimeImmutable::getLastErrors();
        if (
            $parsed === false
            || ($errors !== false && ($errors['warning_count'] > 0 || $errors['error_count'] > 0))
        ) {
            return false;
        }

        return $parsed->format(\DATE_RFC3339) === $base;
    }

    #[\Override]
    public function priority(): int
    {
        return 150;
    }
}
