<?php

declare(strict_types=1);

namespace StrongType\Tests\Arrays;

use StrongType\Exception\StrongTypeException;
use StrongType\Arrays\ArrayOfCallables;

class ArrayOfCallablesTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @test         Valid value
     * @dataProvider dataProviderForValidValues
     * @param        array $values
     */
    public function testValidValue(array $values)
    {
        // When
        $arrayOfCallables = new ArrayOfCallables($values);

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
        $arrayOfCallables = new ArrayOfCallables($values);

        // When
        $obtainedValues = $arrayOfCallables->getValues();

        // Then
        $this->assertSame($values, $obtainedValues);

        // And
        foreach ($values as $value) {
            $this->assertIsCallable($value);
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
        $arrayOfCallables = new ArrayOfCallables($values);

        // When
        $debugInfo = $arrayOfCallables->__debugInfo();

        // Then
        $this->assertSame($values, $debugInfo['values']);
    }

    public function dataProviderForValidValues(): array
    {
        return [
            [[fn ($x) => $x + 1]],
            [[fn ($x) => $x + 1, fn ($y) => $y * $y]],
            [[fn ($x) => $x + 1, fn ($y) => $y * $y, fn ($z) => $z / 2]],
            [['array_sum']],
            [['array_sum', 'array_product']],
            [['abs', 'cos', 'sin']],
            [[fn ($x) => $x + 1, fn ($y) => $y * $y, 'abs', 'cos']],
            [[fn ($x) => $x + 1, 'abs', fn ($y) => $y * $y, 'cos']],
            [['abs',fn ($x) => $x + 1, 'sin', fn ($y) => $y * $y]],
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
        $arrayOfCallables = new ArrayOfCallables($values);

        // When
        $stringRepresentation = (string) $arrayOfCallables;

        // Then
        $this->assertSame($expected, $stringRepresentation);
    }

    public function dataProviderForValidValuesStringRepresentation(): array
    {
        return [
            [[fn ($x) => $x + 1], '[{}]'],
            [[fn ($x) => $x + 1, fn ($y) => $y * $y], '[{},{}]'],
            [['abs', 'cos'], '["abs","cos"]'],
            [[fn ($x) => $x + 1, 'abs', fn ($y) => $y * $y], '[{},"abs",{}]'],
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
        $arrayOfCallables = new ArrayOfCallables($values);

        // When
        $jsonSerialization = json_encode($arrayOfCallables);

        // Then
        $this->assertSame($expected, $jsonSerialization);
    }

    public function dataProviderForValidValuesJsonStringRepresentation(): array
    {
        return [
            [[fn ($x) => $x + 1], '[{}]'],
            [[fn ($x) => $x + 1, fn ($y) => $y * $y], '[{},{}]'],
            [['abs', 'cos'], '["abs","cos"]'],
            [[fn ($x) => $x + 1, 'abs', fn ($y) => $y * $y], '[{},"abs",{}]'],
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
        $arrayOfCallables = new ArrayOfCallables($values);

        // Then
        $this->assertCount($expectedCount, $arrayOfCallables);
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
        $arrayOfCallables = new ArrayOfCallables($values);

        // And
        $i = -1;
        $finalCount = $expectedCount - 1;

        // When
        foreach ($arrayOfCallables as $bool) {
            $i++;
            $this->assertIsCallable($bool);
            $this->assertSame($values[$i], $bool);
        }

        // Then
        $this->assertSame($finalCount, $i);

        // And when reset the iterator
        $i = -1;
        foreach ($arrayOfCallables as $bool) {
            $i++;
            $this->assertIsCallable($bool);
            $this->assertSame($values[$i], $bool);
        }

        // Then
        $this->assertSame($finalCount, $i);
    }

    public function dataProviderForValidCountedValues(): array
    {
        return [
            [[fn ($x) => $x + 1], 1],
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
        $arrayOfCallables = new ArrayOfCallables($values);
    }

    public function dataProviderForInvalidValues(): array
    {
        return [
            [[]],
            [[1]],
            [[1.1]],
            [['string']],
            [[true]],
            [[[1, 2, 3]]],
            [[null]],
            [[\NAN]],
            [[new \stdClass()]],
        ];
    }
}
