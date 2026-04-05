<?php

declare(strict_types=1);

namespace StrongType\DateTime;

readonly abstract class DateTime implements \JsonSerializable, \Stringable
{
    public function __construct(public string $value)
    {
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
        return $this->value;
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
