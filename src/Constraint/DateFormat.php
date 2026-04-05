<?php

declare(strict_types=1);

namespace StrongType\Constraint;

#[\Attribute(\Attribute::TARGET_CLASS)]
final readonly class DateFormat implements ConstraintInterface
{
    public function __construct(private string $format)
    {
    }

    public function validate(mixed $value, string $className): ?string
    {
        $short = \substr($className, \strrpos($className, '\\') + 1);

        if (\is_string($value)) {
            $parsed = \DateTimeImmutable::createFromFormat($this->format, $value);
            if ($parsed === false || $parsed->format($this->format) !== $value) {
                return "{$short} type must match date format {$this->format}, got {$value}";
            }
        }

        return null;
    }

    public function priority(): int
    {
        return 100;
    }
}
