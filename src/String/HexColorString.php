<?php

declare(strict_types=1);

namespace StrongType\String;

use StrongType\Constraint\Pattern;

#[Pattern('/\A#([0-9a-fA-F]{3}|[0-9a-fA-F]{6})\z/')]
readonly class HexColorString extends NonemptyString
{
}
