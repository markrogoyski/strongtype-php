<?php

declare(strict_types=1);

namespace StrongType\Int;

use StrongType\Constraint\ConstraintValidator;

readonly abstract class Integer implements \JsonSerializable, \Stringable
{
    public function __construct(public int $value)
    {
        ConstraintValidator::validate($this, $this->value);
    }

    public function getValue(): int
    {
        return $this->value;
    }

    #[\Override]
    public function jsonSerialize(): int
    {
        return $this->value;
    }

    #[\Override]
    public function __toString(): string
    {
        return \strval($this->value);
    }

    /**
     * @return int[]
     */
    public function __debugInfo(): array
    {
        return [
            'value' => $this->value
        ];
    }
}
