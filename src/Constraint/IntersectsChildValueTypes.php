<?php

declare(strict_types=1);

namespace StrongType\Constraint;

/**
 * Shared `acceptedValueTypes()` for combinators whose applicability is the intersection of
 * their children's — used by {@see AnyOf} and {@see AllOf}. {@see Not} does not use this: with
 * exactly one child, its accepted set already *is* that child's, so no intersection is needed.
 *
 * @property-read non-empty-list<ConstraintInterface> $children
 */
trait IntersectsChildValueTypes
{
    /**
     * @return list<ValueType>
     */
    #[\Override]
    public function acceptedValueTypes(): array
    {
        $accepted = ValueType::cases();

        foreach ($this->children as $child) {
            $childAccepted = $child->acceptedValueTypes();
            $accepted = \array_values(\array_filter(
                $accepted,
                static fn(ValueType $valueType): bool => \in_array($valueType, $childAccepted, true),
            ));
        }

        return $accepted;
    }
}
