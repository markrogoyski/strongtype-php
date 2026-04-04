<?php

declare(strict_types=1);

namespace StrongType\Tests\String;

use StrongType\Exception\StrongTypeException;
use StrongType\String\EmptyString;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;

class EmptyStringTest extends \PHPUnit\Framework\TestCase
{
    #[Test]
    public function testValidEmptyValue()
    {
        // When
        $emptyString = new EmptyString();

        // Then
        $this->assertSame('', $emptyString->getValue());
    }

    #[Test]
    #[DataProvider('dataProviderForValidValues')]
    public function testValidValue(string $value)
    {
        // When
        $emptyString = new EmptyString($value);

        // Then
        $this->expectNotToPerformAssertions();
    }

    #[Test]
    #[DataProvider('dataProviderForValidValues')]
    public function testGetValue(string $value)
    {
        // Given
        $emptyString = new EmptyString($value);

        // When
        $obtainedValue = $emptyString->getValue();

        // Then
        $this->assertSame($value, $obtainedValue);
    }

    #[Test]
    #[DataProvider('dataProviderForValidValues')]
    public function testDebugInfo(string $value)
    {
        // Given
        $emptyString = new EmptyString($value);

        // When
        $debugInfo = $emptyString->__debugInfo();

        // Then
        $this->assertSame($value, $debugInfo['value']);
    }

    public static function dataProviderForValidValues(): array
    {
        return [
            [''],
        ];
    }

    #[Test]
    #[DataProvider('dataProviderForValidValuesStringRepresentation')]
    public function testStringRepresentation(string $value, string $expected)
    {
        // Given
        $emptyString = new EmptyString($value);

        // When
        $stringRepresentation = (string) $emptyString;

        // Then
        $this->assertSame($expected, $stringRepresentation);
    }

    public static function dataProviderForValidValuesStringRepresentation(): array
    {
        return [
            ['', ''],
        ];
    }

    #[Test]
    #[DataProvider('dataProviderForValidValuesJsonStringRepresentation')]
    public function testJsonSerialization(string $value, string $expected)
    {
        // Given
        $emptyString = new EmptyString($value);

        // When
        $jsonSerialization = \json_encode($emptyString);

        // Then
        $this->assertSame($expected, $jsonSerialization);
    }

    public static function dataProviderForValidValuesJsonStringRepresentation(): array
    {
        return [
            ['', '""'],
        ];
    }

    #[Test]
    #[DataProvider('dataProviderForInvalidValues')]
    public function testInvalidValue(string $value)
    {
        // Then
        $this->expectException(StrongTypeException::class);

        // When
        $emptyString = new EmptyString($value);
    }

    public static function dataProviderForInvalidValues(): array
    {
        return [
            [' '],
            ['a'],
            ['1'],
            ['nonempty string'],
            ['_'],
            ['日本語'],
        ];
    }
}
