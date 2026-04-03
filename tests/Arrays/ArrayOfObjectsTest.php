<?php

declare(strict_types=1);

namespace StrongType\Tests\Arrays;

use StrongType\Exception\StrongTypeException;
use StrongType\Arrays\ArrayOfObjects;

class ArrayOfObjectsTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @test         Valid value
     * @dataProvider dataProviderForValidValues
     * @param        array $values
     */
    public function testValidValue(array $values)
    {
        // When
        $arrayOfObjects = new ArrayOfObjects($values);

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
        $arrayOfObjects = new ArrayOfObjects($values);

        // When
        $obtainedValues = $arrayOfObjects->getValues();

        // Then
        $this->assertSame($values, $obtainedValues);

        // And
        foreach ($values as $value) {
            $this->assertIsObject($value);
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
        $arrayOfObjects = new ArrayOfObjects($values);

        // When
        $debugInfo = $arrayOfObjects->__debugInfo();

        // Then
        $this->assertSame($values, $debugInfo['values']);
    }

    public static function dataProviderForValidValues(): array
    {
        return [
            [[new \stdClass()]],
            [[new \stdClass(), new \stdClass()]],
            [[new \ArrayIterator([])]],
        ];
    }

    /**
     * @test         String representation
     */
    public function testStringRepresentation()
    {
        // Given
        $arrayOfObjects = new ArrayOfObjects([new \stdClass()]);

        // When
        $stringRepresentation = (string) $arrayOfObjects;

        // Then
        $this->assertSame('[{}]', $stringRepresentation);
    }

    /**
     * @test         JSON serialization
     */
    public function testJsonSerialization()
    {
        // Given
        $arrayOfObjects = new ArrayOfObjects([new \stdClass()]);

        // When
        $jsonSerialization = json_encode($arrayOfObjects);

        // Then
        $this->assertSame('[{}]', $jsonSerialization);
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
        $arrayOfObjects = new ArrayOfObjects($values);

        // Then
        $this->assertCount($expectedCount, $arrayOfObjects);
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
        $arrayOfObjects = new ArrayOfObjects($values);

        // And
        $i = -1;
        $finalCount = $expectedCount - 1;

        // When
        foreach ($arrayOfObjects as $object) {
            $i++;
            $this->assertIsObject($object);
            $this->assertSame($values[$i], $object);
        }

        // Then
        $this->assertSame($finalCount, $i);

        // And when reset the iterator
        $i = -1;
        foreach ($arrayOfObjects as $object) {
            $i++;
            $this->assertIsObject($object);
            $this->assertSame($values[$i], $object);
        }

        // Then
        $this->assertSame($finalCount, $i);
    }

    public static function dataProviderForValidCountedValues(): array
    {
        return [
            [[new \stdClass()], 1],
            [[new \stdClass(), new \stdClass()], 2],
            [[new \ArrayIterator([])], 1],
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
        $arrayOfObjects = new ArrayOfObjects($values);
    }

    public static function dataProviderForInvalidValues(): array
    {
        return [
            [[]],
            [[1]],
            [[1.0]],
            [['string']],
            [[null]],
            [[true]],
        ];
    }
}
