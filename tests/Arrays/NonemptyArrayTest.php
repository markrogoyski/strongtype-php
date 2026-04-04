<?php

declare(strict_types=1);

namespace StrongType\Tests\Arrays;

use StrongType\Exception\StrongTypeException;
use StrongType\Arrays\NonemptyArray;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;

class NonemptyArrayTest extends \PHPUnit\Framework\TestCase
{
    #[Test]
    #[DataProvider('dataProviderForValidValues')]
    public function testValidValue(array $values)
    {
        // When
        $nonemptyArray = new NonemptyArray($values);

        // Then
        $this->expectNotToPerformAssertions();
    }

    #[Test]
    #[DataProvider('dataProviderForValidValues')]
    public function testGetValue(array $values)
    {
        // Given
        $nonemptyArray = new NonemptyArray($values);

        // When
        $obtainedValues = $nonemptyArray->getValues();

        // Then
        $this->assertSame($values, $obtainedValues);
    }

    #[Test]
    #[DataProvider('dataProviderForValidValues')]
    public function testDebugInfo(array $values)
    {
        // Given
        $nonemptyArray = new NonemptyArray($values);

        // When
        $debugInfo = $nonemptyArray->__debugInfo();

        // Then
        $this->assertSame($values, $debugInfo['values']);
    }

    public static function dataProviderForValidValues(): array
    {
        return [
            [[1]],
            [['a', 'b']],
            [[1, 'mixed', true]],
            [[null]],
        ];
    }

    #[Test]
    #[DataProvider('dataProviderForValidValuesStringRepresentation')]
    public function testStringRepresentation(array $values, string $expected)
    {
        // Given
        $nonemptyArray = new NonemptyArray($values);

        // When
        $stringRepresentation = (string) $nonemptyArray;

        // Then
        $this->assertSame($expected, $stringRepresentation);
    }

    public static function dataProviderForValidValuesStringRepresentation(): array
    {
        return [
            [[1], '[1]'],
            [['a', 'b'], '["a","b"]'],
            [[true, false], '[true,false]'],
            [[null], '[null]'],
        ];
    }

    #[Test]
    #[DataProvider('dataProviderForValidValuesJsonStringRepresentation')]
    public function testJsonSerialization(array $values, string $expected)
    {
        // Given
        $nonemptyArray = new NonemptyArray($values);

        // When
        $jsonSerialization = \json_encode($nonemptyArray);

        // Then
        $this->assertSame($expected, $jsonSerialization);
    }

    public static function dataProviderForValidValuesJsonStringRepresentation(): array
    {
        return [
            [[1], '[1]'],
            [['a', 'b'], '["a","b"]'],
            [[true, false], '[true,false]'],
            [[null], '[null]'],
        ];
    }

    #[Test]
    #[DataProvider('dataProviderForValidCountedValues')]
    public function testCountableInterface(array $values, int $expectedCount)
    {
        // Given
        $nonemptyArray = new NonemptyArray($values);

        // Then
        $this->assertCount($expectedCount, $nonemptyArray);
    }

    #[Test]
    #[DataProvider('dataProviderForValidCountedValues')]
    public function testIteratorInterface(array $values, int $expectedCount)
    {
        // Given
        $nonemptyArray = new NonemptyArray($values);

        // And
        $i = -1;
        $finalCount = $expectedCount - 1;

        // When
        foreach ($nonemptyArray as $value) {
            $i++;
            $this->assertSame($values[$i], $value);
        }

        // Then
        $this->assertSame($finalCount, $i);

        // And when reset the iterator
        $i = -1;
        foreach ($nonemptyArray as $value) {
            $i++;
            $this->assertSame($values[$i], $value);
        }

        // Then
        $this->assertSame($finalCount, $i);
    }

    public static function dataProviderForValidCountedValues(): array
    {
        return [
            [[1], 1],
            [['a', 'b'], 2],
            [[1, 'mixed', true], 3],
            [[null], 1],
        ];
    }

    #[Test]
    #[DataProvider('dataProviderForInvalidValues')]
    public function testInvalidValue(array $values)
    {
        // Then
        $this->expectException(StrongTypeException::class);

        // When
        $nonemptyArray = new NonemptyArray($values);
    }

    public static function dataProviderForInvalidValues(): array
    {
        return [
            [[]],
        ];
    }
}
