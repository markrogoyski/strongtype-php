<?php

declare(strict_types=1);

namespace StrongType\Constraint;

use StrongType\Exception\ConstraintConfigurationException;
use StrongType\Util\ShortClassName;

#[\Attribute(\Attribute::TARGET_CLASS)]
final readonly class DateFormat implements ConstraintInterface
{
    use StringValueType;

    /**
     * '!' prepended to $format, precomputed once since $format never changes after
     * construction. Resets fields absent from the format to the Unix epoch, so partial
     * formats (e.g. 'm-d') validate identically on any day instead of inheriting the
     * current wall-clock year. Used for parsing only -- the round-trip check uses the
     * original format, since format() would emit a leading '!' as a literal character.
     */
    private string $resetParseFormat;

    public function __construct(private string $format)
    {
        if ($format === '') {
            throw new ConstraintConfigurationException('DateFormat: format must not be empty');
        }
        // Unescaped '!' and '|' are parse-only reset directives: createFromFormat
        // consumes them, but format() echoes them as literal characters, so a format
        // containing either can never round-trip. A backslash-escaped '\!' or '\|' is
        // a literal character in both directions and round-trips fine, so only bare
        // occurrences are rejected.
        if (self::hasUnescapedResetDirective($format)) {
            throw new ConstraintConfigurationException("DateFormat: format must not contain an unescaped '!' or '|', got {$format}");
        }
        $this->resetParseFormat = '!' . $format;
    }

    private static function hasUnescapedResetDirective(string $format): bool
    {
        $length = \strlen($format);
        for ($i = 0; $i < $length; $i++) {
            if ($format[$i] === '\\') {
                $i++;
                continue;
            }
            if ($format[$i] === '!' || $format[$i] === '|') {
                return true;
            }
        }

        return false;
    }

    #[\Override]
    public function validate(mixed $value, string $className): ?string
    {
        $short = ShortClassName::of($className);

        if (\is_string($value)) {
            // createFromFormat() throws ValueError for a NUL byte in $value (it never
            // returns false for that case) -- a shape createFromFormat itself rejects, not
            // a value this constraint should let escape as an uncaught engine exception.
            //
            // The $timezone argument is a fallback used only when $format carries no
            // timezone info of its own (per the DateTimeImmutable::createFromFormat
            // contract, it's ignored otherwise) -- without it, a purely calendar format
            // like 'Y-m-d' would parse against the process's default timezone, which for
            // a handful of historical transitions (e.g. Pacific/Apia skipped 2011-12-30
            // entirely) makes the same input string pass or fail depending on server
            // configuration a calendar-only format never referenced. Fixing it to UTC
            // makes validation deterministic regardless of date_default_timezone_set().
            try {
                $parsed = \DateTimeImmutable::createFromFormat(
                    $this->resetParseFormat,
                    $value,
                    new \DateTimeZone('UTC'),
                );
            } catch (\ValueError) {
                return "{$short} type must match date format {$this->format}, got {$value}";
            }
            if ($parsed === false || $parsed->format($this->format) !== $value) {
                return "{$short} type must match date format {$this->format}, got {$value}";
            }
        }

        return null;
    }

    #[\Override]
    public function priority(): int
    {
        return 100;
    }
}
