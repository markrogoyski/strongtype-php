<?php

declare(strict_types=1);

namespace StrongType\Constraint;

/**
 * Declares a constraint applicable to integer values only. Shared by every built-in
 * int-only constraint (`Even`, `DivisibleBy`, `InFuture`, ...) to avoid restating
 * `return [ValueType::Int];` in each one. Each user still documents its own reason for
 * being int-only (e.g. a modulo predicate, or a Unix-timestamp comparison) next to its
 * `use IntValueType;` line -- the trait only removes the duplicated mechanics.
 */
trait IntValueType
{
    /**
     * @return non-empty-list<ValueType>
     */
    #[\Override]
    public function acceptedValueTypes(): array
    {
        return [ValueType::Int];
    }
}
