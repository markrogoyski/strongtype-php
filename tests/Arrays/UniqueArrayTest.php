<?php

declare(strict_types=1);

namespace StrongType\Tests\Arrays;

use StrongType\Exception\StrongTypeException;
use StrongType\Arrays\UniqueArray;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;

class UniqueArrayTest extends \PHPUnit\Framework\TestCase
{
    #[Test]
    #[DataProvider('dataProviderForValidValues')]
    public function testValidValue(array $values)
    {
        // When
        $uniqueArray = new UniqueArray($values);

        // Then
        $this->expectNotToPerformAssertions();
    }

    #[Test]
    #[DataProvider('dataProviderForValidValues')]
    public function testGetValue(array $values)
    {
        // Given
        $uniqueArray = new UniqueArray($values);

        // When
        $obtainedValues = $uniqueArray->getValues();

        // Then
        $this->assertSame($values, $obtainedValues);
    }

    #[Test]
    #[DataProvider('dataProviderForValidValues')]
    public function testDebugInfo(array $values)
    {
        // Given
        $uniqueArray = new UniqueArray($values);

        // When
        $debugInfo = $uniqueArray->__debugInfo();

        // Then
        $this->assertSame($values, $debugInfo['values']);
    }

    public static function dataProviderForValidValues(): array
    {
        return [
            [[1]],
            [[1, 2, 3]],
            [['a', 'b', 'c']],
            [['a', 'b', 'c', 'd']],
            [[1, 2, 3, 4, 5]],
            // Strict equality: loosely-equal scalars are *distinct* values
            // (SORT_REGULAR-based array_unique would have collapsed these).
            [[0, '0']],
            [[1, '1']],
            [[true, 1]],
            [[false, 0, '0', null]],
        ];
    }

    #[Test]
    #[DataProvider('dataProviderForValidValuesStringRepresentation')]
    public function testStringRepresentation(array $values, string $expected)
    {
        // Given
        $uniqueArray = new UniqueArray($values);

        // When
        $stringRepresentation = (string) $uniqueArray;

        // Then
        $this->assertSame($expected, $stringRepresentation);
    }

    #[Test]
    #[DataProvider('dataProviderForValidValuesJsonStringRepresentation')]
    public function testJsonSerialization(array $values, string $expected)
    {
        // Given
        $uniqueArray = new UniqueArray($values);

        // When
        $jsonSerialization = \json_encode($uniqueArray);

        // Then
        $this->assertSame($expected, $jsonSerialization);
    }

    public static function dataProviderForValidValuesStringRepresentation(): array
    {
        return [
            [[1], '[1]'],
            [[1, 2, 3], '[1,2,3]'],
            [['a', 'b'], '["a","b"]'],
        ];
    }

    public static function dataProviderForValidValuesJsonStringRepresentation(): array
    {
        return [
            [[1], '[1]'],
            [[1, 2, 3], '[1,2,3]'],
            [['a', 'b'], '["a","b"]'],
        ];
    }

    #[Test]
    #[DataProvider('dataProviderForInvalidValues')]
    public function testInvalidValue(array $values)
    {
        // Then
        $this->expectException(StrongTypeException::class);

        // When
        $uniqueArray = new UniqueArray($values);
    }

    public static function dataProviderForInvalidValues(): array
    {
        return [
            [[]],
            [[1, 1]],
            [['a', 'a']],
            [[1, 2, 3, 1]],
            [['hello', 'world', 'hello']],
            // Two NaN floats are duplicates: NAN === NAN is false, but they
            // are indistinguishable as values for uniqueness purposes.
            [[\NAN, \NAN]],
        ];
    }
}
