<?php

declare(strict_types=1);

namespace StrongType\Tests\Arrays;

use StrongType\Exception\StrongTypeException;
use StrongType\Arrays\ArrayOfFloats;

class ArrayOfFloatsTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @test         Valid value
     * @dataProvider dataProviderForValidValues
     * @param        array $values
     */
    public function testValidValue(array $values)
    {
        // When
        $arrayOfFloats = new ArrayOfFloats($values);

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
        $arrayOfFloats = new ArrayOfFloats($values);

        // When
        $obtainedValues = $arrayOfFloats->getValues();

        // Then
        $this->assertSame($values, $obtainedValues);

        // And
        foreach ($values as $value) {
            $this->assertIsFloat($value);
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
        $arrayOfFloats = new ArrayOfFloats($values);

        // When
        $debugInfo = $arrayOfFloats->__debugInfo();

        // Then
        $this->assertSame($values, $debugInfo['values']);
    }

    public function dataProviderForValidValues(): array
    {
        return [
            [[0.0]],
            [[1.0]],
            [[10.555]],
            [[1.1, 2.2, 3.3]],
            [[-4.453, 30.432, 3939.9393, 1.111]],
            [[\M_PI, \M_PI_2, \M_PI_4, \M_E, \M_EULER, \M_LN2, \M_SQRT2]],
            [[\PHP_FLOAT_MIN, \PHP_FLOAT_MAX]],
            [[23.4243, 34.5345, 1.0, 0.0, -23424.0, -345765.99969696, 1 / 2, 1 / 3, 1 / 5, 1 / 9, 1 / 10, -1 / 10]],
            [[\NAN, \INF, +\INF]],
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
        $arrayOfFloats = new ArrayOfFloats($values);

        // When
        $stringRepresentation = (string) $arrayOfFloats;

        // Then
        $this->assertSame($expected, $stringRepresentation);
    }

    public function dataProviderForValidValuesStringRepresentation(): array
    {
        return [
            [[0.0], '[0]'],
            [[1.0], '[1]'],
            [[10.555], '[10.555]'],
            [[1.1, 2.2, 3.3], '[1.1,2.2,3.3]'],
            [[-4.453, 30.432, 3939.9393, 1.111], '[-4.453,30.432,3939.9393,1.111]'],
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
        $arrayOfFloats = new ArrayOfFloats($values);

        // When
        $jsonSerialization = json_encode($arrayOfFloats);

        // Then
        $this->assertSame($expected, $jsonSerialization);
    }

    public function dataProviderForValidValuesJsonStringRepresentation(): array
    {
        return [
            [[0.0], '[0]'],
            [[1.0], '[1]'],
            [[10.555], '[10.555]'],
            [[1.1, 2.2, 3.3], '[1.1,2.2,3.3]'],
            [[-4.453, 30.432, 3939.9393, 1.111], '[-4.453,30.432,3939.9393,1.111]'],
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
        $arrayOfFloats = new ArrayOfFloats($values);

        // Then
        $this->assertCount($expectedCount, $arrayOfFloats);
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
        $arrayOfFloats = new ArrayOfFloats($values);

        // And
        $i = -1;
        $finalCount = $expectedCount - 1;

        // When
        foreach ($arrayOfFloats as $float) {
            $i++;
            $this->assertIsFloat($float);
            $this->assertSame($values[$i], $float);
        }

        // Then
        $this->assertSame($finalCount, $i);

        // And when reset the iterator
        $i = -1;
        foreach ($arrayOfFloats as $float) {
            $i++;
            $this->assertIsFloat($float);
            $this->assertSame($values[$i], $float);
        }

        // Then
        $this->assertSame($finalCount, $i);
    }

    public function dataProviderForValidCountedValues(): array
    {
        return [
            [[0.0], 1],
            [[1.0], 1],
            [[10.555], 1],
            [[1.1, 2.2, 3.3], 3],
            [[-4.453, 30.432, 3939.9393, 1.111], 4],
            [[\M_PI, \M_PI_2, \M_PI_4, \M_E, \M_EULER, \M_LN2, \M_SQRT2], 7],
            [[\PHP_FLOAT_MIN, \PHP_FLOAT_MAX], 2],
            [[23.4243, 34.5345, 1.0, 0.0, -23424.0, -345765.99969696, 1 / 2, 1 / 3, 1 / 5, 1 / 9, 1 / 10, -1 / 10], 12],
            [[\INF, +\INF], 2],
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
        $arrayOfFloats = new ArrayOfFloats($values);
    }

    public function dataProviderForInvalidValues(): array
    {
        return [
            [[]],
            [[1]],
            [['string']],
            [[[1, 2, 3]]],
            [[null]],
            [[new \stdClass()]],
        ];
    }
}
