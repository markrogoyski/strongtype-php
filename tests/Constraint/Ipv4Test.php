<?php

declare(strict_types=1);

namespace StrongType\Tests\Constraint;

use StrongType\Constraint\Ipv4;
use StrongType\Exception\StrongTypeException;
use StrongType\String\StringType;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;

#[Ipv4]
readonly class Ipv4TestString extends StringType
{
}

class Ipv4Test extends \PHPUnit\Framework\TestCase
{
    #[Test]
    #[DataProvider('validIpv4Addresses')]
    public function testValidIpv4Addresses(string $ip): void
    {
        $this->assertSame($ip, (new Ipv4TestString($ip))->value);
    }

    public static function validIpv4Addresses(): array
    {
        return [
            'loopback'    => ['127.0.0.1'],
            'private'     => ['192.168.1.1'],
            'all zeros'   => ['0.0.0.0'],
            'broadcast'   => ['255.255.255.255'],
            'class A'     => ['10.0.0.1'],
        ];
    }

    #[Test]
    #[DataProvider('invalidIpv4Addresses')]
    public function testInvalidIpv4Addresses(string $ip): void
    {
        $this->expectException(StrongTypeException::class);
        new Ipv4TestString($ip);
    }

    public static function invalidIpv4Addresses(): array
    {
        return [
            'out of range'   => ['999.999.999.999'],
            'ipv6 loopback'  => ['::1'],
            'text'           => ['not-an-ip'],
            'partial'        => ['192.168.1'],
            'empty string'   => [''],
        ];
    }
}
