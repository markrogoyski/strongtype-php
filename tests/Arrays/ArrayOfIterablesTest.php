<?php

declare(strict_types=1);

namespace StrongType\Tests\Arrays;

use StrongType\Exception\StrongTypeException;
use StrongType\Arrays\ArrayOfIterables;

class ArrayOfIterablesTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @test         Valid value
     * @dataProvider dataProviderForValidValues
     * @param        array $values
     */
    public function testValidValue(array $values)
    {
        // When
        $arrayOfIterables = new ArrayOfIterables($values);

        // Then
        $this->expectNotToPerformAssertions();
    }

    /**
     * @test         Get value
     * @dataProvider dataProviderForValidValues
     * @param        array $values
     */
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

    /**
     * @test         Debug info
     * @dataProvider dataProviderForValidValues
     * @param        array $values
     */
    public function testDebugInfo(array $values)
    {
        // Given
        $arrayOfIterables = new ArrayOfIterables($values);

        // When
        $debugInfo = $arrayOfIterables->__debugInfo();

        // Then
        $this->assertSame($values, $debugInfo['values']);
    }

    public function dataProviderForValidValues(): array
    {
        return [
            [[[1, 2, 3]]],
            [[[1, 2, 3], ['a', 'b', 'c']]],
            [[[], [1]]],
            [[[]]],
        ];
    }

    /**
     * @test         String representation
     * @dataProvider dataProviderForValidValuesStringRepresentation
     * @param        array  $values
     * @param        string $expected
     */
    public function testStringRepresentation(array $values, string $expected)
    {
        // Given
        $arrayOfIterables = new ArrayOfIterables($values);

        // When
        $stringRepresentation = (string) $arrayOfIterables;

        // Then
        $this->assertSame($expected, $stringRepresentation);
    }

    public function dataProviderForValidValuesStringRepresentation(): array
    {
        return [
            [[[1, 2, 3]], '[[1,2,3]]'],
            [[[1, 2, 3], ['a', 'b', 'c']], '[[1,2,3],["a","b","c"]]'],
            [[[], [1]], '[[],[1]]'],
            [[[]], '[[]]'],
        ];
    }

    /**
     * @test         JSON serialization
     * @dataProvider dataProviderForValidValuesJsonStringRepresentation
     * @param        array  $values
     * @param        string $expected
     */
    public function testJsonSerialization(array $values, string $expected)
    {
        // Given
        $arrayOfIterables = new ArrayOfIterables($values);

        // When
        $jsonSerialization = json_encode($arrayOfIterables);

        // Then
        $this->assertSame($expected, $jsonSerialization);
    }

    public function dataProviderForValidValuesJsonStringRepresentation(): array
    {
        return [
            [[[1, 2, 3]], '[[1,2,3]]'],
            [[[1, 2, 3], ['a', 'b', 'c']], '[[1,2,3],["a","b","c"]]'],
            [[[], [1]], '[[],[1]]'],
            [[[]], '[[]]'],
        ];
    }

    /**
     * @test         Countable interface
     * @dataProvider dataProviderForValidCountedValues
     * @param        array $values
     * @param        int   $expectedCount
     */
    public function testCountableInterface(array $values, int $expectedCount)
    {
        // Given
        $arrayOfIterables = new ArrayOfIterables($values);

        // Then
        $this->assertCount($expectedCount, $arrayOfIterables);
    }

    /**
     * @test         Iterator interface
     * @dataProvider dataProviderForValidCountedValues
     * @param        array $values
     * @param        int   $expectedCount
     */
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

    public function dataProviderForValidCountedValues(): array
    {
        return [
            [[[1, 2, 3]], 1],
            [[[1, 2, 3], ['a', 'b', 'c']], 2],
            [[[], [1]], 2],
            [[[]], 1],
        ];
    }

    /**
     * @test         Invalid value
     * @dataProvider dataProviderForInvalidValues
     * @param        array $values
     */
    public function testInvalidValue(array $values)
    {
        // Then
        $this->expectException(StrongTypeException::class);

        // When
        $arrayOfIterables = new ArrayOfIterables($values);
    }

    public function dataProviderForInvalidValues(): array
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
