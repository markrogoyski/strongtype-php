<?php

declare(strict_types=1);

namespace StrongType\DateTime;

use StrongType\Exception\StrongTypeException;
use StrongType\Int\Integer;

readonly class Timestamp extends Integer
{
    public function __construct(int $value)
    {
        parent::__construct($value);

        if ($this->value < 0) {
            throw new StrongTypeException("Timestamp type must be >= 0, got {$this->value}");
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
