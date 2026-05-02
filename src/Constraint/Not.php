<?php

declare(strict_types=1);

namespace StrongType\Constraint;

use StrongType\Util\ShortClassName;

#[\Attribute(\Attribute::TARGET_CLASS)]
final readonly class Not implements ConstraintInterface
{
    public function __construct(private ConstraintInterface $child)
    {
    }

    #[\Override]
    public function validate(mixed $value, string $className): ?string
    {
        $error = $this->child->validate($value, $className);
        if ($error === null) {
            $short      = ShortClassName::of($className);
            $childShort = ShortClassName::of($this->child::class);

            return "{$short} type must NOT satisfy {$childShort}";
        }

        return null;
    }

    #[\Override]
    public function priority(): int
    {
        return $this->child->priority();
    }
}
