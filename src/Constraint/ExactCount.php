<?php

declare(strict_types=1);

namespace StrongType\Constraint;

use StrongType\Exception\ConstraintConfigurationException;
use StrongType\Util\ShortClassName;

#[\Attribute(\Attribute::TARGET_CLASS)]
final readonly class ExactCount implements ConstraintInterface
{
    public function __construct(private int $count)
    {
        if ($count < 0) {
            throw new ConstraintConfigurationException("ExactCount: count must be >= 0, got {$count}");
        }
    }

    #[\Override]
    public function validate(mixed $value, string $className): ?string
    {
        $short = ShortClassName::of($className);

        if (\is_array($value) && \count($value) !== $this->count) {
            return "{$short} type must have exactly {$this->count} elements, got " . \count($value);
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
        return [ValueType::Array];
    }
}
