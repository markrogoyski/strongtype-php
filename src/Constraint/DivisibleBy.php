<?php

declare(strict_types=1);

namespace StrongType\Constraint;

use StrongType\Exception\ConstraintConfigurationException;
use StrongType\Util\ShortClassName;

#[\Attribute(\Attribute::TARGET_CLASS)]
final readonly class DivisibleBy implements ConstraintInterface
{
    public function __construct(private int $divisor)
    {
        if ($divisor === 0) {
            throw new ConstraintConfigurationException('DivisibleBy: divisor must not be zero');
        }
    }

    #[\Override]
    public function validate(mixed $value, string $className): ?string
    {
        $short = ShortClassName::of($className);

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

    /**
     * @return non-empty-list<ValueType>
     */
    #[\Override]
    public function acceptedValueTypes(): array
    {
        // The modulo predicate is integer-only: it is not meaningful for float values.
        return [ValueType::Int];
    }
}
