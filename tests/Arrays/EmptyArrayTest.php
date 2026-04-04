<?php

declare(strict_types=1);

namespace StrongType\Tests\Arrays;

use StrongType\Exception\StrongTypeException;
use StrongType\Arrays\EmptyArray;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;

class EmptyArrayTest extends \PHPUnit\Framework\TestCase
{
    #[Test]
    #[DataProvider('dataProviderForValidValues')]
    public function testValidValue(array $values)
    {
        // When
        $emptyArray = new EmptyArray($values);

        // Then
        $this->expectNotToPerformAssertions();
    }

    #[Test]
    #[DataProvider('dataProviderForValidValues')]
    public function testGetValue(array $values)
    {
        // Given
        $emptyArray = new EmptyArray($values);

        // When
        $obtainedValues = $emptyArray->getValues();

        // Then
        $this->assertSame($values, $obtainedValues);
    }

    #[Test]
    #[DataProvider('dataProviderForValidValues')]
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

    #[Test]
    public function testStringRepresentation()
    {
        // Given
        $emptyArray = new EmptyArray([]);

        // When
        $stringRepresentation = (string) $emptyArray;

        // Then
        $this->assertSame('[]', $stringRepresentation);
    }

    #[Test]
    public function testJsonSerialization()
    {
        // Given
        $emptyArray = new EmptyArray([]);

        // When
        $jsonSerialization = \json_encode($emptyArray);

        // Then
        $this->assertSame('[]', $jsonSerialization);
    }

    #[Test]
    public function testCountableInterface()
    {
        // Given
        $emptyArray = new EmptyArray([]);

        // Then
        $this->assertCount(0, $emptyArray);
    }

    #[Test]
    #[DataProvider('dataProviderForInvalidValues')]
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
