<?php

declare(strict_types=1);

namespace StrongType\Constraint;

#[\Attribute(\Attribute::TARGET_CLASS)]
final readonly class MaxCount implements ConstraintInterface
{
    public function __construct(private int $maxCount)
    {
    }

    #[\Override]
    public function validate(mixed $value, string $className): ?string
    {
        $short = \substr($className, (int) \strrpos($className, '\\') + 1);

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
