<?php

declare(strict_types=1);

namespace StrongType\Bool;

readonly class FalseValue extends BoolType
{
    public function __construct(false $value)
    {
        parent::__construct($value);
    }

    /**
     * @psalm-suppress MoreSpecificReturnType, LessSpecificReturnStatement
     */
    #[\Override]
    public static function tryFrom(mixed $value): ?static
    {
        if ($value !== false) {
            return null;
        }
        return parent::tryFrom($value);
    }
}
