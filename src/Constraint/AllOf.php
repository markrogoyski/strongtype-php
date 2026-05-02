<?php

declare(strict_types=1);

namespace StrongType\Constraint;

#[\Attribute(\Attribute::TARGET_CLASS)]
final readonly class AllOf implements ConstraintInterface
{
    /** @var non-empty-list<ConstraintInterface> */
    private array $children;

    public function __construct(ConstraintInterface ...$children)
    {
        if ($children === []) {
            throw new \LogicException('AllOf requires at least one child constraint');
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
}
