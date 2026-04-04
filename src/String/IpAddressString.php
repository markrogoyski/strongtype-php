<?php

declare(strict_types=1);

namespace StrongType\String;

use StrongType\Exception\StrongTypeException;

readonly class IpAddressString extends NonemptyString
{
    public function __construct(string $value)
    {
        parent::__construct($value);

        if (\filter_var($this->value, \FILTER_VALIDATE_IP) === false) {
            throw new StrongTypeException("IpAddressString type must be a valid IP address, got {$this->value}");
        }
    }
}
