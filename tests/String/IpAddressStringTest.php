<?php

declare(strict_types=1);

namespace StrongType\Tests\String;

use StrongType\Exception\StrongTypeException;
use StrongType\String\IpAddressString;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;

class IpAddressStringTest extends \PHPUnit\Framework\TestCase
{
    #[Test]
    #[DataProvider('dataProviderForValidValues')]
    public function testValidValue(string $value)
    {
        // When
        $ipAddressString = new IpAddressString($value);

        // Then
        $this->expectNotToPerformAssertions();
    }

    #[Test]
    #[DataProvider('dataProviderForValidValues')]
    public function testGetValue(string $value)
    {
        // Given
        $ipAddressString = new IpAddressString($value);

        // When
        $obtainedValue = $ipAddressString->getValue();

        // Then
        $this->assertSame($value, $obtainedValue);
    }

    #[Test]
    #[DataProvider('dataProviderForValidValues')]
    public function testDebugInfo(string $value)
    {
        // Given
        $ipAddressString = new IpAddressString($value);

        // When
        $debugInfo = $ipAddressString->__debugInfo();

        // Then
        $this->assertSame($value, $debugInfo['value']);
    }

    public static function dataProviderForValidValues(): array
    {
        return [
            ['127.0.0.1'],
            ['192.168.1.1'],
            ['0.0.0.0'],
            ['255.255.255.255'],
            ['::1'],
            ['2001:0db8:85a3:0000:0000:8a2e:0370:7334'],
        ];
    }

    #[Test]
    #[DataProvider('dataProviderForValidValuesStringRepresentation')]
    public function testStringRepresentation(string $value, string $expected)
    {
        // Given
        $ipAddressString = new IpAddressString($value);

        // When
        $stringRepresentation = (string) $ipAddressString;

        // Then
        $this->assertSame($expected, $stringRepresentation);
    }

    public static function dataProviderForValidValuesStringRepresentation(): array
    {
        return [
            ['127.0.0.1', '127.0.0.1'],
            ['192.168.1.1', '192.168.1.1'],
            ['0.0.0.0', '0.0.0.0'],
        ];
    }

    #[Test]
    #[DataProvider('dataProviderForValidValuesJsonStringRepresentation')]
    public function testJsonSerialization(string $value, string $expected)
    {
        // Given
        $ipAddressString = new IpAddressString($value);

        // When
        $jsonSerialization = \json_encode($ipAddressString);

        // Then
        $this->assertSame($expected, $jsonSerialization);
    }

    public static function dataProviderForValidValuesJsonStringRepresentation(): array
    {
        return [
            ['127.0.0.1', '"127.0.0.1"'],
            ['192.168.1.1', '"192.168.1.1"'],
            ['0.0.0.0', '"0.0.0.0"'],
        ];
    }

    #[Test]
    #[DataProvider('dataProviderForInvalidValues')]
    public function testInvalidValue(string $value)
    {
        // Then
        $this->expectException(StrongTypeException::class);

        // When
        $ipAddressString = new IpAddressString($value);
    }

    public static function dataProviderForInvalidValues(): array
    {
        return [
            [''],
            ['not an ip'],
            ['999.999.999.999'],
            ['256.1.1.1'],
            ['1.2.3'],
            ['abc'],
        ];
    }
}
