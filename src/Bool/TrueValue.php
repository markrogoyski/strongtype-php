<?php

declare(strict_types=1);

namespace StrongType\Bool;

readonly class TrueValue extends BoolType
{
    public function __construct(true $value)
    {
        parent::__construct($value);
    }

    /**
     * @psalm-suppress MoreSpecificReturnType, LessSpecificReturnStatement
     */
    #[\Override]
    public static function tryFrom(mixed $value): ?static
    {
        if ($value !== true) {
            return null;
        }
        return parent::tryFrom($value);
    }
}
