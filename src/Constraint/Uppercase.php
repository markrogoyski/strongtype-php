<?php

declare(strict_types=1);

namespace StrongType\Constraint;

use StrongType\Util\ShortClassName;

#[\Attribute(\Attribute::TARGET_CLASS)]
final readonly class Uppercase implements ConstraintInterface
{
    #[\Override]
    public function validate(mixed $value, string $className): ?string
    {
        $short = ShortClassName::of($className);

        if (\is_string($value) && !\ctype_upper($value)) {
            return "{$short} type must only contain uppercase characters, got {$value}";
        }

        return null;
    }

    #[\Override]
    public function priority(): int
    {
        return 100;
    }
}
