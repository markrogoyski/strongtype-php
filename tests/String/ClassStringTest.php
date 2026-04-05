<?php

declare(strict_types=1);

namespace StrongType\Tests\String;

use StrongType\Exception\StrongTypeException;
use StrongType\String\ClassString;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;

class ClassStringTest extends \PHPUnit\Framework\TestCase
{
    #[Test]
    #[DataProvider('dataProviderForValidValues')]
    public function testValidValue(string $value)
    {
        // When
        $classString = new ClassString($value);

        // Then
        $this->expectNotToPerformAssertions();
    }

    #[Test]
    #[DataProvider('dataProviderForValidValues')]
    public function testGetValue(string $value)
    {
        // Given
        $classString = new ClassString($value);

        // When
        $obtainedValue = $classString->getValue();

        // Then
        $this->assertSame($value, $obtainedValue);
    }

    #[Test]
    #[DataProvider('dataProviderForValidValues')]
    public function testDebugInfo(string $value)
    {
        // Given
        $classString = new ClassString($value);

        // When
        $debugInfo = $classString->__debugInfo();

        // Then
        $this->assertSame($value, $debugInfo['value']);
    }

    public static function dataProviderForValidValues(): array
    {
        return [
            [\stdClass::class],
            [\JsonSerializable::class],
            [\DateTimeImmutable::class],
            [\Stringable::class],
            [\Iterator::class],
        ];
    }

    #[Test]
    #[DataProvider('dataProviderForValidValuesStringRepresentation')]
    public function testStringRepresentation(string $value, string $expected)
    {
        // Given
        $classString = new ClassString($value);

        // When
        $stringRepresentation = (string) $classString;

        // Then
        $this->assertSame($expected, $stringRepresentation);
    }

    #[Test]
    #[DataProvider('dataProviderForValidValuesJsonStringRepresentation')]
    public function testJsonSerialization(string $value, string $expected)
    {
        // Given
        $classString = new ClassString($value);

        // When
        $jsonSerialization = \json_encode($classString);

        // Then
        $this->assertSame($expected, $jsonSerialization);
    }

    public static function dataProviderForValidValuesStringRepresentation(): array
    {
        return [
            [\stdClass::class, 'stdClass'],
            [\DateTimeImmutable::class, 'DateTimeImmutable'],
        ];
    }

    public static function dataProviderForValidValuesJsonStringRepresentation(): array
    {
        return [
            [\stdClass::class, '"stdClass"'],
            [\DateTimeImmutable::class, '"DateTimeImmutable"'],
        ];
    }

    #[Test]
    #[DataProvider('dataProviderForInvalidValues')]
    public function testInvalidValue(string $value)
    {
        // Then
        $this->expectException(StrongTypeException::class);

        // When
        $classString = new ClassString($value);
    }

    public static function dataProviderForInvalidValues(): array
    {
        return [
            ['NonExistentClass'],
            ['Not\\A\\Real\\Class'],
            ['hello'],
            ['123'],
        ];
    }
}
