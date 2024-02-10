<?php

declare(strict_types=1);

namespace StrongType\Arrays;

/**
 * @implements \Iterator<mixed, mixed>
 */
abstract class ArrayType implements \JsonSerializable, \Countable, \Iterator
{
    /** @var mixed[]  */
    protected array $values;

    /**
     * @param mixed[] $values
     */
    public function __construct(array $values)
    {
        $this->values = $values;
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
    public function jsonSerialize(): array
    {
        return $this->values;
    }

    public function __toString(): string
    {
        return json_encode($this->values, \JSON_THROW_ON_ERROR);
    }

    public function count(): int
    {
        return count($this->values);
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

    public function rewind()
    {
        \reset($this->values);
    }

    /**
     * @return mixed
     */
    public function current()
    {
        return \current($this->values);
    }

    /**
     * @return mixed
     */
    public function key()
    {
        return \key($this->values);
    }

    public function next(): void
    {
        \next($this->values);
    }

    public function valid(): bool
    {
        return \key($this->values) !== null;
    }
}
