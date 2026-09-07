<?php

declare(strict_types=1);

namespace StrongType\Constraint;

/**
 * Declares a constraint applicable to string values only. Shared by every built-in
 * string constraint (`Pattern`, `MinLength`, `Email`, ...) to avoid restating
 * `return [ValueType::String];` in each one.
 */
trait StringValueType
{
    /**
     * @return non-empty-list<ValueType>
     */
    #[\Override]
    public function acceptedValueTypes(): array
    {
        return [ValueType::String];
    }
}
