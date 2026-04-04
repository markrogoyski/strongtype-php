<?php

declare(strict_types=1);

namespace StrongType\Tests\Bool;

use StrongType\Exception\StrongTypeException;
use StrongType\Bool\FalseValue;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;

class FalseValueTest extends \PHPUnit\Framework\TestCase
{
    #[Test]
    #[DataProvider('dataProviderForValidValues')]
    public function testValidValue(bool $value)
    {
        // When
        $falseValue = new FalseValue($value);

        // Then
        $this->expectNotToPerformAssertions();
    }

    #[Test]
    #[DataProvider('dataProviderForValidValues')]
    public function testGetValue(bool $value)
    {
        // Given
        $falseValue = new FalseValue($value);

        // When
        $obtainedValue = $falseValue->getValue();

        // Then
        $this->assertSame($value, $obtainedValue);
    }

    #[Test]
    #[DataProvider('dataProviderForValidValues')]
    public function testDebugInfo(bool $value)
    {
        // Given
        $falseValue = new FalseValue($value);

        // When
        $debugInfo = $falseValue->__debugInfo();

        // Then
        $this->assertSame($value, $debugInfo['value']);
    }

    public static function dataProviderForValidValues(): array
    {
        return [
            [false],
        ];
    }

    #[Test]
    #[DataProvider('dataProviderForValidValuesStringRepresentation')]
    public function testStringRepresentation(bool $value, string $expected)
    {
        // Given
        $falseValue = new FalseValue($value);

        // When
        $stringRepresentation = (string) $falseValue;

        // Then
        $this->assertSame($expected, $stringRepresentation);
    }

    #[Test]
    #[DataProvider('dataProviderForValidValuesStringRepresentation')]
    public function testJsonSerialization(bool $value, string $expected)
    {
        // Given
        $falseValue = new FalseValue($value);

        // When
        $jsonSerialization = \json_encode($falseValue);

        // Then
        $this->assertSame($expected, $jsonSerialization);
    }

    public static function dataProviderForValidValuesStringRepresentation(): array
    {
        return [
            [false, 'false'],
        ];
    }

    #[Test]
    #[DataProvider('dataProviderForInvalidValues')]
    public function testInvalidValue(bool $value)
    {
        // Then
        $this->expectException(\TypeError::class);

        // When
        $falseValue = new FalseValue($value);
    }

    public static function dataProviderForInvalidValues(): array
    {
        return [
            [true],
        ];
    }
}
