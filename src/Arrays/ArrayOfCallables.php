<?php

declare(strict_types=1);

namespace StrongType\Arrays;

use StrongType\Exception\StrongTypeException;

class ArrayOfCallables extends NonemptyArray
{
    /**
     * @param callable[] $values
     */
    public function __construct(array $values)
    {
        parent::__construct($values);

        if (!\array_all($this->values, fn($v) => \is_callable($v))) {
            throw new StrongTypeException('ArrayOfCallables type values must be callables');
        }
    }

    #[\Override]
    public function current(): callable
    {
        /** @var callable $current */
        $current = \current($this->values);
        return $current;
    }
}
