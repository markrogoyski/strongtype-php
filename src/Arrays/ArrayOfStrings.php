<?php

declare(strict_types=1);

namespace StrongType\Arrays;

use StrongType\Exception\StrongTypeException;
use StrongType\Util\Stringify;

class ArrayOfStrings extends NonemptyArray
{
    /**
     * @param string[] $values
     */
    public function __construct(array $values)
    {
        parent::__construct($values);
        $this->validateArrayOfStrings();
    }

    private function validateArrayOfStrings(): void
    {
        foreach ($this->values as $value) {
            if (!is_string($value)) {
                throw new StrongTypeException('ArrayOfStrings type values must be strings, got ' . Stringify::value($value) . ' as a value');
            }
        }
    }

    public function current(): string
    {
        /** @var string $current */
        $current = \current($this->values);
        return $current;
    }
}
