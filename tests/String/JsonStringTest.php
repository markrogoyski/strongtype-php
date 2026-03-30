<?php

declare(strict_types=1);

namespace StrongType\Tests\String;

use StrongType\Exception\StrongTypeException;
use StrongType\String\JsonString;

class JsonStringTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @test         Valid value
     * @dataProvider dataProviderForValidValues
     * @param        string $value
     */
    public function testValidValue(string $value)
    {
        // When
        $jsonString = new JsonString($value);

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
        $jsonString = new JsonString($value);

        // When
        $obtainedValue = $jsonString->getValue();

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
        $jsonString = new JsonString($value);

        // When
        $debugInfo = $jsonString->__debugInfo();

        // Then
        $this->assertSame($value, $debugInfo['value']);
    }

    public function dataProviderForValidValues(): array
    {
        return [
            ['{}'],
            ['[]'],
            ['{"key":"value"}'],
            ['[1,2,3]'],
            ['"hello"'],
            ['123'],
            ['true'],
            ['null'],
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
        $jsonString = new JsonString($value);

        // When
        $stringRepresentation = (string) $jsonString;

        // Then
        $this->assertSame($expected, $stringRepresentation);
    }

    public function dataProviderForValidValuesStringRepresentation(): array
    {
        return [
            ['{}', '{}'],
            ['[]', '[]'],
            ['{"key":"value"}', '{"key":"value"}'],
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
        $jsonString = new JsonString($value);

        // When
        $jsonSerialization = json_encode($jsonString);

        // Then
        $this->assertSame($expected, $jsonSerialization);
    }

    public function dataProviderForValidValuesJsonStringRepresentation(): array
    {
        return [
            ['{}', '"{}"'],
            ['[]', '"[]"'],
            ['{"key":"value"}', '"{\"key\":\"value\"}"'],
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
        $jsonString = new JsonString($value);
    }

    public function dataProviderForInvalidValues(): array
    {
        return [
            [''],
            ['{invalid}'],
            ['{"key":}'],
            ['{key: value}'],
            ['undefined'],
        ];
    }
}
