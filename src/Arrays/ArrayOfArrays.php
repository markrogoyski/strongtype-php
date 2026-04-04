<?php

declare(strict_types=1);

namespace StrongType\Arrays;

use StrongType\Exception\StrongTypeException;

class ArrayOfArrays extends NonemptyArray
{
    /**
     * @param array<mixed>[] $values
     */
    public function __construct(array $values)
    {
        parent::__construct($values);

        if (!\array_all($this->values, fn($v) => \is_array($v))) {
            throw new StrongTypeException('ArrayOfArrays type values must be arrays');
        }
    }

    /**
     * @return array<mixed>
     */
    #[\Override]
    public function current(): array
    {
        /** @var array<mixed> $current */
        $current = \current($this->values);
        return $current;
    }
}
