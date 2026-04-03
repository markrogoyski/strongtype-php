<?php

declare(strict_types=1);

namespace StrongType\Tests\Arrays;

use StrongType\Exception\StrongTypeException;
use StrongType\Arrays\ArrayOfInts;

class ArrayOfIntsTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @test         Valid value
     * @dataProvider dataProviderForValidValues
     * @param        array $values
     */
    public function testValidValue(array $values)
    {
        // When
        $arrayOfInts = new ArrayOfInts($values);

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
        $arrayOfInts = new ArrayOfInts($values);

        // When
        $obtainedValues = $arrayOfInts->getValues();

        // Then
        $this->assertSame($values, $obtainedValues);

        // And
        foreach ($values as $value) {
            $this->assertIsInt($value);
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
        $arrayOfInts = new ArrayOfInts($values);

        // When
        $debugInfo = $arrayOfInts->__debugInfo();

        // Then
        $this->assertSame($values, $debugInfo['values']);
    }

    public static function dataProviderForValidValues(): array
    {
        return [
            [[1]],
            [[1, 2, 3]],
            [[-1, 0, 1]],
            [[\PHP_INT_MIN, \PHP_INT_MAX]],
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
        $arrayOfInts = new ArrayOfInts($values);

        // When
        $stringRepresentation = (string) $arrayOfInts;

        // Then
        $this->assertSame($expected, $stringRepresentation);
    }

    public static function dataProviderForValidValuesStringRepresentation(): array
    {
        return [
            [[1], '[1]'],
            [[1, 2, 3], '[1,2,3]'],
            [[-1, 0, 1], '[-1,0,1]'],
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
        $arrayOfInts = new ArrayOfInts($values);

        // When
        $jsonSerialization = json_encode($arrayOfInts);

        // Then
        $this->assertSame($expected, $jsonSerialization);
    }

    public static function dataProviderForValidValuesJsonStringRepresentation(): array
    {
        return [
            [[1], '[1]'],
            [[1, 2, 3], '[1,2,3]'],
            [[-1, 0, 1], '[-1,0,1]'],
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
        $arrayOfInts = new ArrayOfInts($values);

        // Then
        $this->assertCount($expectedCount, $arrayOfInts);
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
        $arrayOfInts = new ArrayOfInts($values);

        // And
        $i = -1;
        $finalCount = $expectedCount - 1;

        // When
        foreach ($arrayOfInts as $int) {
            $i++;
            $this->assertIsInt($int);
            $this->assertSame($values[$i], $int);
        }

        // Then
        $this->assertSame($finalCount, $i);

        // And when reset the iterator
        $i = -1;
        foreach ($arrayOfInts as $int) {
            $i++;
            $this->assertIsInt($int);
            $this->assertSame($values[$i], $int);
        }

        // Then
        $this->assertSame($finalCount, $i);
    }

    public static function dataProviderForValidCountedValues(): array
    {
        return [
            [[1], 1],
            [[1, 2, 3], 3],
            [[-1, 0, 1], 3],
            [[\PHP_INT_MIN, \PHP_INT_MAX], 2],
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
        $arrayOfInts = new ArrayOfInts($values);
    }

    public static function dataProviderForInvalidValues(): array
    {
        return [
            [[]],
            [[1.0]],
            [['string']],
            [[null]],
            [[true]],
        ];
    }
}
