<?php

declare(strict_types=1);

namespace StrongType\String;

use StrongType\Exception\StrongTypeException;

readonly class HexColorString extends NonemptyString
{
    public function __construct(string $value)
    {
        parent::__construct($value);

        if (!\preg_match('/^#([0-9a-fA-F]{3}|[0-9a-fA-F]{6})$/', $this->value)) {
            throw new StrongTypeException("HexColorString type must be a valid hex color (#RGB or #RRGGBB), got {$this->value}");
        }
    }
}
