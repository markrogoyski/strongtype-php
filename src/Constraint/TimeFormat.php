<?php

declare(strict_types=1);

namespace StrongType\Constraint;

use StrongType\Util\ShortClassName;

#[\Attribute(\Attribute::TARGET_CLASS)]
final readonly class TimeFormat implements ConstraintInterface
{
    #[\Override]
    public function validate(mixed $value, string $className): ?string
    {
        $short = ShortClassName::of($className);

        if (\is_string($value) && !\preg_match('/\A([01]\d|2[0-3]):([0-5]\d):([0-5]\d)\z/', $value)) {
            return "{$short} type must be a valid time (HH:MM:SS), got {$value}";
        }

        return null;
    }

    #[\Override]
    public function priority(): int
    {
        return 100;
    }

    /**
     * @return non-empty-list<ValueType>
     */
    #[\Override]
    public function acceptedValueTypes(): array
    {
        return [ValueType::String];
    }
}
