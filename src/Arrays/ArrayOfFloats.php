<?php

declare(strict_types=1);

namespace StrongType\Arrays;

use StrongType\Exception\StrongTypeException;
use StrongType\Util\Stringify;

class ArrayOfFloats extends NonemptyArray
{
    /**
     * @param float[] $values
     */
    public function __construct(array $values)
    {
        parent::__construct($values);
        $this->validateArrayOfFloats();
    }

    private function validateArrayOfFloats(): void
    {
        foreach ($this->values as $value) {
            if (!\is_float($value)) {
                throw new StrongTypeException('ArrayOfFloats type values must be floats, got ' . Stringify::value($value) . ' as a value');
            }
        }
    }

    public function current(): float
    {
        /** @var float $current */
        $current = \current($this->values);
        return $current;
    }
}
