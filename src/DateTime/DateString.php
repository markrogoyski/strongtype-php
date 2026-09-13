<?php

declare(strict_types=1);

namespace StrongType\DateTime;

use StrongType\Exception\FormatException;

readonly class DateString extends DateTime
{
    public function __construct(string $value)
    {
        parent::__construct($value);

        if (!\preg_match('/\A\d{4}-\d{2}-\d{2}\z/', $this->value)) {
            throw new FormatException("DateString type must be a valid YYYY-MM-DD date, got {$this->value}");
        }

        [$year, $month, $day] = \array_map('intval', \explode('-', $this->value));
        if (!\checkdate($month, $day, $year)) {
            throw new FormatException("DateString type must be a valid YYYY-MM-DD date, got {$this->value}");
        }
    }

    // Validation-only override: stores $value verbatim via parent::__construct(), never
    // transforms it, so inherited __serialize()/__unserialize() stays safe to reuse.
    #[\Override]
    protected static function constructorPreservesSerializedStateVerbatim(): bool
    {
        return true;
    }
}
