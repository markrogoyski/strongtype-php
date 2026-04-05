<?php

declare(strict_types=1);

namespace StrongType\Constraint;

#[\Attribute(\Attribute::TARGET_CLASS | \Attribute::IS_REPEATABLE)]
final readonly class Pattern implements ConstraintInterface
{
    public function __construct(private string $pattern)
    {
    }

    public function validate(mixed $value, string $className): ?string
    {
        $short = \substr($className, \strrpos($className, '\\') + 1);

        if (\is_string($value) && !\preg_match($this->pattern, $value)) {
            return "{$short} type must match pattern {$this->pattern}, got {$value}";
        }

        return null;
    }

    public function priority(): int
    {
        return 100;
    }
}
