<?php

declare(strict_types=1);

namespace StrongType\Constraint;

#[\Attribute(\Attribute::TARGET_CLASS)]
final readonly class MinCount implements ConstraintInterface
{
    public function __construct(private int $minCount)
    {
        if ($minCount < 0) {
            throw new \LogicException("MinCount: count must be >= 0, got {$minCount}");
        }
    }

    #[\Override]
    public function validate(mixed $value, string $className): ?string
    {
        $short = \substr($className, (int) \strrpos($className, '\\') + 1);

        if (\is_array($value) && \count($value) < $this->minCount) {
            return "{$short} type must have count >= {$this->minCount}, got " . \count($value);
        }

        return null;
    }

    #[\Override]
    public function priority(): int
    {
        return 50;
    }
}
