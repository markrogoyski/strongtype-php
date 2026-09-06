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
            'ipv4 /1'       => ['128.0.0.0/1'],
            'ipv4 /9'       => ['10.0.0.0/9'],
            'ipv4 /10'      => ['10.0.0.0/10'],
            'ipv6 /32'      => ['2001:db8::/32'],
            'ipv6 /128'     => ['::1/128'],
            'ipv6 /0'       => ['::0/0'],
            'ipv6 /1'       => ['::/1'],
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
            'leading space'      => [' 10.0.0.0/8'],
            'trailing space'     => ['10.0.0.0/8 '],
            'space before prefix' => ['10.0.0.0/ 8'],
            'trailing newline'   => ["10.0.0.0/8\n"],
            'arabic-indic digit prefix' => ['10.0.0.0/٨'],
            'fullwidth digit prefix'    => ['10.0.0.0/８'],
            'extra slash'        => ['10.0.0.0/8/9'],
            'prefix beyond PHP_INT_MAX' => ['10.0.0.0/99999999999999999999'],
            'signed prefix'      => ['10.0.0.0/+8'],
            'decimal prefix'     => ['10.0.0.0/8.0'],
            'empty string'       => [''],
            'slash only'         => ['/'],
            'prefix without ip'  => ['/24'],
        ];
    }

    /**
     * A leading zero is a second spelling of an in-range prefix length, not a distinct
     * one; accepting it would let the same network be written several ways.
     */
    #[Test]
    #[DataProvider('dataProviderForNonCanonicalPrefixLengths')]
    public function testNonCanonicalPrefixLengthIsRejected(string $cidr): void
    {
        // Then
        $this->expectException(StrongTypeException::class);

        // When
        new CidrTestString($cidr);
    }

    public static function dataProviderForNonCanonicalPrefixLengths(): array
    {
        return [
            'ipv4 /00'    => ['0.0.0.0/00'],
            'ipv4 /01'    => ['128.0.0.0/01'],
            'ipv4 /08'    => ['10.0.0.0/08'],
            'ipv4 /024'   => ['192.168.1.0/024'],
            'ipv4 /032'   => ['192.168.1.1/032'],
            'ipv4 /000'   => ['0.0.0.0/000'],
            'ipv6 /00'    => ['::0/00'],
            'ipv6 /0128'  => ['::1/0128'],
            'ipv6 /032'   => ['2001:db8::/032'],
        ];
    }

    /**
     * The canonical spelling of every rejected leading-zero prefix is still accepted,
     * so the new rule narrows spelling only, never the range of prefix lengths.
     */
    #[Test]
    #[DataProvider('dataProviderForCanonicalCounterparts')]
    public function testCanonicalCounterpartOfARejectedPrefixIsAccepted(string $cidr): void
    {
        // When
        $cidrString = new CidrTestString($cidr);

        // Then
        $this->assertSame($cidr, $cidrString->value);
    }

    public static function dataProviderForCanonicalCounterparts(): array
    {
        return [
            'ipv4 /0'    => ['0.0.0.0/0'],
            'ipv4 /1'    => ['128.0.0.0/1'],
            'ipv4 /8'    => ['10.0.0.0/8'],
            'ipv4 /24'   => ['192.168.1.0/24'],
            'ipv4 /32'   => ['192.168.1.1/32'],
            'ipv6 /0'    => ['::0/0'],
            'ipv6 /128'  => ['::1/128'],
            'ipv6 /32'   => ['2001:db8::/32'],
        ];
    }
}
