<?php

declare(strict_types=1);

namespace StrongType\Constraint;

interface ConstraintInterface
{
    /**
     * Return null on success, error message string on failure.
     */
    public function validate(mixed $value, string $className): ?string;

    /**
     * Lower runs first. Range=50, format=100, semantic=150.
     */
    public function priority(): int;

    /**
     * The value types this constraint can actually engage on.
     *
     * `ConstraintValidator` rejects the type definition with a
     * `ConstraintConfigurationException` when the class's `VALUE_TYPE` is not in this set,
     * so a constraint that would silently never fire becomes a loud, definition-time error.
     * Declare only the types `validate()` genuinely inspects; `use AnyValueType;` covers a
     * constraint that is meaningful on all of them.
     *
     * An empty list means the constraint can never engage anywhere -- only combinators
     * produce one, from children with no value type in common.
     *
     * @return list<ValueType>
     */
    public function acceptedValueTypes(): array;
}
