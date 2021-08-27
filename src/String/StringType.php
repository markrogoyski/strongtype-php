<?php

declare(strict_types=1);

namespace StrongType\String;

abstract class StringType implements \JsonSerializable
{
    protected string $value;

    public function __construct(string $value)
    {
        $this->value = $value;
    }

    public function getValue(): string
    {
        return $this->value;
    }

    public function jsonSerialize(): string
    {
        return $this->value;
    }

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
