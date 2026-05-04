<?php

declare(strict_types=1);

namespace StrongType\Tests\Arrays;

use StrongType\Exception\StrongTypeException;
use StrongType\Arrays\ArrayOfArrays;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;

class ArrayOfArraysTest extends \PHPUnit\Framework\TestCase
{
    #[Test]
    #[DataProvider('dataProviderForValidValues')]
    public function testValidValue(array $values)
    {
        // When
        $arrayOfArrays = new ArrayOfArrays($values);

        // Then
        $this->expectNotToPerformAssertions();
    }

    #[Test]
    #[DataProvider('dataProviderForValidValues')]
    public function testGetValue(array $values)
    {
        // Given
        $arrayOfArrays = new ArrayOfArrays($values);

        // When
        $obtainedValues = $arrayOfArrays->getValues();

        // Then
        $this->assertSame($values, $obtainedValues);

        // And
        foreach ($values as $value) {
            $this->assertIsArray($value);
        }
    }

    #[Test]
    #[DataProvider('dataProviderForValidValues')]
    public function testDebugInfo(array $values)
    {
        // Given
        $arrayOfArrays = new ArrayOfArrays($values);

        // When
        $debugInfo = $arrayOfArrays->__debugInfo();

        // Then
        $this->assertSame($values, $debugInfo['values']);
    }

    public static function dataProviderForValidValues(): array
    {
        return [
            [[[1]]],
            [[[1.1]]],
            [[['string']]],
            [[[true]]],
            [[[null]]],
            [[[\NAN]]],
            [[[new \stdClass()]]],
            [[[1, 2, 3, 4, 5]]],
            [[[1.1, 2.2, 3.3]]],
            [[[false, false]]],
            [[['hello', 'world', 'strong', 'type', 'php']]],
            [[[new \stdClass(), new \stdClass(), new \stdClass()]]],
            [[[1], [2], [3]]],
            [[[1.1, 2.2], [3.3, 4.4]]],
            [[['string'], ['hello', 'world'], ['php', 'strong', 'type']]],
        ];
    }

    #[Test]
    #[DataProvider('dataProviderForValidValuesStringRepresentation')]
    public function testStringRepresentation(array $values, string $expected)
    {
        // Given
        $arrayOfArrays = new ArrayOfArrays($values);

        // When
        $stringRepresentation = (string) $arrayOfArrays;

        // Then
        $this->assertSame($expected, $stringRepresentation);
    }

    public static function dataProviderForValidValuesStringRepresentation(): array
    {
        return [
            [[[1, 2, 3], [3, 4, 5]], '[[1,2,3],[3,4,5]]'],
            [[[1.1, 2.2], [3.3]], '[[1.1,2.2],[3.3]]'],
            [[[false, true],[true, false]], '[[false,true],[true,false]]'],
        ];
    }

    #[Test]
    #[DataProvider('dataProviderForValidValuesJsonStringRepresentation')]
    public function testJsonSerialization(array $values, string $expected)
    {
        // Given
        $arrayOfArrays = new ArrayOfArrays($values);

        // When
        $jsonSerialization = \json_encode($arrayOfArrays);

        // Then
        $this->assertSame($expected, $jsonSerialization);
    }

    public static function dataProviderForValidValuesJsonStringRepresentation(): array
    {
        return [
            [[[1, 2, 3], [3, 4, 5]], '[[1,2,3],[3,4,5]]'],
            [[[1.1, 2.2], [3.3]], '[[1.1,2.2],[3.3]]'],
            [[[false, true],[true, false]], '[[false,true],[true,false]]'],
        ];
    }

    #[Test]
    #[DataProvider('dataProviderForValidCountedValues')]
    public function testCountableInterface(array $values, int $expectedCount)
    {
        // Given
        $arrayOfArrays = new ArrayOfArrays($values);

        // Then
        $this->assertCount($expectedCount, $arrayOfArrays);
    }

