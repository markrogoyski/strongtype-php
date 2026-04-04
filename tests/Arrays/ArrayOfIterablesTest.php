<?php

declare(strict_types=1);

namespace StrongType\Tests\Arrays;

use StrongType\Exception\StrongTypeException;
use StrongType\Arrays\ArrayOfIterables;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;

class ArrayOfIterablesTest extends \PHPUnit\Framework\TestCase
{
    #[Test]
    #[DataProvider('dataProviderForValidValues')]
    public function testValidValue(array $values)
    {
        // When
        $arrayOfIterables = new ArrayOfIterables($values);

        // Then
        $this->expectNotToPerformAssertions();
    }

    #[Test]
    #[DataProvider('dataProviderForValidValues')]
    public function testGetValue(array $values)
    {
        // Given
        $arrayOfIterables = new ArrayOfIterables($values);

        // When
        $obtainedValues = $arrayOfIterables->getValues();

        // Then
        $this->assertSame($values, $obtainedValues);

        // And
        foreach ($values as $value) {
            $this->assertIsIterable($value);
        }
    }

    #[Test]
    #[DataProvider('dataProviderForValidValues')]
    public function testDebugInfo(array $values)
    {
        // Given
        $arrayOfIterables = new ArrayOfIterables($values);

        // When
        $debugInfo = $arrayOfIterables->__debugInfo();

        // Then
        $this->assertSame($values, $debugInfo['values']);
    }

    public static function dataProviderForValidValues(): array
    {
        return [
            [[[1, 2, 3]]],
            [[[1, 2, 3], ['a', 'b', 'c']]],
            [[[], [1]]],
            [[[]]],
        ];
    }

    #[Test]
    #[DataProvider('dataProviderForValidValuesStringRepresentation')]
    public function testStringRepresentation(array $values, string $expected)
    {
        // Given
        $arrayOfIterables = new ArrayOfIterables($values);

        // When
        $stringRepresentation = (string) $arrayOfIterables;

        // Then
        $this->assertSame($expected, $stringRepresentation);
    }

    public static function dataProviderForValidValuesStringRepresentation(): array
    {
        return [
            [[[1, 2, 3]], '[[1,2,3]]'],
            [[[1, 2, 3], ['a', 'b', 'c']], '[[1,2,3],["a","b","c"]]'],
            [[[], [1]], '[[],[1]]'],
            [[[]], '[[]]'],
        ];
    }

    #[Test]
    #[DataProvider('dataProviderForValidValuesJsonStringRepresentation')]
    public function testJsonSerialization(array $values, string $expected)
    {
        // Given
        $arrayOfIterables = new ArrayOfIterables($values);

        // When
        $jsonSerialization = \json_encode($arrayOfIterables);

        // Then
        $this->assertSame($expected, $jsonSerialization);
    }

    public static function dataProviderForValidValuesJsonStringRepresentation(): array
    {
        return [
            [[[1, 2, 3]], '[[1,2,3]]'],
            [[[1, 2, 3], ['a', 'b', 'c']], '[[1,2,3],["a","b","c"]]'],
            [[[], [1]], '[[],[1]]'],
            [[[]], '[[]]'],
        ];
    }

    #[Test]
    #[DataProvider('dataProviderForValidCountedValues')]
    public function testCountableInterface(array $values, int $expectedCount)
    {
        // Given
        $arrayOfIterables = new ArrayOfIterables($values);

        // Then
        $this->assertCount($expectedCount, $arrayOfIterables);
    }

    #[Test]
    #[DataProvider('dataProviderForValidCountedValues')]
    public function testIteratorInterface(array $values, int $expectedCount)
    {
        // Given
        $arrayOfIterables = new ArrayOfIterables($values);

        // And
        $i = -1;
        $finalCount = $expectedCount - 1;

        // When
        foreach ($arrayOfIterables as $iterable) {
            $i++;
            $this->assertIsIterable($iterable);
            $this->assertSame($values[$i], $iterable);
        }

        // Then
        $this->assertSame($finalCount, $i);

        // And when reset the iterator
        $i = -1;
        foreach ($arrayOfIterables as $iterable) {
            $i++;
            $this->assertIsIterable($iterable);
            $this->assertSame($values[$i], $iterable);
        }

        // Then
        $this->assertSame($finalCount, $i);
    }

    public static function dataProviderForValidCountedValues(): array
    {
        return [
            [[[1, 2, 3]], 1],
            [[[1, 2, 3], ['a', 'b', 'c']], 2],
            [[[], [1]], 2],
            [[[]], 1],
        ];
    }

    #[Test]
    #[DataProvider('dataProviderForInvalidValues')]
    public function testInvalidValue(array $values)
    {
        // Then
        $this->expectException(StrongTypeException::class);

        // When
        $arrayOfIterables = new ArrayOfIterables($values);
    }

    public static function dataProviderForInvalidValues(): array
    {
        return [
            [[]],
            [[1]],
            [['string']],
            [[null]],
            [[true]],
        ];
    }
}
