<?php

declare(strict_types=1);

namespace StrongType\Tests\Int;

use StrongType\Exception\StrongTypeException;
use StrongType\Int\ByteInt;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;

class ByteIntTest extends \PHPUnit\Framework\TestCase
{
    #[Test]
    #[DataProvider('dataProviderForValidValues')]
    public function testValidValue(int $value)
    {
        // When
        $byteInt = new ByteInt($value);

        // Then
        $this->expectNotToPerformAssertions();
    }

    #[Test]
    #[DataProvider('dataProviderForValidValues')]
    public function testGetValue(int $value)
    {
        // Given
        $byteInt = new ByteInt($value);

        // When
        $obtainedValue = $byteInt->getValue();

        // Then
        $this->assertSame($value, $obtainedValue);
    }

    #[Test]
    #[DataProvider('dataProviderForValidValues')]
    public function testDebugInfo(int $value)
    {
        // Given
        $byteInt = new ByteInt($value);

        // When
        $debugInfo = $byteInt->__debugInfo();

        // Then
        $this->assertSame($value, $debugInfo['value']);
    }

    public static function dataProviderForValidValues(): array
    {
        return [
            [0],
            [1],
            [127],
            [128],
            [255],
        ];
    }

    #[Test]
    #[DataProvider('dataProviderForValidValuesStringRepresentation')]
    public function testStringRepresentation(int $value, string $expected)
    {
        // Given
        $byteInt = new ByteInt($value);

        // When
        $stringRepresentation = (string) $byteInt;

        // Then
        $this->assertSame($expected, $stringRepresentation);
    }

    #[Test]
    #[DataProvider('dataProviderForValidValuesStringRepresentation')]
    public function testJsonSerialization(int $value, string $expected)
    {
        // Given
        $byteInt = new ByteInt($value);

        // When
        $jsonSerialization = \json_encode($byteInt);

        // Then
        $this->assertSame($expected, $jsonSerialization);
    }

    public static function dataProviderForValidValuesStringRepresentation(): array
    {
        return [
            [0, '0'],
            [1, '1'],
            [127, '127'],
            [128, '128'],
            [255, '255'],
        ];
    }

    #[Test]
    #[DataProvider('dataProviderForInvalidValues')]
    public function testInvalidValue(int $value)
    {
        // Then
        $this->expectException(StrongTypeException::class);

        // When
        $byteInt = new ByteInt($value);
    }

    public static function dataProviderForInvalidValues(): array
    {
        return [
            [-1],
            [-255],
            [256],
            [1000],
            [\PHP_INT_MIN],
            [\PHP_INT_MAX],
        ];
    }
}
