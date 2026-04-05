<?php

declare(strict_types=1);

namespace StrongType\String;

use StrongType\Constraint\Url;

#[Url]
readonly class UrlString extends NonemptyString
{
}
