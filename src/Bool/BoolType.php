<?php

declare(strict_types=1);

namespace StrongType\Bool;

use StrongType\Constraint\ConstraintValidator;

readonly abstract class BoolType implements \JsonSerializable, \Stringable
{
    public function __construct(public bool $value)
    {
        ConstraintValidator::validate($this, $this->value);
    }

    public function getValue(): bool
    {
        return $this->value;
    }

    #[\Override]
    public function jsonSerialize(): bool
    {
        return $this->value;
    }

    #[\Override]
    public function __toString(): string
    {
        return $this->value ? 'true' : 'false';
    }

    /**
     * @return bool[]
     */
    public function __debugInfo(): array
    {
        return [
            'value' => $this->value
        ];
    }
}
