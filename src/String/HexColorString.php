<?php

declare(strict_types=1);

namespace StrongType\String;

use StrongType\Constraint\Pattern;

#[Pattern('/^#([0-9a-fA-F]{3}|[0-9a-fA-F]{6})$/')]
readonly class HexColorString extends NonemptyString
{
}
