<?php

declare(strict_types=1);

namespace StrongType\Tests\Arrays;

use StrongType\Exception\StrongTypeException;
use StrongType\Arrays\EmptyArray;

class EmptyArrayTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @test         Valid value
     * @dataProvider dataProviderForValidValues
     * @param        array $values
     */
    public function testValidValue(array $values)
    {
        // When
        $emptyArray = new EmptyArray($values);

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
        $emptyArray = new EmptyArray($values);

        // When
        $obtainedValues = $emptyArray->getValues();

        // Then
        $this->assertSame($values, $obtainedValues);
    }

    /**
     * @test         Debug info
     * @dataProvider dataProviderForValidValues
     * @param        array $values
     */
    public function testDebugInfo(array $values)
    {
        // Given
        $emptyArray = new EmptyArray($values);

        // When
        $debugInfo = $emptyArray->__debugInfo();

        // Then
        $this->assertSame($values, $debugInfo['values']);
    }

    public static function dataProviderForValidValues(): array
    {
        return [
            [[]],
        ];
    }

    /**
     * @test         String representation
     */
    public function testStringRepresentation()
    {
        // Given
        $emptyArray = new EmptyArray([]);

        // When
        $stringRepresentation = (string) $emptyArray;

        // Then
        $this->assertSame('[]', $stringRepresentation);
    }

    /**
     * @test         JSON serialization
     */
    public function testJsonSerialization()
    {
        // Given
        $emptyArray = new EmptyArray([]);

        // When
        $jsonSerialization = \json_encode($emptyArray);

        // Then
        $this->assertSame('[]', $jsonSerialization);
    }

    /**
     * @test         Countable interface
     */
    public function testCountableInterface()
    {
        // Given
        $emptyArray = new EmptyArray([]);

        // Then
        $this->assertCount(0, $emptyArray);
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
        $emptyArray = new EmptyArray($values);
    }

    public static function dataProviderForInvalidValues(): array
    {
        return [
            [[1]],
            [['a']],
            [[true]],
            [[null]],
        ];
    }
}
