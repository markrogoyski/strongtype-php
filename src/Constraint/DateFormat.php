<?php

declare(strict_types=1);

namespace StrongType\Constraint;

use StrongType\Exception\ConstraintConfigurationException;
use StrongType\Util\ShortClassName;

#[\Attribute(\Attribute::TARGET_CLASS)]
final readonly class DateFormat implements ConstraintInterface
{
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
            // Prepend '!' to the parse format only: it resets fields absent from the
            // format to the Unix epoch, so partial formats (e.g. 'm-d') validate
            // identically on any day instead of inheriting the current wall-clock year.
            // The round-trip check below uses the original format, since format() would
            // emit a leading '!' as a literal character.
            $parsed = \DateTimeImmutable::createFromFormat('!' . $this->format, $value);
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

    /**
     * @return non-empty-list<ValueType>
     */
    #[\Override]
    public function acceptedValueTypes(): array
    {
        return [ValueType::String];
    }
}
