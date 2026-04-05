<?php

declare(strict_types=1);

namespace StrongType\Tests\Arrays;

use StrongType\Exception\StrongTypeException;
use StrongType\Arrays\FixedSizeArray;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;

class FixedSizeArrayTest extends \PHPUnit\Framework\TestCase
{
    #[Test]
    #[DataProvider('dataProviderForValidValues')]
    public function testValidValue(array $values, int $size)
    {
        // When
        $fixedSizeArray = new FixedSizeArray($values, $size);

        // Then
        $this->expectNotToPerformAssertions();
    }

    #[Test]
    #[DataProvider('dataProviderForValidValues')]
    public function testGetValue(array $values, int $size)
    {
        // Given
        $fixedSizeArray = new FixedSizeArray($values, $size);

        // When
        $obtainedValues = $fixedSizeArray->getValues();

        // Then
        $this->assertSame($values, $obtainedValues);
    }

    #[Test]
    #[DataProvider('dataProviderForValidValues')]
    public function testDebugInfo(array $values, int $size)
    {
        // Given
        $fixedSizeArray = new FixedSizeArray($values, $size);

        // When
        $debugInfo = $fixedSizeArray->__debugInfo();

        // Then
        $this->assertSame($values, $debugInfo['values']);
    }

    #[Test]
    #[DataProvider('dataProviderForValidValues')]
    public function testSize(array $values, int $size)
    {
        // Given
        $fixedSizeArray = new FixedSizeArray($values, $size);

        // Then
        $this->assertSame($size, $fixedSizeArray->size);
    }

    public static function dataProviderForValidValues(): array
    {
        return [
            [[], 0],
            [[1], 1],
            [[1, 2], 2],
            [[1, 2, 3], 3],
            [['a', 'b', 'c', 'd', 'e'], 5],
        ];
    }

    #[Test]
    #[DataProvider('dataProviderForValidValuesStringRepresentation')]
    public function testStringRepresentation(array $values, int $size, string $expected)
    {
        // Given
        $fixedSizeArray = new FixedSizeArray($values, $size);

        // When
        $stringRepresentation = (string) $fixedSizeArray;

        // Then
        $this->assertSame($expected, $stringRepresentation);
    }

    #[Test]
    #[DataProvider('dataProviderForValidValuesJsonStringRepresentation')]
    public function testJsonSerialization(array $values, int $size, string $expected)
    {
        // Given
        $fixedSizeArray = new FixedSizeArray($values, $size);

        // When
        $jsonSerialization = \json_encode($fixedSizeArray);

        // Then
        $this->assertSame($expected, $jsonSerialization);
    }

    public static function dataProviderForValidValuesStringRepresentation(): array
    {
        return [
            [[], 0, '[]'],
            [[1], 1, '[1]'],
            [[1, 2, 3], 3, '[1,2,3]'],
        ];
    }

    public static function dataProviderForValidValuesJsonStringRepresentation(): array
    {
        return [
            [[], 0, '[]'],
            [[1], 1, '[1]'],
            [[1, 2, 3], 3, '[1,2,3]'],
        ];
    }

    #[Test]
    #[DataProvider('dataProviderForValidCountedValues')]
    public function testCountableInterface(array $values, int $size)
    {
        // Given
        $fixedSizeArray = new FixedSizeArray($values, $size);

        // Then
        $this->assertCount($size, $fixedSizeArray);
    }

    public static function dataProviderForValidCountedValues(): array
    {
        return [
            [[], 0],
            [[1], 1],
            [[1, 2, 3], 3],
        ];
    }

    #[Test]
    #[DataProvider('dataProviderForInvalidValues')]
    public function testInvalidValue(array $values, int $size)
    {
        // Then
        $this->expectException(StrongTypeException::class);

        // When
        $fixedSizeArray = new FixedSizeArray($values, $size);
    }

    public static function dataProviderForInvalidValues(): array
    {
        return [
            [[], 1],
            [[1], 0],
            [[1], 2],
            [[1, 2, 3], 2],
            [[1, 2, 3], 4],
        ];
    }
}
