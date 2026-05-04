<?php

declare(strict_types=1);

namespace StrongType\Tests\String;

use StrongType\Exception\StrongTypeException;
use StrongType\String\HexColorString;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;

class HexColorStringTest extends \PHPUnit\Framework\TestCase
{
    #[Test]
    #[DataProvider('dataProviderForValidValues')]
    public function testValidValue(string $value)
    {
        // When
        $hexColorString = new HexColorString($value);

        // Then
        $this->expectNotToPerformAssertions();
    }

    #[Test]
    #[DataProvider('dataProviderForValidValues')]
    public function testGetValue(string $value)
    {
        // Given
        $hexColorString = new HexColorString($value);

        // When
        $obtainedValue = $hexColorString->getValue();

        // Then
        $this->assertSame($value, $obtainedValue);
    }

    #[Test]
    #[DataProvider('dataProviderForValidValues')]
    public function testDebugInfo(string $value)
    {
        // Given
        $hexColorString = new HexColorString($value);

        // When
        $debugInfo = $hexColorString->__debugInfo();

        // Then
        $this->assertSame($value, $debugInfo['value']);
    }

    public static function dataProviderForValidValues(): array
    {
        return [
            ['#000'],
            ['#fff'],
            ['#FFF'],
            ['#abc'],
            ['#000000'],
            ['#ffffff'],
            ['#FFFFFF'],
            ['#aAbBcC'],
            ['#123456'],
            ['#f0F0f0'],
        ];
    }

    #[Test]
    #[DataProvider('dataProviderForValidValuesStringRepresentation')]
    public function testStringRepresentation(string $value, string $expected)
    {
        // Given
        $hexColorString = new HexColorString($value);

        // When
        $stringRepresentation = (string) $hexColorString;

        // Then
        $this->assertSame($expected, $stringRepresentation);
    }

    #[Test]
    #[DataProvider('dataProviderForValidValuesJsonStringRepresentation')]
    public function testJsonSerialization(string $value, string $expected)
    {
        // Given
        $hexColorString = new HexColorString($value);

        // When
        $jsonSerialization = \json_encode($hexColorString);

        // Then
        $this->assertSame($expected, $jsonSerialization);
    }

    public static function dataProviderForValidValuesStringRepresentation(): array
    {
        return [
            ['#000', '#000'],
            ['#fff', '#fff'],
            ['#000000', '#000000'],
            ['#ffffff', '#ffffff'],
        ];
    }

    public static function dataProviderForValidValuesJsonStringRepresentation(): array
    {
        return [
            ['#000', '"#000"'],
            ['#fff', '"#fff"'],
            ['#000000', '"#000000"'],
            ['#ffffff', '"#ffffff"'],
        ];
    }

    #[Test]
    #[DataProvider('dataProviderForInvalidValues')]
    public function testInvalidValue(string $value)
    {
        // Then
        $this->expectException(StrongTypeException::class);

        // When
        $hexColorString = new HexColorString($value);
    }

    public static function dataProviderForInvalidValues(): array
    {
        return [
            [''],
            ['000'],
            ['000000'],
            ['#00'],
            ['#0000'],
            ['#00000'],
            ['#0000000'],
            ['#ggg'],
            ['#gggggg'],
            ['red'],
            ['#'],
            ["#fff\n"],
            ["#ffffff\n"],
        ];
    }
}
