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

    /**
     * The child's set. A child that cannot engage always "passes", which inverts to a
     * `Not` that rejects every value -- so the child's domain is exactly the domain on
     * which the inversion is meaningful.
     *
     * @return list<ValueType>
     */
    #[\Override]
    public function acceptedValueTypes(): array
    {
        return $this->child->acceptedValueTypes();
    }
}
