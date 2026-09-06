<?php

declare(strict_types=1);

namespace StrongType\Constraint;

use StrongType\Exception\ConstraintConfigurationException;

#[\Attribute(\Attribute::TARGET_CLASS)]
final readonly class AllOf implements ConstraintInterface
{
    /** @var non-empty-list<ConstraintInterface> */
    private array $children;

    public function __construct(ConstraintInterface ...$children)
    {
        if ($children === []) {
            throw new ConstraintConfigurationException('AllOf requires at least one child constraint');
        }
        $this->children = \array_values($children);
    }

    #[\Override]
    public function validate(mixed $value, string $className): ?string
    {
        foreach ($this->children as $child) {
            $error = $child->validate($value, $className);
            if ($error !== null) {
                return $error;
            }
        }

        return null;
    }

    #[\Override]
    public function priority(): int
    {
        return \min(\array_map(static fn(ConstraintInterface $c): int => $c->priority(), $this->children));
    }

    /**
     * The intersection of the children's sets: a child that cannot engage on the value
     * type contributes nothing to the conjunction, so requiring every child to apply is
     * what keeps `AllOf` honest. An empty intersection means no single type can satisfy
     * the combinator, so attaching it anywhere is a configuration error.
     *
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
