<?php

declare(strict_types=1);

namespace StrongType\Tests\String;

use StrongType\Exception\StrongTypeException;
use StrongType\String\Ipv4AddressString;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;

class Ipv4AddressStringTest extends \PHPUnit\Framework\TestCase
{
    #[Test]
    #[DataProvider('dataProviderForValidValues')]
    public function testValidValue(string $value)
    {
        // When
        $ipv4AddressString = new Ipv4AddressString($value);

        // Then
        $this->expectNotToPerformAssertions();
    }

    #[Test]
    #[DataProvider('dataProviderForValidValues')]
    public function testGetValue(string $value)
    {
        // Given
        $ipv4AddressString = new Ipv4AddressString($value);

        // When
        $obtainedValue = $ipv4AddressString->getValue();

        // Then
        $this->assertSame($value, $obtainedValue);
    }

    #[Test]
    #[DataProvider('dataProviderForValidValues')]
    public function testDebugInfo(string $value)
    {
        // Given
        $ipv4AddressString = new Ipv4AddressString($value);

        // When
        $debugInfo = $ipv4AddressString->__debugInfo();

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
        ];
    }

    #[Test]
    #[DataProvider('dataProviderForValidValuesStringRepresentation')]
    public function testStringRepresentation(string $value, string $expected)
    {
        // Given
        $ipv4AddressString = new Ipv4AddressString($value);

        // When
        $stringRepresentation = (string) $ipv4AddressString;

        // Then
        $this->assertSame($expected, $stringRepresentation);
    }

    public static function dataProviderForValidValuesStringRepresentation(): array
    {
        return [
            ['127.0.0.1', '127.0.0.1'],
            ['192.168.1.1', '192.168.1.1'],
            ['0.0.0.0', '0.0.0.0'],
            ['255.255.255.255', '255.255.255.255'],
        ];
    }

    #[Test]
    #[DataProvider('dataProviderForValidValuesJsonStringRepresentation')]
    public function testJsonSerialization(string $value, string $expected)
    {
        // Given
        $ipv4AddressString = new Ipv4AddressString($value);

        // When
        $jsonSerialization = \json_encode($ipv4AddressString);

        // Then
        $this->assertSame($expected, $jsonSerialization);
    }

    public static function dataProviderForValidValuesJsonStringRepresentation(): array
    {
        return [
            ['127.0.0.1', '"127.0.0.1"'],
            ['192.168.1.1', '"192.168.1.1"'],
            ['0.0.0.0', '"0.0.0.0"'],
            ['255.255.255.255', '"255.255.255.255"'],
        ];
    }

    #[Test]
    #[DataProvider('dataProviderForInvalidValues')]
    public function testInvalidValue(string $value)
    {
        // Then
        $this->expectException(StrongTypeException::class);

        // When
        $ipv4AddressString = new Ipv4AddressString($value);
    }

    public static function dataProviderForInvalidValues(): array
    {
        return [
            [''],
            ['not-an-ip'],
            ['999.999.999.999'],
            ['::1'],
        ];
    }
}
