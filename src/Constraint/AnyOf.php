<?php

declare(strict_types=1);

namespace StrongType\Constraint;

use StrongType\Exception\ConstraintConfigurationException;
use StrongType\Util\ShortClassName;

#[\Attribute(\Attribute::TARGET_CLASS)]
final readonly class AnyOf implements ConstraintInterface
{
    use IntersectsChildValueTypes;

    /** @var non-empty-list<ConstraintInterface> */
    private array $children;

    public function __construct(ConstraintInterface ...$children)
    {
        if ($children === []) {
            throw new ConstraintConfigurationException('AnyOf requires at least one child constraint');
        }
        $this->children = \array_values($children);
    }

    #[\Override]
    public function validate(mixed $value, string $className): ?string
    {
        $errors = [];
        foreach ($this->children as $child) {
            $error = $child->validate($value, $className);
            if ($error === null) {
                return null;
            }
            $errors[] = $error;
        }

        $short = ShortClassName::of($className);

        return "{$short} type must satisfy one of: [" . \implode(' | ', $errors) . ']';
    }

    #[\Override]
    public function priority(): int
    {
        return \min(\array_map(static fn(ConstraintInterface $c): int => $c->priority(), $this->children));
    }

    // acceptedValueTypes(): the intersection of the children's sets -- a value type only
    // one child engages on would let the others pass vacuously, which is the fail-open
    // hole the applicability check exists to close. An empty intersection means no single
    // type can satisfy the combinator, so attaching it anywhere is a configuration error.
    // Shared with AllOf via IntersectsChildValueTypes.
}
