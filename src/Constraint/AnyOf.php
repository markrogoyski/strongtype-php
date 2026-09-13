<?php

declare(strict_types=1);

namespace StrongType\Constraint;

use StrongType\Exception\ConstraintConfigurationException;
use StrongType\Exception\ConstraintEvaluationException;
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

    /**
     * A child that throws ConstraintEvaluationException (couldn't be evaluated at all,
     * e.g. a regex engine error) is neither a pass nor a confirmed failure -- it must not
     * abort the OR outright, since a *later* child might still pass and correctly satisfy
     * it, and it must not be silently treated as "failed" either, since that would let an
     * unlucky child ordering flip the overall result (the same value could accept or reject
     * depending on which child happens to run first). So every child still gets a chance to
     * pass; the exception is retained and re-thrown only once the loop finishes without any
     * child passing, meaning the indeterminate child's result is the only thing that could
     * have changed the outcome.
     */
    #[\Override]
    public function validate(mixed $value, string $className): ?string
    {
        $errors = [];
        $pendingException = null;
        foreach ($this->children as $child) {
            try {
                $error = $child->validate($value, $className);
            } catch (ConstraintEvaluationException $e) {
                $pendingException ??= $e;
                continue;
            }
            if ($error === null) {
                return null;
            }
            $errors[] = $error;
        }

        if ($pendingException !== null) {
            throw $pendingException;
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
