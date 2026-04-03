<?php

declare(strict_types=1);

namespace StrongType\Arrays;

use StrongType\Exception\StrongTypeException;
use StrongType\Util\Stringify;

class ArrayOfIterables extends NonemptyArray
{
    /**
     * @param iterable<mixed>[] $values
     */
    public function __construct(array $values)
    {
        parent::__construct($values);
        $this->validateArrayOfIterables();
    }

    private function validateArrayOfIterables(): void
    {
        foreach ($this->values as $value) {
            if (!\is_iterable($value)) {
                throw new StrongTypeException('ArrayOfIterables type values must be iterables, got ' . Stringify::value($value) . ' as a value');
            }
        }
    }

    /**
     * @return iterable<mixed>
     */
    public function current(): iterable
    {
        /** @var iterable<mixed> $current */
        $current = \current($this->values);
        return $current;
    }
}
