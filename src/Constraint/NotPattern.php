<?php

declare(strict_types=1);

namespace StrongType\Constraint;

#[\Attribute(\Attribute::TARGET_CLASS | \Attribute::IS_REPEATABLE)]
final readonly class NotPattern implements ConstraintInterface
{
    /** @param non-empty-string $pattern */
    public function __construct(private string $pattern)
    {
    }

    #[\Override]
    public function validate(mixed $value, string $className): ?string
    {
        $short = \substr($className, (int) \strrpos($className, '\\') + 1);

        if (\is_string($value) && \preg_match($this->pattern, $value)) {
            return "{$short} type must not match pattern {$this->pattern}, got {$value}";
        }

        return null;
    }

    #[\Override]
    public function priority(): int
    {
        return 100;
    }
}
