<?php

declare(strict_types=1);

namespace StrongType\Constraint;

/**
 * Declares a constraint applicable to int and float values (the library's "numeric"
 * assignment: `Min`, `Max`, `InRange`, `Positive`, `Negative`, `Nonnegative`,
 * `Nonpositive`, `Nonzero`). Shared to avoid restating
 * `return [ValueType::Int, ValueType::Float];` in each one.
 */
trait NumericValueType
{
    /**
     * @return non-empty-list<ValueType>
     */
    #[\Override]
    public function acceptedValueTypes(): array
    {
        return [ValueType::Int, ValueType::Float];
    }
}