    #[Test]
    #[DataProvider('dataProviderForValidCountedValues')]
    public function testIteratorInterface(array $values, int $expectedCount)
    {
        // Given
        $arrayOfArrays = new ArrayOfArrays($values);

        // And
        $i = -1;
        $finalCount = $expectedCount - 1;

        // When
        foreach ($arrayOfArrays as $array) {
            $i++;
            $this->assertIsArray($array);
            $this->assertSame($values[$i], $array);
        }

        // Then
        $this->assertSame($finalCount, $i);

        // And when reset the iterator
        $i = -1;
        foreach ($arrayOfArrays as $array) {
            $i++;
            $this->assertIsArray($array);
            $this->assertSame($values[$i], $array);
        }

        // Then
        $this->assertSame($finalCount, $i);
    }

    public static function dataProviderForValidCountedValues(): array
    {
        return [
            [[[1]], 1],
            [[[1, 2]], 1],
            [[[1, 2, 3], [2, 3, 4], [3, 4, 5]], 3],
            [[['one', 'two', 'three', 'four', 'five', 'six']], 1],
            [[['one', 'two', 'three', 'four', 'five', 'six'], ['two', 'three']], 2],
        ];
    }

    #[Test]
    public function testStringRepresentationTerminatesOnSelfReferentialArray()
    {
        // Given an inner array that contains a reference to itself.
        // ArrayOfArrays validly accepts arrays-of-arrays, including ones with
        // internal cycles. json_encode throws JsonException("Recursion detected"),
        // so the fallback stringifier must terminate cleanly rather than recurse.
        $inner = [];
        $inner[] = &$inner;
        $arr = new ArrayOfArrays([$inner]);

        // When casting to string
        $rendered = (string) $arr;

        // Then __toString must not exhaust the stack and must mark the cycle.
        $this->assertStringContainsString('*RECURSION*', $rendered);
    }

    #[Test]
    public function testStringRepresentationRendersJustUnderDepthGuard()
    {
        // Given a structure one level shy of the depth guard. The fallback's
        // STRINGIFY_MAX_DEPTH is 64, and the leaf reaches stringifyJsonishValue
        // at depth ($levels + 1) — one hop is consumed walking from the outer
        // ArrayType::values wrapper into its first child. $levels = 62 puts
        // the NAN leaf at depth 63, just under the >= 64 trip point. NAN is
        // used because it forces json_encode to throw and hands control to the
        // fallback renderer (the only path under test here).
        $value = \NAN;
        for ($i = 0; $i < 62; $i++) {
            $value = [$value];
        }
        $arr = new ArrayOfArrays([$value]);

        // When casting to string
        $rendered = (string) $arr;

        // Then the leaf renders as 'NaN' and no recursion marker is emitted.
        $this->assertStringNotContainsString('*RECURSION*', $rendered);
        $this->assertStringContainsString('NaN', $rendered);
    }

    #[Test]
    public function testStringRepresentationMarksAtDepthGuard()
    {
        // Given a structure one level past the depth guard: $levels = 63 puts
        // the NAN leaf at depth 64 in stringifyJsonishValue — exactly the >= 64
        // boundary where STRINGIFY_MAX_DEPTH cuts off recursion.
        $value = \NAN;
        for ($i = 0; $i < 63; $i++) {
            $value = [$value];
        }
        $arr = new ArrayOfArrays([$value]);

        // When casting to string
        $rendered = (string) $arr;

        // Then the marker fires for the over-depth leaf and 'NaN' is replaced.
        $this->assertStringContainsString('*RECURSION*', $rendered);
        $this->assertStringNotContainsString('NaN', $rendered);
    }

    #[Test]
    #[DataProvider('dataProviderForInvalidValues')]
    public function testInvalidValue(array $values)
    {
        // Then
        $this->expectException(StrongTypeException::class);

        // When
        $arrayOfArrays = new ArrayOfArrays($values);
    }

    public static function dataProviderForInvalidValues(): array
    {
        return [
            [[]],
            [[1]],
            [[1.1]],
            [['string']],
            [[1, 2, 3]],
            [[null]],
            [[\NAN]],
            [[new \stdClass()]],
        ];
    }
}
