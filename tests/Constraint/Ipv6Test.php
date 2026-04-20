<?php

declare(strict_types=1);

namespace StrongType\Tests\Constraint;

use StrongType\Constraint\Ipv6;
use StrongType\Exception\StrongTypeException;
use StrongType\String\StringType;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;

#[Ipv6]
readonly class Ipv6TestString extends StringType
{
}

class Ipv6Test extends \PHPUnit\Framework\TestCase
{
    #[Test]
    #[DataProvider('validIpv6Addresses')]
    public function testValidIpv6Addresses(string $ip): void
    {
        $this->assertSame($ip, (new Ipv6TestString($ip))->value);
    }

    public static function validIpv6Addresses(): array
    {
        return [
            'loopback'     => ['::1'],
            'documentation' => ['2001:db8::1'],
            'link local'   => ['fe80::1'],
            'full form'    => ['2001:0db8:85a3:0000:0000:8a2e:0370:7334'],
        ];
    }

    #[Test]
    #[DataProvider('invalidIpv6Addresses')]
    public function testInvalidIpv6Addresses(string $ip): void
    {
        $this->expectException(StrongTypeException::class);
        new Ipv6TestString($ip);
    }

    public static function invalidIpv6Addresses(): array
    {
        return [
            'ipv4 address'  => ['127.0.0.1'],
            'text'          => ['not-an-ip'],
            'empty string'  => [''],
        ];
    }
}
