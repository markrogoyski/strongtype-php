<?php

declare(strict_types=1);

namespace StrongType\Constraint;

use StrongType\Util\ShortClassName;

#[\Attribute(\Attribute::TARGET_CLASS)]
final readonly class MaxCount implements ConstraintInterface
{
    public function __construct(private int $maxCount)
    {
        if ($maxCount < 0) {
            throw new \LogicException("MaxCount: count must be >= 0, got {$maxCount}");
        }
    }

    #[\Override]
    public function validate(mixed $value, string $className): ?string
    {
        $short = ShortClassName::of($className);

        if (\is_array($value) && \count($value) > $this->maxCount) {
            return "{$short} type must have count <= {$this->maxCount}, got " . \count($value);
        }

        return null;
    }

    #[\Override]
    public function priority(): int
    {
        return 50;
    }
}
