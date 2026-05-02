<?php

declare(strict_types=1);

namespace StrongType\Util;

class ShortClassName
{
    public static function of(string $fqcn): string
    {
        $pos = \strrpos($fqcn, '\\');

        return $pos === false ? $fqcn : \substr($fqcn, $pos + 1);
    }
}
