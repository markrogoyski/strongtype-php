<?php

declare(strict_types=1);

namespace StrongType\Tests\Int;

use StrongType\Exception\StrongTypeException;
use StrongType\Int\PercentInt;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;

class PercentIntTest extends \PHPUnit\Framework\TestCase
{
    #[Test]
    #[DataProvider('dataProviderForValidValues')]
    public function testValidValue(int $value)
    {
        // When
        $percentInt = new PercentInt($value);

        // Then
        $this->expectNotToPerformAssertions();
    }

    #[Test]
    #[DataProvider('dataProviderForValidValues')]
    public function testGetValue(int $value)
    {
        // Given
        $percentInt = new PercentInt($value);

        // When
        $obtainedValue = $percentInt->getValue();

        // Then
        $this->assertSame($value, $obtainedValue);
    }

    #[Test]
    #[DataProvider('dataProviderForValidValues')]
    public function testDebugInfo(int $value)
    {
        // Given
        $percentInt = new PercentInt($value);

        // When
        $debugInfo = $percentInt->__debugInfo();

        // Then
        $this->assertSame($value, $debugInfo['value']);
    }

    public static function dataProviderForValidValues(): array
    {
        return [
            [0],
            [1],
            [50],
            [99],
            [100],
        ];
    }

    #[Test]
    #[DataProvider('dataProviderForValidValuesStringRepresentation')]
    public function testStringRepresentation(int $value, string $expected)
    {
        // Given
        $percentInt = new PercentInt($value);

        // When
        $stringRepresentation = (string) $percentInt;

        // Then
        $this->assertSame($expected, $stringRepresentation);
    }

    #[Test]
    #[DataProvider('dataProviderForValidValuesStringRepresentation')]
    public function testJsonSerialization(int $value, string $expected)
    {
        // Given
        $percentInt = new PercentInt($value);

        // When
        $jsonSerialization = \json_encode($percentInt);

        // Then
        $this->assertSame($expected, $jsonSerialization);
    }

    public static function dataProviderForValidValuesStringRepresentation(): array
    {
        return [
            [0, '0'],
            [1, '1'],
            [50, '50'],
            [99, '99'],
            [100, '100'],
        ];
    }

    #[Test]
    #[DataProvider('dataProviderForInvalidValues')]
    public function testInvalidValue(int $value)
    {
        // Then
        $this->expectException(StrongTypeException::class);

        // When
        $percentInt = new PercentInt($value);
    }

    public static function dataProviderForInvalidValues(): array
    {
        return [
            [-1],
            [-100],
            [101],
            [200],
            [1000],
            [\PHP_INT_MIN],
            [\PHP_INT_MAX],
        ];
    }
}
