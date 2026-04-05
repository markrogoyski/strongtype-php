<?php

declare(strict_types=1);

namespace StrongType\Tests\String;

use StrongType\Exception\StrongTypeException;
use StrongType\String\SemverString;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;

class SemverStringTest extends \PHPUnit\Framework\TestCase
{
    #[Test]
    #[DataProvider('dataProviderForValidValues')]
    public function testValidValue(string $value)
    {
        // When
        $semverString = new SemverString($value);

        // Then
        $this->expectNotToPerformAssertions();
    }

    #[Test]
    #[DataProvider('dataProviderForValidValues')]
    public function testGetValue(string $value)
    {
        // Given
        $semverString = new SemverString($value);

        // When
        $obtainedValue = $semverString->getValue();

        // Then
        $this->assertSame($value, $obtainedValue);
    }

    #[Test]
    #[DataProvider('dataProviderForValidValues')]
    public function testDebugInfo(string $value)
    {
        // Given
        $semverString = new SemverString($value);

        // When
        $debugInfo = $semverString->__debugInfo();

        // Then
        $this->assertSame($value, $debugInfo['value']);
    }

    public static function dataProviderForValidValues(): array
    {
        return [
            ['0.0.0'],
            ['1.0.0'],
            ['1.2.3'],
            ['10.20.30'],
            ['1.0.0-alpha'],
            ['1.0.0-alpha.1'],
            ['1.0.0+build'],
            ['1.0.0+build.123'],
            ['1.0.0-beta+build.456'],
        ];
    }

    #[Test]
    #[DataProvider('dataProviderForValidValuesStringRepresentation')]
    public function testStringRepresentation(string $value, string $expected)
    {
        // Given
        $semverString = new SemverString($value);

        // When
        $stringRepresentation = (string) $semverString;

        // Then
        $this->assertSame($expected, $stringRepresentation);
    }

    #[Test]
    #[DataProvider('dataProviderForValidValuesJsonStringRepresentation')]
    public function testJsonSerialization(string $value, string $expected)
    {
        // Given
        $semverString = new SemverString($value);

        // When
        $jsonSerialization = \json_encode($semverString);

        // Then
        $this->assertSame($expected, $jsonSerialization);
    }

    public static function dataProviderForValidValuesStringRepresentation(): array
    {
        return [
            ['0.0.0', '0.0.0'],
            ['1.0.0', '1.0.0'],
            ['1.2.3', '1.2.3'],
            ['1.0.0-alpha', '1.0.0-alpha'],
        ];
    }

    public static function dataProviderForValidValuesJsonStringRepresentation(): array
    {
        return [
            ['0.0.0', '"0.0.0"'],
            ['1.0.0', '"1.0.0"'],
            ['1.2.3', '"1.2.3"'],
            ['1.0.0-alpha', '"1.0.0-alpha"'],
        ];
    }

    #[Test]
    #[DataProvider('dataProviderForInvalidValues')]
    public function testInvalidValue(string $value)
    {
        // Then
        $this->expectException(StrongTypeException::class);

        // When
        $semverString = new SemverString($value);
    }

    public static function dataProviderForInvalidValues(): array
    {
        return [
            [''],
            ['1'],
            ['1.0'],
            ['1.0.0.0'],
            ['v1.0.0'],
            ['01.0.0'],
            ['1.02.0'],
            ['1.0.03'],
            ['abc'],
            ['1.0.0-'],
            ['1.0.0+'],
        ];
    }
}
