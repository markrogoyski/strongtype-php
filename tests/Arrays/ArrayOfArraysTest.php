<?php

declare(strict_types=1);

namespace StrongType\Tests\Arrays;

use StrongType\Exception\StrongTypeException;
use StrongType\Arrays\ArrayOfArrays;

class ArrayOfArraysTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @test         Valid value
     * @dataProvider dataProviderForValidValues
     * @param        array $values
     */
    public function testValidValue(array $values)
    {
        // When
        $arrayOfArrays = new ArrayOfArrays($values);

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
        $arrayOfArrays = new ArrayOfArrays($values);

        // When
        $obtainedValues = $arrayOfArrays->getValues();

        // Then
        $this->assertSame($values, $obtainedValues);

        // And
        foreach ($values as $value) {
            $this->assertIsArray($value);
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
        $arrayOfArrays = new ArrayOfArrays($values);

        // When
        $debugInfo = $arrayOfArrays->__debugInfo();

        // Then
        $this->assertSame($values, $debugInfo['values']);
    }

    public function dataProviderForValidValues(): array
    {
        return [
            [[[1]]],
            [[[1.1]]],
            [[['string']]],
            [[[true]]],
            [[[null]]],
            [[[\NAN]]],
            [[[new \stdClass()]]],
            [[[1, 2, 3, 4, 5]]],
            [[[1.1, 2.2, 3.3]]],
            [[[false, false]]],
            [[['hello', 'world', 'strong', 'type', 'php']]],
            [[[new \stdClass(), new \stdClass(), new \stdClass()]]],
            [[[1], [2], [3]]],
            [[[1.1, 2.2], [3.3, 4.4]]],
            [[['string'], ['hello', 'world'], ['php', 'strong', 'type']]],
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
        $arrayOfArrays = new ArrayOfArrays($values);

        // When
        $stringRepresentation = (string) $arrayOfArrays;

        // Then
        $this->assertSame($expected, $stringRepresentation);
    }

    public function dataProviderForValidValuesStringRepresentation(): array
    {
        return [
            [[[1, 2, 3], [3, 4, 5]], '[[1,2,3],[3,4,5]]'],
            [[[1.1, 2.2], [3.3]], '[[1.1,2.2],[3.3]]'],
            [[[false, true],[true, false]], '[[false,true],[true,false]]'],
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
        $arrayOfArrays = new ArrayOfArrays($values);

        // When
        $jsonSerialization = json_encode($arrayOfArrays);

        // Then
        $this->assertSame($expected, $jsonSerialization);
    }

    public function dataProviderForValidValuesJsonStringRepresentation(): array
    {
        return [
            [[[1, 2, 3], [3, 4, 5]], '[[1,2,3],[3,4,5]]'],
            [[[1.1, 2.2], [3.3]], '[[1.1,2.2],[3.3]]'],
            [[[false, true],[true, false]], '[[false,true],[true,false]]'],
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
        $arrayOfArrays = new ArrayOfArrays($values);

        // Then
        $this->assertCount($expectedCount, $arrayOfArrays);
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
        $arrayOfArrays = new ArrayOfArrays($values);

        // And
        $i = -1;
        $finalCount = $expectedCount - 1;

        // When
        foreach ($arrayOfArrays as $array) {
            $i++;
            $this->assertIsArray($array);
            $this->assertSame($values[$i], $array);
        }

        // Then
        $this->assertSame($finalCount, $i);

        // And when reset the iterator
        $i = -1;
        foreach ($arrayOfArrays as $array) {
            $i++;
            $this->assertIsArray($array);
            $this->assertSame($values[$i], $array);
        }

        // Then
        $this->assertSame($finalCount, $i);
    }

    public function dataProviderForValidCountedValues(): array
    {
        return [
            [[[1]], 1],
            [[[1, 2]], 1],
            [[[1, 2, 3], [2, 3, 4], [3, 4, 5]], 3],
            [[['one', 'two', 'three', 'four', 'five', 'six']], 1],
            [[['one', 'two', 'three', 'four', 'five', 'six'], ['two', 'three']], 2],
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
        $arrayOfArrays = new ArrayOfArrays($values);
    }

    public function dataProviderForInvalidValues(): array
    {
        return [
            [[]],
            [[1]],
            [[1.1]],
            [['string']],
            [[1, 2, 3]],
            [[null]],
            [[\NAN]],
            [[new \stdClass()]],
        ];
    }
}
