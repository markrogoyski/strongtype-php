<?php

declare(strict_types=1);

namespace StrongType\Constraint;

/**
 * Declares a constraint applicable to every value type.
 *
 * Use it in a genuinely type-agnostic custom constraint -- one whose predicate is
 * meaningful for ints, floats, strings, bools and arrays alike -- to satisfy
 * `ConstraintInterface::acceptedValueTypes()` in one line:
 *
 * ```php
 * final readonly class NotForbidden implements ConstraintInterface
 * {
 *     use AnyValueType;
 *     // validate() / priority() ...
 * }
 * ```
 *
 * Prefer an explicit set whenever the constraint actually inspects a specific type: a
 * constraint that guards on `is_string()` and declares "any" is silently dead on every
 * other base, which is exactly what the applicability check exists to prevent.
 */
trait AnyValueType
{
    /**
     * @return non-empty-list<ValueType>
     */
    public function acceptedValueTypes(): array
    {
        return ValueType::cases();
    }
}
