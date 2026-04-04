<?php

declare(strict_types=1);

namespace StrongType\Arrays;

use StrongType\Exception\StrongTypeException;

class ArrayOfFloats extends NonemptyArray
{
    /**
     * @param float[] $values
     */
    public function __construct(array $values)
    {
        parent::__construct($values);

        if (!\array_all($this->values, fn($v) => \is_float($v))) {
            throw new StrongTypeException('ArrayOfFloats type values must be floats');
        }
    }

    #[\Override]
    public function current(): float
    {
        /** @var float $current */
        $current = \current($this->values);
        return $current;
    }
}
