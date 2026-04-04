<?php

declare(strict_types=1);

namespace StrongType\Tests\Int;

use StrongType\Exception\StrongTypeException;
use StrongType\Int\EvenInt;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;

class EvenIntTest extends \PHPUnit\Framework\TestCase
{
    #[Test]
    #[DataProvider('dataProviderForValidValues')]
    public function testValidValue(int $value)
    {
        // When
        $evenInt = new EvenInt($value);

        // Then
        $this->expectNotToPerformAssertions();
    }

    #[Test]
    #[DataProvider('dataProviderForValidValues')]
    public function testGetValue(int $value)
    {
        // Given
        $evenInt = new EvenInt($value);

        // When
        $obtainedValue = $evenInt->getValue();

        // Then
        $this->assertSame($value, $obtainedValue);
    }

    #[Test]
    #[DataProvider('dataProviderForValidValues')]
    public function testDebugInfo(int $value)
    {
        // Given
        $evenInt = new EvenInt($value);

        // When
        $debugInfo = $evenInt->__debugInfo();

        // Then
        $this->assertSame($value, $debugInfo['value']);
    }

    public static function dataProviderForValidValues(): array
    {
        return [
            [0],
            [2],
            [4],
            [-2],
            [-4],
            [10],
            [1000],
            [\PHP_INT_MAX - 1],
        ];
    }

    #[Test]
    #[DataProvider('dataProviderForValidValuesStringRepresentation')]
    public function testStringRepresentation(int $value, string $expected)
    {
        // Given
        $evenInt = new EvenInt($value);

        // When
        $stringRepresentation = (string) $evenInt;

        // Then
        $this->assertSame($expected, $stringRepresentation);
    }

    #[Test]
    #[DataProvider('dataProviderForValidValuesStringRepresentation')]
    public function testJsonSerialization(int $value, string $expected)
    {
        // Given
        $evenInt = new EvenInt($value);

        // When
        $jsonSerialization = \json_encode($evenInt);

        // Then
        $this->assertSame($expected, $jsonSerialization);
    }

    public static function dataProviderForValidValuesStringRepresentation(): array
    {
        return [
            [0, '0'],
            [2, '2'],
            [4, '4'],
            [-2, '-2'],
            [-4, '-4'],
            [10, '10'],
            [1000, '1000'],
            [\PHP_INT_MAX - 1, \strval(\PHP_INT_MAX - 1)],
        ];
    }

    #[Test]
    #[DataProvider('dataProviderForInvalidValues')]
    public function testInvalidValue(int $value)
    {
        // Then
        $this->expectException(StrongTypeException::class);

        // When
        $evenInt = new EvenInt($value);
    }

    public static function dataProviderForInvalidValues(): array
    {
        return [
            [1],
            [3],
            [-1],
            [-3],
            [11],
            [1001],
            [\PHP_INT_MAX],
        ];
    }
}
