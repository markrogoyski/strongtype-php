<?php

declare(strict_types=1);

namespace StrongType\Float;

abstract class FloatingPoint implements \JsonSerializable
{
    protected float $value;

    public function __construct(float $value)
    {
        $this->value = $value;
    }

    public function getValue(): float
    {
        return $this->value;
    }

    public function jsonSerialize(): float
    {
        return $this->value;
    }

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
