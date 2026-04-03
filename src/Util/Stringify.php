<?php

declare(strict_types=1);

namespace StrongType\Util;

class Stringify
{
    public static function value(mixed $value): string
    {
        if (\is_float($value) && \is_nan($value)) {
            return 'NAN';
        }

        return \print_r($value, true);
    }
}
