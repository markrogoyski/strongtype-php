<?php

declare(strict_types=1);

namespace StrongType\Tests\Arrays;

use StrongType\Exception\StrongTypeException;
use StrongType\Arrays\ArrayOfBools;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;

class ArrayOfBoolsTest extends \PHPUnit\Framework\TestCase
{
    #[Test]
    #[DataProvider('dataProviderForValidValues')]
    public function testValidValue(array $values)
    {
        // When
        $arrayOfBools = new ArrayOfBools($values);

        // Then
        $this->expectNotToPerformAssertions();
    }

    #[Test]
    #[DataProvider('dataProviderForValidValues')]
    public function testGetValue(array $values)
    {
        // Given
        $arrayOfBools = new ArrayOfBools($values);

        // When
        $obtainedValues = $arrayOfBools->getValues();

        // Then
        $this->assertSame($values, $obtainedValues);

        // And
        foreach ($values as $value) {
            $this->assertIsBool($value);
        }
    }

    #[Test]
    #[DataProvider('dataProviderForValidValues')]
    public function testDebugInfo(array $values)
    {
        // Given
        $arrayOfBools = new ArrayOfBools($values);

        // When
        $debugInfo = $arrayOfBools->__debugInfo();

        // Then
        $this->assertSame($values, $debugInfo['values']);
    }

    public static function dataProviderForValidValues(): array
    {
        return [
            [[true]],
            [[false]],
            [[true, false]],
            [[false, true]],
            [[true, true]],
            [[false, false]],
            [[true, false, true, false, true, false]],
        ];
    }

    #[Test]
    #[DataProvider('dataProviderForValidValuesStringRepresentation')]
    public function testStringRepresentation(array $values, string $expected)
    {
        // Given
        $arrayOfBools = new ArrayOfBools($values);

        // When
        $stringRepresentation = (string) $arrayOfBools;

        // Then
        $this->assertSame($expected, $stringRepresentation);
    }

    public static function dataProviderForValidValuesStringRepresentation(): array
    {
        return [
            [[true], '[true]'],
            [[false], '[false]'],
            [[true, false], '[true,false]'],
            [[false, true], '[false,true]'],
            [[true, true], '[true,true]'],
            [[false, false], '[false,false]'],
            [[true, false, true, false, true, false], '[true,false,true,false,true,false]'],
        ];
    }

    #[Test]
    #[DataProvider('dataProviderForValidValuesJsonStringRepresentation')]
    public function testJsonSerialization(array $values, string $expected)
    {
        // Given
        $arrayOfBools = new ArrayOfBools($values);

        // When
        $jsonSerialization = \json_encode($arrayOfBools);

        // Then
        $this->assertSame($expected, $jsonSerialization);
    }

    public static function dataProviderForValidValuesJsonStringRepresentation(): array
    {
        return [
            [[true], '[true]'],
            [[false], '[false]'],
            [[true, false], '[true,false]'],
            [[false, true], '[false,true]'],
            [[true, true], '[true,true]'],
            [[false, false], '[false,false]'],
            [[true, false, true, false, true, false], '[true,false,true,false,true,false]'],
        ];
    }

    #[Test]
    #[DataProvider('dataProviderForValidCountedValues')]
    public function testCountableInterface(array $values, int $expectedCount)
    {
        // Given
        $arrayOfBools = new ArrayOfBools($values);

        // Then
        $this->assertCount($expectedCount, $arrayOfBools);
    }

    #[Test]
    #[DataProvider('dataProviderForValidCountedValues')]
    public function testIteratorInterface(array $values, int $expectedCount)
    {
        // Given
        $arrayOfBools = new ArrayOfBools($values);

        // And
        $i = -1;
        $finalCount = $expectedCount - 1;

        // When
        foreach ($arrayOfBools as $bool) {
            $i++;
            $this->assertIsBool($bool);
            $this->assertSame($values[$i], $bool);
        }

        // Then
        $this->assertSame($finalCount, $i);

        // And when reset the iterator
        $i = -1;
        foreach ($arrayOfBools as $bool) {
            $i++;
            $this->assertIsBool($bool);
            $this->assertSame($values[$i], $bool);
        }

        // Then
        $this->assertSame($finalCount, $i);
    }

    public static function dataProviderForValidCountedValues(): array
    {
        return [
            [[true], 1],
            [[true, false], 2],
            [[true, false, true, false, true, false], 6],
        ];
    }

    #[Test]
    #[DataProvider('dataProviderForInvalidValues')]
    public function testInvalidValue(array $values)
    {
        // Then
        $this->expectException(StrongTypeException::class);

        // When
        $arrayOfBools = new ArrayOfBools($values);
    }

    public static function dataProviderForInvalidValues(): array
    {
        return [
            [[]],
            [[1]],
            [[1.1]],
            [['string']],
            [[[1, 2, 3]]],
            [[null]],
            [[\NAN]],
            [[new \stdClass()]],
        ];
    }
}
