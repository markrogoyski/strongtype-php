<?php

declare(strict_types=1);

namespace StrongType\Bool;

abstract class BoolType implements \JsonSerializable
{
    protected bool $value;

    public function __construct(bool $value)
    {
        $this->value = $value;
    }

    public function getValue(): bool
    {
        return $this->value;
    }

    public function jsonSerialize(): bool
    {
        return $this->value;
    }

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
