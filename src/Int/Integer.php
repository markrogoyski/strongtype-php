<?php

declare(strict_types=1);

namespace StrongType\Int;

abstract class Integer implements \JsonSerializable
{
    protected int $value;

    public function __construct(int $value)
    {
        $this->value = $value;
    }

    public function getValue(): int
    {
        return $this->value;
    }

    public function jsonSerialize(): int
    {
        return $this->value;
    }

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
