<?php

declare(strict_types=1);

namespace StrongType\Arrays;

use StrongType\Exception\StrongTypeException;

class ArrayOfBools extends NonemptyArray
{
    /**
     * @param bool[] $values
     */
    public function __construct(array $values)
    {
        parent::__construct($values);

        if (!\array_all($this->values, fn($v) => \is_bool($v))) {
            throw new StrongTypeException('ArrayOfBools type values must be bools');
        }
    }

    #[\Override]
    public function current(): bool
    {
        /** @var bool $current */
        $current = \current($this->values);
        return $current;
    }
}
