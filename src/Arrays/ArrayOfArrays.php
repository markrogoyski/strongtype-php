<?php

declare(strict_types=1);

namespace StrongType\Arrays;

use StrongType\Exception\StrongTypeException;
use StrongType\Util\Stringify;

class ArrayOfArrays extends NonemptyArray
{
    /**
     * @param array<mixed>[] $values
     */
    public function __construct(array $values)
    {
        parent::__construct($values);
        $this->validateArrayOfArrays();
    }

    private function validateArrayOfArrays(): void
    {
        foreach ($this->values as $value) {
            if (!is_array($value)) {
                throw new StrongTypeException('ArrayOfArrays type values must be arrays, got ' . Stringify::value($value) . ' as a value');
            }
        }
    }

    /**
     * @return array<mixed>
     */
    public function current(): array
    {
        /** @var array<mixed> $current */
        $current = \current($this->values);
        return $current;
    }
}
