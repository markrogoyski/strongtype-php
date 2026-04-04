<?php

declare(strict_types=1);

namespace StrongType\Bool;

readonly abstract class BoolType implements \JsonSerializable, \Stringable
{
    public function __construct(public bool $value)
    {
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
