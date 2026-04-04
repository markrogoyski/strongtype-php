<?php

declare(strict_types=1);

namespace StrongType\Arrays;

use StrongType\Exception\StrongTypeException;

class ArrayOfObjects extends NonemptyArray
{
    /**
     * @param object[] $values
     */
    public function __construct(array $values)
    {
        parent::__construct($values);

        if (!\array_all($this->values, fn($v) => \is_object($v))) {
            throw new StrongTypeException('ArrayOfObjects type values must be objects');
        }
    }

    #[\Override]
    public function current(): object
    {
        /** @var object $current */
        $current = \current($this->values);
        return $current;
    }
}
