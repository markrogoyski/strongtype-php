<?php

declare(strict_types=1);

namespace StrongType\Tests\String;

use StrongType\Exception\StrongTypeException;
use StrongType\String\SlugString;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;

class SlugStringTest extends \PHPUnit\Framework\TestCase
{
    #[Test]
    #[DataProvider('dataProviderForValidValues')]
    public function testValidValue(string $value)
    {
        // When
        $slugString = new SlugString($value);

        // Then
        $this->expectNotToPerformAssertions();
    }

    #[Test]
    #[DataProvider('dataProviderForValidValues')]
    public function testGetValue(string $value)
    {
        // Given
        $slugString = new SlugString($value);

        // When
        $obtainedValue = $slugString->getValue();

        // Then
        $this->assertSame($value, $obtainedValue);
    }

    #[Test]
    #[DataProvider('dataProviderForValidValues')]
    public function testDebugInfo(string $value)
    {
        // Given
        $slugString = new SlugString($value);

        // When
        $debugInfo = $slugString->__debugInfo();

        // Then
        $this->assertSame($value, $debugInfo['value']);
    }

    public static function dataProviderForValidValues(): array
    {
        return [
            ['hello'],
            ['hello-world'],
            ['my-long-slug-string'],
            ['a'],
            ['123'],
            ['hello123'],
            ['a-1'],
        ];
    }

    #[Test]
    #[DataProvider('dataProviderForValidValuesStringRepresentation')]
    public function testStringRepresentation(string $value, string $expected)
    {
        // Given
        $slugString = new SlugString($value);

        // When
        $stringRepresentation = (string) $slugString;

        // Then
        $this->assertSame($expected, $stringRepresentation);
    }

    #[Test]
    #[DataProvider('dataProviderForValidValuesJsonStringRepresentation')]
    public function testJsonSerialization(string $value, string $expected)
    {
        // Given
        $slugString = new SlugString($value);

        // When
        $jsonSerialization = \json_encode($slugString);

        // Then
        $this->assertSame($expected, $jsonSerialization);
    }

    public static function dataProviderForValidValuesStringRepresentation(): array
    {
        return [
            ['hello', 'hello'],
            ['hello-world', 'hello-world'],
            ['a', 'a'],
        ];
    }

    public static function dataProviderForValidValuesJsonStringRepresentation(): array
    {
        return [
            ['hello', '"hello"'],
            ['hello-world', '"hello-world"'],
            ['a', '"a"'],
        ];
    }

    #[Test]
    #[DataProvider('dataProviderForInvalidValues')]
    public function testInvalidValue(string $value)
    {
        // Then
        $this->expectException(StrongTypeException::class);

        // When
        $slugString = new SlugString($value);
    }

    public static function dataProviderForInvalidValues(): array
    {
        return [
            [''],
            ['Hello'],
            ['hello_world'],
            ['hello world'],
            ['-hello'],
            ['hello-'],
            ['hello--world'],
            ['HELLO'],
            ['hello/world'],
            ["hello-world\n"],
        ];
    }
}
