<?php

declare(strict_types=1);

namespace StrongType\Tests\Arrays;

use StrongType\Exception\StrongTypeException;
use StrongType\Arrays\ArrayOfObjects;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;

class ArrayOfObjectsTest extends \PHPUnit\Framework\TestCase
{
    #[Test]
    #[DataProvider('dataProviderForValidValues')]
    public function testValidValue(array $values)
    {
        // When
        $arrayOfObjects = new ArrayOfObjects($values);

        // Then
        $this->expectNotToPerformAssertions();
    }

    #[Test]
    #[DataProvider('dataProviderForValidValues')]
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

    #[Test]
    #[DataProvider('dataProviderForValidValues')]
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

    #[Test]
    public function testStringRepresentation()
    {
        // Given
        $arrayOfObjects = new ArrayOfObjects([new \stdClass()]);

        // When
        $stringRepresentation = (string) $arrayOfObjects;

        // Then
        $this->assertSame('[{}]', $stringRepresentation);
    }

    #[Test]
    public function testJsonSerialization()
    {
        // Given
        $arrayOfObjects = new ArrayOfObjects([new \stdClass()]);

        // When
        $jsonSerialization = \json_encode($arrayOfObjects);

        // Then
        $this->assertSame('[{}]', $jsonSerialization);
    }

    #[Test]
    #[DataProvider('dataProviderForValidCountedValues')]
    public function testCountableInterface(array $values, int $expectedCount)
    {
        // Given
        $arrayOfObjects = new ArrayOfObjects($values);

        // Then
        $this->assertCount($expectedCount, $arrayOfObjects);
    }

    #[Test]
    #[DataProvider('dataProviderForValidCountedValues')]
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

    #[Test]
    #[DataProvider('dataProviderForInvalidValues')]
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
