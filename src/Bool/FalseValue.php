<?php

declare(strict_types=1);

namespace StrongType\Bool;

readonly class FalseValue extends BoolType
{
    public function __construct(false $value)
    {
        parent::__construct($value);
    }

    // Validation-only override: stores $value verbatim via parent::__construct() (the
    // narrowed `false` parameter type only rejects `true` earlier, via TypeError), so
    // inherited __serialize()/__unserialize() stays safe to reuse.
    #[\Override]
    protected static function constructorPreservesSerializedStateVerbatim(): bool
    {
        return true;
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
