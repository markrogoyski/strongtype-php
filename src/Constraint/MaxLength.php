<?php

declare(strict_types=1);

namespace StrongType\Constraint;

use StrongType\Util\ShortClassName;

#[\Attribute(\Attribute::TARGET_CLASS)]
final readonly class MaxLength implements ConstraintInterface
{
    public function __construct(private int $maxLength)
    {
        if ($maxLength < 0) {
            throw new \LogicException("MaxLength: length must be >= 0, got {$maxLength}");
        }
    }

    #[\Override]
    public function validate(mixed $value, string $className): ?string
    {
        $short = ShortClassName::of($className);

        if (\is_string($value) && \strlen($value) > $this->maxLength) {
            return "{$short} type must have length <= {$this->maxLength}, got {$value}";
        }

        return null;
    }

    #[\Override]
    public function priority(): int
    {
        return 50;
    }
}
