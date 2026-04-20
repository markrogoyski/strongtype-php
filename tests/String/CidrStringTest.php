<?php

declare(strict_types=1);

namespace StrongType\Tests\String;

use StrongType\Exception\StrongTypeException;
use StrongType\String\CidrString;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;

class CidrStringTest extends \PHPUnit\Framework\TestCase
{
    #[Test]
    #[DataProvider('dataProviderForValidValues')]
    public function testValidValue(string $value)
    {
        // When
        $cidrString = new CidrString($value);

        // Then
        $this->expectNotToPerformAssertions();
    }

    #[Test]
    #[DataProvider('dataProviderForValidValues')]
    public function testGetValue(string $value)
    {
        // Given
        $cidrString = new CidrString($value);

        // When
        $obtainedValue = $cidrString->getValue();

        // Then
        $this->assertSame($value, $obtainedValue);
    }

    #[Test]
    #[DataProvider('dataProviderForValidValues')]
    public function testDebugInfo(string $value)
    {
        // Given
        $cidrString = new CidrString($value);

        // When
        $debugInfo = $cidrString->__debugInfo();

        // Then
        $this->assertSame($value, $debugInfo['value']);
    }

    public static function dataProviderForValidValues(): array
    {
        return [
            ['192.168.1.0/24'],
            ['10.0.0.0/8'],
            ['0.0.0.0/0'],
            ['2001:db8::/32'],
            ['::1/128'],
        ];
    }

    #[Test]
    #[DataProvider('dataProviderForValidValuesStringRepresentation')]
    public function testStringRepresentation(string $value, string $expected)
    {
        // Given
        $cidrString = new CidrString($value);

        // When
        $stringRepresentation = (string) $cidrString;

        // Then
        $this->assertSame($expected, $stringRepresentation);
    }

    public static function dataProviderForValidValuesStringRepresentation(): array
    {
        return [
            ['192.168.1.0/24', '192.168.1.0/24'],
            ['10.0.0.0/8', '10.0.0.0/8'],
            ['0.0.0.0/0', '0.0.0.0/0'],
            ['2001:db8::/32', '2001:db8::/32'],
            ['::1/128', '::1/128'],
        ];
    }

    #[Test]
    #[DataProvider('dataProviderForValidValuesJsonStringRepresentation')]
    public function testJsonSerialization(string $value, string $expected)
    {
        // Given
        $cidrString = new CidrString($value);

        // When
        $jsonSerialization = \json_encode($cidrString);

        // Then
        $this->assertSame($expected, $jsonSerialization);
    }

    public static function dataProviderForValidValuesJsonStringRepresentation(): array
    {
        return [
            ['192.168.1.0/24', '"192.168.1.0\/24"'],
            ['10.0.0.0/8', '"10.0.0.0\/8"'],
            ['0.0.0.0/0', '"0.0.0.0\/0"'],
            ['2001:db8::/32', '"2001:db8::\/32"'],
            ['::1/128', '"::1\/128"'],
        ];
    }

    #[Test]
    #[DataProvider('dataProviderForInvalidValues')]
    public function testInvalidValue(string $value)
    {
        // Then
        $this->expectException(StrongTypeException::class);

        // When
        $cidrString = new CidrString($value);
    }

    public static function dataProviderForInvalidValues(): array
    {
        return [
            [''],
            ['192.168.1.0'],
            ['192.168.1.0/33'],
            ['not-cidr'],
            ['999.999.999.999/24'],
        ];
    }
}
