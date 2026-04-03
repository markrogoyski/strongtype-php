<?php

declare(strict_types=1);

namespace StrongType\Tests\Arrays;

use StrongType\Exception\StrongTypeException;
use StrongType\Arrays\ArrayOfStrings;

class ArrayOfStringsTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @test         Valid value
     * @dataProvider dataProviderForValidValues
     * @param        array $values
     */
    public function testValidValue(array $values)
    {
        // When
        $arrayOfStrings = new ArrayOfStrings($values);

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
        $arrayOfStrings = new ArrayOfStrings($values);

        // When
        $obtainedValues = $arrayOfStrings->getValues();

        // Then
        $this->assertSame($values, $obtainedValues);

        // And
        foreach ($values as $value) {
            $this->assertIsString($value);
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
        $arrayOfStrings = new ArrayOfStrings($values);

        // When
        $debugInfo = $arrayOfStrings->__debugInfo();

        // Then
        $this->assertSame($values, $debugInfo['values']);
    }

    public static function dataProviderForValidValues(): array
    {
        return [
            [['a']],
            [['hello', 'world']],
            [['', 'nonempty']],
            [['日本語']],
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
        $arrayOfStrings = new ArrayOfStrings($values);

        // When
        $stringRepresentation = (string) $arrayOfStrings;

        // Then
        $this->assertSame($expected, $stringRepresentation);
    }

    public static function dataProviderForValidValuesStringRepresentation(): array
    {
        return [
            [['a'], '["a"]'],
            [['hello', 'world'], '["hello","world"]'],
            [['', 'nonempty'], '["","nonempty"]'],
            [['日本語'], '["\u65e5\u672c\u8a9e"]'],
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
        $arrayOfStrings = new ArrayOfStrings($values);

        // When
        $jsonSerialization = \json_encode($arrayOfStrings);

        // Then
        $this->assertSame($expected, $jsonSerialization);
    }

    public static function dataProviderForValidValuesJsonStringRepresentation(): array
    {
        return [
            [['a'], '["a"]'],
            [['hello', 'world'], '["hello","world"]'],
            [['', 'nonempty'], '["","nonempty"]'],
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
        $arrayOfStrings = new ArrayOfStrings($values);

        // Then
        $this->assertCount($expectedCount, $arrayOfStrings);
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
        $arrayOfStrings = new ArrayOfStrings($values);

        // And
        $i = -1;
        $finalCount = $expectedCount - 1;

        // When
        foreach ($arrayOfStrings as $string) {
            $i++;
            $this->assertIsString($string);
            $this->assertSame($values[$i], $string);
        }

        // Then
        $this->assertSame($finalCount, $i);

        // And when reset the iterator
        $i = -1;
        foreach ($arrayOfStrings as $string) {
            $i++;
            $this->assertIsString($string);
            $this->assertSame($values[$i], $string);
        }

        // Then
        $this->assertSame($finalCount, $i);
    }

    public static function dataProviderForValidCountedValues(): array
    {
        return [
            [['a'], 1],
            [['hello', 'world'], 2],
            [['', 'nonempty'], 2],
            [['日本語'], 1],
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
        $arrayOfStrings = new ArrayOfStrings($values);
    }

    public static function dataProviderForInvalidValues(): array
    {
        return [
            [[]],
            [[1]],
            [[1.0]],
            [[null]],
            [[true]],
            [[new \stdClass()]],
        ];
    }
}
