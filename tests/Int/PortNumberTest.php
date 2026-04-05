<?php

declare(strict_types=1);

namespace StrongType\Tests\Int;

use StrongType\Exception\StrongTypeException;
use StrongType\Int\PortNumber;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;

class PortNumberTest extends \PHPUnit\Framework\TestCase
{
    #[Test]
    #[DataProvider('dataProviderForValidValues')]
    public function testValidValue(int $value)
    {
        // When
        $portNumber = new PortNumber($value);

        // Then
        $this->expectNotToPerformAssertions();
    }

    #[Test]
    #[DataProvider('dataProviderForValidValues')]
    public function testGetValue(int $value)
    {
        // Given
        $portNumber = new PortNumber($value);

        // When
        $obtainedValue = $portNumber->getValue();

        // Then
        $this->assertSame($value, $obtainedValue);
    }

    #[Test]
    #[DataProvider('dataProviderForValidValues')]
    public function testDebugInfo(int $value)
    {
        // Given
        $portNumber = new PortNumber($value);

        // When
        $debugInfo = $portNumber->__debugInfo();

        // Then
        $this->assertSame($value, $debugInfo['value']);
    }

    public static function dataProviderForValidValues(): array
    {
        return [
            [1],
            [22],
            [80],
            [443],
            [8080],
            [65535],
        ];
    }

    #[Test]
    #[DataProvider('dataProviderForValidValuesStringRepresentation')]
    public function testStringRepresentation(int $value, string $expected)
    {
        // Given
        $portNumber = new PortNumber($value);

        // When
        $stringRepresentation = (string) $portNumber;

        // Then
        $this->assertSame($expected, $stringRepresentation);
    }

    #[Test]
    #[DataProvider('dataProviderForValidValuesStringRepresentation')]
    public function testJsonSerialization(int $value, string $expected)
    {
        // Given
        $portNumber = new PortNumber($value);

        // When
        $jsonSerialization = \json_encode($portNumber);

        // Then
        $this->assertSame($expected, $jsonSerialization);
    }

    public static function dataProviderForValidValuesStringRepresentation(): array
    {
        return [
            [1, '1'],
            [22, '22'],
            [80, '80'],
            [443, '443'],
            [8080, '8080'],
            [65535, '65535'],
        ];
    }

    #[Test]
    #[DataProvider('dataProviderForInvalidValues')]
    public function testInvalidValue(int $value)
    {
        // Then
        $this->expectException(StrongTypeException::class);

        // When
        $portNumber = new PortNumber($value);
    }

    public static function dataProviderForInvalidValues(): array
    {
        return [
            [0],
            [-1],
            [-100],
            [65536],
            [100000],
            [\PHP_INT_MIN],
            [\PHP_INT_MAX],
        ];
    }
}
