<?php

declare(strict_types=1);

namespace StrongType\Float;

readonly abstract class FloatingPoint implements \JsonSerializable, \Stringable
{
    public function __construct(public float $value)
    {
    }

    public function getValue(): float
    {
        return $this->value;
    }

    #[\Override]
    public function jsonSerialize(): float
    {
        return $this->value;
    }

    #[\Override]
    public function __toString(): string
    {
        return \strval($this->value);
    }

    /**
     * @return float[]
     */
    public function __debugInfo(): array
    {
        return [
            'value' => $this->value
        ];
    }
}
