<?php

declare(strict_types=1);

namespace StrongType\Tests\String;

use StrongType\Exception\StrongTypeException;
use StrongType\String\IpAddressString;

class IpAddressStringTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @test         Valid value
     * @dataProvider dataProviderForValidValues
     * @param        string $value
     */
    public function testValidValue(string $value)
    {
        // When
        $ipAddressString = new IpAddressString($value);

        // Then
        $this->expectNotToPerformAssertions();
    }

    /**
     * @test         Get value
     * @dataProvider dataProviderForValidValues
     * @param        string $value
     */
    public function testGetValue(string $value)
    {
        // Given
        $ipAddressString = new IpAddressString($value);

        // When
        $obtainedValue = $ipAddressString->getValue();

        // Then
        $this->assertSame($value, $obtainedValue);
    }

    /**
     * @test         Debug info
     * @dataProvider dataProviderForValidValues
     * @param        string $value
     */
    public function testDebugInfo(string $value)
    {
        // Given
        $ipAddressString = new IpAddressString($value);

        // When
        $debugInfo = $ipAddressString->__debugInfo();

        // Then
        $this->assertSame($value, $debugInfo['value']);
    }

    public function dataProviderForValidValues(): array
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

    /**
     * @test         String representation
     * @dataProvider dataProviderForValidValuesStringRepresentation
     * @param        string $value
     * @param        string $expected
     */
    public function testStringRepresentation(string $value, string $expected)
    {
        // Given
        $ipAddressString = new IpAddressString($value);

        // When
        $stringRepresentation = (string) $ipAddressString;

        // Then
        $this->assertSame($expected, $stringRepresentation);
    }

    public function dataProviderForValidValuesStringRepresentation(): array
    {
        return [
            ['127.0.0.1', '127.0.0.1'],
            ['192.168.1.1', '192.168.1.1'],
            ['0.0.0.0', '0.0.0.0'],
        ];
    }

    /**
     * @test         JSON serialization
     * @dataProvider dataProviderForValidValuesJsonStringRepresentation
     * @param        string $value
     * @param        string $expected
     */
    public function testJsonSerialization(string $value, string $expected)
    {
        // Given
        $ipAddressString = new IpAddressString($value);

        // When
        $jsonSerialization = json_encode($ipAddressString);

        // Then
        $this->assertSame($expected, $jsonSerialization);
    }

    public function dataProviderForValidValuesJsonStringRepresentation(): array
    {
        return [
            ['127.0.0.1', '"127.0.0.1"'],
            ['192.168.1.1', '"192.168.1.1"'],
            ['0.0.0.0', '"0.0.0.0"'],
        ];
    }

    /**
     * @test         Invalid value
     * @dataProvider dataProviderForInvalidValues
     * @param        string $value
     */
    public function testInvalidValue(string $value)
    {
        // Then
        $this->expectException(StrongTypeException::class);

        // When
        $ipAddressString = new IpAddressString($value);
    }

    public function dataProviderForInvalidValues(): array
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
