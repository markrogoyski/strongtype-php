<?php

declare(strict_types=1);

namespace StrongType\String;

use StrongType\Constraint\ConstraintValidator;

readonly abstract class StringType implements \JsonSerializable, \Stringable
{
    public function __construct(public string $value)
    {
        ConstraintValidator::validate($this, $this->value);
    }

    public function getValue(): string
    {
        return $this->value;
    }

    #[\Override]
    public function jsonSerialize(): string
    {
        return $this->value;
    }

    #[\Override]
    public function __toString(): string
    {
        return \strval($this->value);
    }

    /**
     * @return string[]
     */
    public function __debugInfo(): array
    {
        return [
            'value' => $this->value
        ];
    }
}
