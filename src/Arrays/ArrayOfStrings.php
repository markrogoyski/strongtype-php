<?php

declare(strict_types=1);

namespace StrongType\Arrays;

use StrongType\Exception\StrongTypeException;

class ArrayOfStrings extends NonemptyArray
{
    /**
     * @param string[] $values
     */
    public function __construct(array $values)
    {
        parent::__construct($values);

        if (!\array_all($this->values, fn($v) => \is_string($v))) {
            throw new StrongTypeException('ArrayOfStrings type values must be strings');
        }
    }

    #[\Override]
    public function current(): string
    {
        /** @var string $current */
        $current = \current($this->values);
        return $current;
    }
}
