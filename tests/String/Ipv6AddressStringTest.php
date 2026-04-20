<?php

declare(strict_types=1);

namespace StrongType\Tests\String;

use StrongType\Exception\StrongTypeException;
use StrongType\String\Ipv6AddressString;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;

class Ipv6AddressStringTest extends \PHPUnit\Framework\TestCase
{
    #[Test]
    #[DataProvider('dataProviderForValidValues')]
    public function testValidValue(string $value)
    {
        // When
        $ipv6AddressString = new Ipv6AddressString($value);

        // Then
        $this->expectNotToPerformAssertions();
    }

    #[Test]
    #[DataProvider('dataProviderForValidValues')]
    public function testGetValue(string $value)
    {
        // Given
        $ipv6AddressString = new Ipv6AddressString($value);

        // When
        $obtainedValue = $ipv6AddressString->getValue();

        // Then
        $this->assertSame($value, $obtainedValue);
    }

    #[Test]
    #[DataProvider('dataProviderForValidValues')]
    public function testDebugInfo(string $value)
    {
        // Given
        $ipv6AddressString = new Ipv6AddressString($value);

        // When
        $debugInfo = $ipv6AddressString->__debugInfo();

        // Then
        $this->assertSame($value, $debugInfo['value']);
    }

    public static function dataProviderForValidValues(): array
    {
        return [
            ['::1'],
            ['2001:0db8:85a3:0000:0000:8a2e:0370:7334'],
            ['fe80::1'],
        ];
    }

    #[Test]
    #[DataProvider('dataProviderForValidValuesStringRepresentation')]
    public function testStringRepresentation(string $value, string $expected)
    {
        // Given
        $ipv6AddressString = new Ipv6AddressString($value);

        // When
        $stringRepresentation = (string) $ipv6AddressString;

        // Then
        $this->assertSame($expected, $stringRepresentation);
    }

    public static function dataProviderForValidValuesStringRepresentation(): array
    {
        return [
            ['::1', '::1'],
            ['2001:0db8:85a3:0000:0000:8a2e:0370:7334', '2001:0db8:85a3:0000:0000:8a2e:0370:7334'],
            ['fe80::1', 'fe80::1'],
        ];
    }

    #[Test]
    #[DataProvider('dataProviderForValidValuesJsonStringRepresentation')]
    public function testJsonSerialization(string $value, string $expected)
    {
        // Given
        $ipv6AddressString = new Ipv6AddressString($value);

        // When
        $jsonSerialization = \json_encode($ipv6AddressString);

        // Then
        $this->assertSame($expected, $jsonSerialization);
    }

    public static function dataProviderForValidValuesJsonStringRepresentation(): array
    {
        return [
            ['::1', '"::1"'],
            ['2001:0db8:85a3:0000:0000:8a2e:0370:7334', '"2001:0db8:85a3:0000:0000:8a2e:0370:7334"'],
            ['fe80::1', '"fe80::1"'],
        ];
    }

    #[Test]
    #[DataProvider('dataProviderForInvalidValues')]
    public function testInvalidValue(string $value)
    {
        // Then
        $this->expectException(StrongTypeException::class);

        // When
        $ipv6AddressString = new Ipv6AddressString($value);
    }

    public static function dataProviderForInvalidValues(): array
    {
        return [
            [''],
            ['127.0.0.1'],
            ['not-an-ip'],
        ];
    }
}
