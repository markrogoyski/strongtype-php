<?php

declare(strict_types=1);

namespace StrongType\Arrays;

use StrongType\Exception\StrongTypeException;
use StrongType\Stringify;

class ArrayOfObjects extends NonemptyArray
{
    /**
     * @param object[] $values
     */
    public function __construct(array $values)
    {
        parent::__construct($values);
        $this->validateArrayOfObjects();
    }

    private function validateArrayOfObjects(): void
    {
        foreach ($this->values as $value) {
            if (!is_object($value)) {
                throw new StrongTypeException('ArrayOfObjects type values must be objects, got ' . Stringify::value($value) . ' as a value');
            }
        }
    }

    public function current(): object
    {
        /** @var object $current */
        $current = \current($this->values);
        return $current;
    }
}
