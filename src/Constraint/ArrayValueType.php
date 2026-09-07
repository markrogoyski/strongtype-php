<?php

declare(strict_types=1);

namespace StrongType\Constraint;

/**
 * Declares a constraint applicable to array values only. Shared by every built-in
 * array constraint (`Unique`, `MinCount`, `IsList`, ...) to avoid restating
 * `return [ValueType::Array];` in each one.
 */
trait ArrayValueType
{
    /**
     * @return non-empty-list<ValueType>
     */
    #[\Override]
    public function acceptedValueTypes(): array
    {
        return [ValueType::Array];
    }
}
