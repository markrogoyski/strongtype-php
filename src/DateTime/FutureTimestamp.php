<?php

declare(strict_types=1);

namespace StrongType\DateTime;

use StrongType\Exception\StrongTypeException;
use StrongType\Int\Integer;

readonly class FutureTimestamp extends Integer
{
    public function __construct(int $value)
    {
        parent::__construct($value);

        if ($this->value <= \time()) {
            throw new StrongTypeException("FutureTimestamp type must be in the future, got {$this->value}");
        }
    }

    // Validation-only override: stores $value verbatim via parent::__construct(), never
    // transforms it, so inherited __serialize()/__unserialize() stays safe to reuse. The
    // time()-dependent check can legitimately fail on a *later* reconstruction of the same
    // value (e.g. a timestamp that was in the future a moment ago no longer is) -- that is
    // a separate, expected concern from constructor fidelity, which this only certifies.
    #[\Override]
    protected static function constructorPreservesSerializedStateVerbatim(): bool
    {
        return true;
    }
}
