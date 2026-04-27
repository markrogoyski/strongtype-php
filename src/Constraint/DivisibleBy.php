<?php

declare(strict_types=1);

namespace StrongType\Constraint;

#[\Attribute(\Attribute::TARGET_CLASS)]
final readonly class DivisibleBy implements ConstraintInterface
{
    public function __construct(private int $divisor)
    {
        if ($divisor === 0) {
            throw new \LogicException('DivisibleBy: divisor must not be zero');
        }
    }

    #[\Override]
    public function validate(mixed $value, string $className): ?string
    {
        $short = \substr($className, (int) \strrpos($className, '\\') + 1);

        if (\is_int($value) && $value % $this->divisor !== 0) {
            return "{$short} type must be divisible by {$this->divisor}, got {$value}";
        }

        return null;
    }

    #[\Override]
    public function priority(): int
    {
        return 50;
    }
}
