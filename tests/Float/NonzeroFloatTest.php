<?php

declare(strict_types=1);

namespace StrongType\Tests\Float;

use StrongType\Exception\StrongTypeException;
use StrongType\Float\NonzeroFloat;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;

class NonzeroFloatTest extends \PHPUnit\Framework\TestCase
{
    #[Test]
    #[DataProvider('dataProviderForValidValues')]
    public function testValidValue(float $value)
    {
        // When
        $nonnegativeFloat = new NonzeroFloat($value);

        // Then
        $this->expectNotToPerformAssertions();
    }

    #[Test]
    #[DataProvider('dataProviderForValidValues')]
    public function testGetValue(float $value)
    {
        // Given
        $nonnegativeFloat = new NonzeroFloat($value);

        // When
        $obtainedValue = $nonnegativeFloat->getValue();

        // Then
        $this->assertSame($value, $obtainedValue);
    }

    #[Test]
    #[DataProvider('dataProviderForValidValues')]
    public function testDebugInfo(float $value)
    {
        // Given
        $nonnegativeFloat = new NonzeroFloat($value);

        // When
        $debugInfo = $nonnegativeFloat->__debugInfo();

        // Then
        $this->assertSame($value, $debugInfo['value']);
    }

    public static function dataProviderForValidValues(): array
    {
        return [
            [-1.0],
            [-2.2],
            [-3.945],
            [-10.1],
            [-1000.005],
            [-8973298792.777],
            [-\PHP_FLOAT_MIN],
            [-\PHP_FLOAT_MAX],
            [1.0],
            [2.2],
            [3.945],
            [10.1],
            [1000.005],
            [8973298792.777],
            [\PHP_FLOAT_MIN],
            [\PHP_FLOAT_MAX],
        ];
    }

    #[Test]
    #[DataProvider('dataProviderForValidValuesStringRepresentation')]
    public function testStringRepresentation(float $value, string $expected)
    {
        // Given
        $nonnegativeFloat = new NonzeroFloat($value);

        // When
        $stringRepresentation = (string) $nonnegativeFloat;

        // Then
        $this->assertSame($expected, $stringRepresentation);
    }

    #[Test]
    #[DataProvider('dataProviderForValidValuesStringRepresentation')]
    public function testJsonSerialization(float $value, string $expected)
    {
        // Given
        $nonnegativeFloat = new NonzeroFloat($value);

        // When
        $jsonSerialization = \json_encode($nonnegativeFloat);

        // Then
        $this->assertSame($expected, $jsonSerialization);
    }

    public static function dataProviderForValidValuesStringRepresentation(): array
    {
        return [
            [-1.0, '-1'],
            [-2.2, '-2.2'],
            [-3.945, '-3.945'],
            [-10.1, '-10.1'],
            [-1000.005, '-1000.005'],
            [-8973298792.777, '-8973298792.777'],
            [1.0, '1'],
            [2.2, '2.2'],
            [3.945, '3.945'],
            [10.1, '10.1'],
            [1000.005, '1000.005'],
            [8973298792.777, '8973298792.777'],
        ];
    }

    #[Test]
    #[DataProvider('dataProviderForInvalidValues')]
    public function testInvalidValue(float $value)
    {
        // Then
        $this->expectException(StrongTypeException::class);

        // When
        $nonnegativeFloat = new NonzeroFloat($value);
    }

    public static function dataProviderForInvalidValues(): array
    {
        return [
            [0.0],
        ];
    }
}
