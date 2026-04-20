<?php

declare(strict_types=1);

namespace StrongType;

/**
 * Implemented by every StrongType base class so that wrappers (notably {@see Nullable})
 * can compare two strong-type instances generically without knowing their concrete type.
 *
 * Implementations must:
 *  - Return false when {@see static::class} differs from `$other::class` (no cross-type equality).
 *  - Compare the underlying value(s) by strict equality.
 */
interface HasEquals
{
    public function equals(self $other): bool;
}
