<?php

declare(strict_types=1);

namespace StrongType\Tests\Bool;

use StrongType\Exception\StrongTypeException;
use StrongType\Bool\TrueValue;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;

class TrueValueTest extends \PHPUnit\Framework\TestCase
{
    #[Test]
    #[DataProvider('dataProviderForValidValues')]
    public function testValidValue(bool $value)
    {
        // When
        $trueValue = new TrueValue($value);

        // Then
        $this->expectNotToPerformAssertions();
    }

    #[Test]
    #[DataProvider('dataProviderForValidValues')]
    public function testGetValue(bool $value)
    {
        // Given
        $trueValue = new TrueValue($value);

        // When
        $obtainedValue = $trueValue->getValue();

        // Then
        $this->assertSame($value, $obtainedValue);
    }

    #[Test]
    #[DataProvider('dataProviderForValidValues')]
    public function testDebugInfo(bool $value)
    {
        // Given
        $trueValue = new TrueValue($value);

        // When
        $debugInfo = $trueValue->__debugInfo();

        // Then
        $this->assertSame($value, $debugInfo['value']);
    }

    public static function dataProviderForValidValues(): array
    {
        return [
            [true],
        ];
    }

    #[Test]
    #[DataProvider('dataProviderForValidValuesStringRepresentation')]
    public function testStringRepresentation(bool $value, string $expected)
    {
        // Given
        $trueValue = new TrueValue($value);

        // When
        $stringRepresentation = (string) $trueValue;

        // Then
        $this->assertSame($expected, $stringRepresentation);
    }

    #[Test]
    #[DataProvider('dataProviderForValidValuesStringRepresentation')]
    public function testJsonSerialization(bool $value, string $expected)
    {
        // Given
        $trueValue = new TrueValue($value);

        // When
        $jsonSerialization = \json_encode($trueValue);

        // Then
        $this->assertSame($expected, $jsonSerialization);
    }

    public static function dataProviderForValidValuesStringRepresentation(): array
    {
        return [
            [true, 'true'],
        ];
    }

    #[Test]
    #[DataProvider('dataProviderForInvalidValues')]
    public function testInvalidValue(bool $value)
    {
        // Then
        $this->expectException(\TypeError::class);

        // When
        $trueValue = new TrueValue($value);
    }

    public static function dataProviderForInvalidValues(): array
    {
        return [
            [false],
        ];
    }
}
