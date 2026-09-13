<?php

declare(strict_types=1);

namespace StrongType\Constraint;

use StrongType\Exception\ConstraintConfigurationException;
use StrongType\Exception\ConstraintEvaluationException;

#[\Attribute(\Attribute::TARGET_CLASS)]
final readonly class AllOf implements ConstraintInterface
{
    use IntersectsChildValueTypes;

    /** @var non-empty-list<ConstraintInterface> */
    private array $children;

    public function __construct(ConstraintInterface ...$children)
    {
        if ($children === []) {
            throw new ConstraintConfigurationException('AllOf requires at least one child constraint');
        }
        $this->children = \array_values($children);
    }

    /**
     * A child that throws ConstraintEvaluationException (couldn't be evaluated at all,
     * e.g. a regex engine error) does not automatically fail the AND: a *later* child might
     * still return a definite violation, which is a real, known reason to reject and should
     * be preferred over an indeterminate result -- and reporting it, rather than the
     * exception, keeps AllOf's message deterministic regardless of which child happened to
     * be unevaluable. The exception is retained and re-thrown only if every other child
     * passes, meaning the indeterminate child's result is the only thing standing between
     * this value and a confirmed pass.
     */
    #[\Override]
    public function validate(mixed $value, string $className): ?string
    {
        $pendingException = null;
        foreach ($this->children as $child) {
            try {
                $error = $child->validate($value, $className);
            } catch (ConstraintEvaluationException $e) {
                $pendingException ??= $e;
                continue;
            }
            if ($error !== null) {
                return $error;
            }
        }

        if ($pendingException !== null) {
            throw $pendingException;
        }

        return null;
    }

    #[\Override]
    public function priority(): int
    {
        return \min(\array_map(static fn(ConstraintInterface $c): int => $c->priority(), $this->children));
    }

    // acceptedValueTypes(): the intersection of the children's sets -- a child that cannot
    // engage on the value type contributes nothing to the conjunction, so requiring every
    // child to apply is what keeps `AllOf` honest. An empty intersection means no single
    // type can satisfy the combinator, so attaching it anywhere is a configuration error.
    // Shared with AnyOf via IntersectsChildValueTypes.
}
