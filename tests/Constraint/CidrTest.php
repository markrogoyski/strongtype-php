<?php

declare(strict_types=1);

namespace StrongType\Tests\Constraint;

use StrongType\Constraint\Cidr;
use StrongType\Exception\StrongTypeException;
use StrongType\String\StringType;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;

#[Cidr]
readonly class CidrTestString extends StringType
{
}

class CidrTest extends \PHPUnit\Framework\TestCase
{
    #[Test]
    #[DataProvider('validCidrNotations')]
    public function testValidCidrNotations(string $cidr): void
    {
        $this->assertSame($cidr, (new CidrTestString($cidr))->value);
    }

    public static function validCidrNotations(): array
    {
        return [
            'ipv4 /24'      => ['192.168.1.0/24'],
            'ipv4 /8'       => ['10.0.0.0/8'],
            'ipv4 /0'       => ['0.0.0.0/0'],
            'ipv4 /32'      => ['192.168.1.1/32'],
            'ipv6 /32'      => ['2001:db8::/32'],
            'ipv6 /128'     => ['::1/128'],
            'ipv6 /0'       => ['::0/0'],
        ];
    }

    #[Test]
    #[DataProvider('invalidCidrNotations')]
    public function testInvalidCidrNotations(string $cidr): void
    {
        $this->expectException(StrongTypeException::class);
        new CidrTestString($cidr);
    }

    public static function invalidCidrNotations(): array
    {
        return [
            'no slash'           => ['192.168.1.0'],
            'invalid ip'         => ['999.999.999.999/24'],
            'ipv4 prefix too high' => ['192.168.1.0/33'],
            'ipv6 prefix too high' => ['2001:db8::/129'],
            'text'               => ['not-cidr'],
            'empty prefix'       => ['192.168.1.0/'],
            'negative prefix'    => ['192.168.1.0/-1'],
            'non-numeric prefix' => ['192.168.1.0/abc'],
        ];
    }
}
