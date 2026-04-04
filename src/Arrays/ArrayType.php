<?php

declare(strict_types=1);

namespace StrongType\Arrays;

/**
 * @implements \Iterator<mixed, mixed>
 */
abstract class ArrayType implements \JsonSerializable, \Countable, \Iterator, \Stringable
{
    /**
     * @param mixed[] $values
     */
    public function __construct(public protected(set) array $values)
    {
    }

    /**
     * @return mixed[]
     */
    public function getValues(): array
    {
        return $this->values;
    }

    /**
     * @return mixed[]
     */
    #[\Override]
    public function jsonSerialize(): array
    {
        return $this->values;
    }

    #[\Override]
    public function __toString(): string
    {
        return \json_encode($this->values, \JSON_THROW_ON_ERROR);
    }

    #[\Override]
    public function count(): int
    {
        return \count($this->values);
    }

    /**
     * @return mixed[]
     */
    public function __debugInfo(): array
    {
        return [
            'values' => $this->values
        ];
    }

    #[\Override]
    public function rewind(): void
    {
        \reset($this->values);
    }

    #[\Override]
    public function current(): mixed
    {
        return \current($this->values);
    }

    #[\Override]
    public function key(): mixed
    {
        return \key($this->values);
    }

    #[\Override]
    public function next(): void
    {
        \next($this->values);
    }

    #[\Override]
    public function valid(): bool
    {
        return \key($this->values) !== null;
    }
}
