<?php

declare(strict_types=1);

namespace StrongType\Arrays;

use StrongType\Exception\StrongTypeException;

class ArrayOfIterables extends NonemptyArray
{
    /**
     * @param iterable<mixed>[] $values
     */
    public function __construct(array $values)
    {
        parent::__construct($values);

        if (!\array_all($this->values, fn($v) => \is_iterable($v))) {
            throw new StrongTypeException('ArrayOfIterables type values must be iterables');
        }
    }

    /**
     * @return iterable<mixed>
     */
    #[\Override]
    public function current(): iterable
    {
        /** @var iterable<mixed> $current */
        $current = \current($this->values);
        return $current;
    }
}
