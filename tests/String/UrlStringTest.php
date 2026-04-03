<?php

declare(strict_types=1);

namespace StrongType\Tests\String;

use StrongType\Exception\StrongTypeException;
use StrongType\String\UrlString;

class UrlStringTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @test         Valid value
     * @dataProvider dataProviderForValidValues
     * @param        string $value
     */
    public function testValidValue(string $value)
    {
        // When
        $urlString = new UrlString($value);

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
        $urlString = new UrlString($value);

        // When
        $obtainedValue = $urlString->getValue();

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
        $urlString = new UrlString($value);

        // When
        $debugInfo = $urlString->__debugInfo();

        // Then
        $this->assertSame($value, $debugInfo['value']);
    }

    public static function dataProviderForValidValues(): array
    {
        return [
            ['http://example.com'],
            ['https://example.com'],
            ['https://example.com/path'],
            ['ftp://files.example.com'],
            ['https://example.com/path?q=1&r=2'],
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
        $urlString = new UrlString($value);

        // When
        $stringRepresentation = (string) $urlString;

        // Then
        $this->assertSame($expected, $stringRepresentation);
    }

    public static function dataProviderForValidValuesStringRepresentation(): array
    {
        return [
            ['http://example.com', 'http://example.com'],
            ['https://example.com', 'https://example.com'],
            ['https://example.com/path', 'https://example.com/path'],
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
        $urlString = new UrlString($value);

        // When
        $jsonSerialization = json_encode($urlString);

        // Then
        $this->assertSame($expected, $jsonSerialization);
    }

    public static function dataProviderForValidValuesJsonStringRepresentation(): array
    {
        return [
            ['http://example.com', '"http:\/\/example.com"'],
            ['https://example.com', '"https:\/\/example.com"'],
            ['https://example.com/path', '"https:\/\/example.com\/path"'],
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
        $urlString = new UrlString($value);
    }

    public static function dataProviderForInvalidValues(): array
    {
        return [
            [''],
            ['not a url'],
            ['example.com'],
            ['://missing'],
            ['http://'],
            ['just-text'],
        ];
    }
}
