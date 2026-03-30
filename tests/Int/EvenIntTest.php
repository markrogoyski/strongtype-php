<?php

declare(strict_types=1);

namespace StrongType\Tests\Int;

use StrongType\Exception\StrongTypeException;
use StrongType\Int\EvenInt;

class EvenIntTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @test         Valid value
     * @dataProvider dataProviderForValidValues
     * @param        int $value
     */
    public function testValidValue(int $value)
    {
        // When
        $evenInt = new EvenInt($value);

        // Then
        $this->expectNotToPerformAssertions();
    }

    /**
     * @test         Get value
     * @dataProvider dataProviderForValidValues
     * @param        int $value
     */
    public function testGetValue(int $value)
    {
        // Given
        $evenInt = new EvenInt($value);

        // When
        $obtainedValue = $evenInt->getValue();

        // Then
        $this->assertSame($value, $obtainedValue);
    }

    /**
     * @test         Debug info
     * @dataProvider dataProviderForValidValues
     * @param        int $value
     */
    public function testDebugInfo(int $value)
    {
        // Given
        $evenInt = new EvenInt($value);

        // When
        $debugInfo = $evenInt->__debugInfo();

        // Then
        $this->assertSame($value, $debugInfo['value']);
    }

    public function dataProviderForValidValues(): array
    {
        return [
            [0],
            [2],
            [4],
            [-2],
            [-4],
            [10],
            [1000],
            [\PHP_INT_MAX - 1],
        ];
    }

    /**
     * @test         String representation
     * @dataProvider dataProviderForValidValuesStringRepresentation
     * @param        int    $value
     * @param        string $expected
     */
    public function testStringRepresentation(int $value, string $expected)
    {
        // Given
        $evenInt = new EvenInt($value);

        // When
        $stringRepresentation = (string) $evenInt;

        // Then
        $this->assertSame($expected, $stringRepresentation);
    }

    /**
     * @test         JSON serialization
     * @dataProvider dataProviderForValidValuesStringRepresentation
     * @param        int    $value
     * @param        string $expected
     */
    public function testJsonSerialization(int $value, string $expected)
    {
        // Given
        $evenInt = new EvenInt($value);

        // When
        $jsonSerialization = json_encode($evenInt);

        // Then
        $this->assertSame($expected, $jsonSerialization);
    }

    public function dataProviderForValidValuesStringRepresentation(): array
    {
        return [
            [0, '0'],
            [2, '2'],
            [4, '4'],
            [-2, '-2'],
            [-4, '-4'],
            [10, '10'],
            [1000, '1000'],
            [\PHP_INT_MAX - 1, \strval(\PHP_INT_MAX - 1)],
        ];
    }

    /**
     * @test         Invalid value
     * @dataProvider dataProviderForInvalidValues
     * @param        int $value
     */
    public function testInvalidValue(int $value)
    {
        // Then
        $this->expectException(StrongTypeException::class);

        // When
        $evenInt = new EvenInt($value);
    }

    public function dataProviderForInvalidValues(): array
    {
        return [
            [1],
            [3],
            [-1],
            [-3],
            [11],
            [1001],
            [\PHP_INT_MAX],
        ];
    }
}
