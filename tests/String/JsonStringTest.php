<?php

declare(strict_types=1);

namespace StrongType\Tests\String;

use StrongType\Exception\StrongTypeException;
use StrongType\String\JsonString;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;

class JsonStringTest extends \PHPUnit\Framework\TestCase
{
    #[Test]
    #[DataProvider('dataProviderForValidValues')]
    public function testValidValue(string $value)
    {
        // When
        $jsonString = new JsonString($value);

        // Then
        $this->expectNotToPerformAssertions();
    }

    #[Test]
    #[DataProvider('dataProviderForValidValues')]
    public function testGetValue(string $value)
    {
        // Given
        $jsonString = new JsonString($value);

        // When
        $obtainedValue = $jsonString->getValue();

        // Then
        $this->assertSame($value, $obtainedValue);
    }

    #[Test]
    #[DataProvider('dataProviderForValidValues')]
    public function testDebugInfo(string $value)
    {
        // Given
        $jsonString = new JsonString($value);

        // When
        $debugInfo = $jsonString->__debugInfo();

        // Then
        $this->assertSame($value, $debugInfo['value']);
    }

    public static function dataProviderForValidValues(): array
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

    #[Test]
    #[DataProvider('dataProviderForValidValuesStringRepresentation')]
    public function testStringRepresentation(string $value, string $expected)
    {
        // Given
        $jsonString = new JsonString($value);

        // When
        $stringRepresentation = (string) $jsonString;

        // Then
        $this->assertSame($expected, $stringRepresentation);
    }

    public static function dataProviderForValidValuesStringRepresentation(): array
    {
        return [
            ['{}', '{}'],
            ['[]', '[]'],
            ['{"key":"value"}', '{"key":"value"}'],
        ];
    }

    #[Test]
    #[DataProvider('dataProviderForValidValuesJsonStringRepresentation')]
    public function testJsonSerialization(string $value, string $expected)
    {
        // Given
        $jsonString = new JsonString($value);

        // When
        $jsonSerialization = \json_encode($jsonString);

        // Then
        $this->assertSame($expected, $jsonSerialization);
    }

    public static function dataProviderForValidValuesJsonStringRepresentation(): array
    {
        return [
            ['{}', '"{}"'],
            ['[]', '"[]"'],
            ['{"key":"value"}', '"{\"key\":\"value\"}"'],
        ];
    }

    #[Test]
    #[DataProvider('dataProviderForInvalidValues')]
    public function testInvalidValue(string $value)
    {
        // Then
        $this->expectException(StrongTypeException::class);

        // When
        $jsonString = new JsonString($value);
    }

    public static function dataProviderForInvalidValues(): array
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
