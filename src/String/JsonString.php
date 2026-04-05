<?php

declare(strict_types=1);

namespace StrongType\String;

use StrongType\Constraint\Json;

#[Json]
readonly class JsonString extends NonemptyString
{
}
