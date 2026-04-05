<?php

declare(strict_types=1);

namespace StrongType\Constraint;

interface ConstraintInterface
{
    /**
     * Return null on success, error message string on failure.
     */
    public function validate(mixed $value, string $className): ?string;

    /**
     * Lower runs first. Range=50, format=100, semantic=150.
     */
    public function priority(): int;
}
