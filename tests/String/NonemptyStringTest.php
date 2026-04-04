<?php

declare(strict_types=1);

namespace StrongType\Tests\String;

use StrongType\Exception\StrongTypeException;
use StrongType\String\NonemptyString;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;

class NonemptyStringTest extends \PHPUnit\Framework\TestCase
{
    #[Test]
    #[DataProvider('dataProviderForValidValues')]
    public function testValidValue(string $value)
    {
        // When
        $nonemptyString = new NonemptyString($value);

        // Then
        $this->expectNotToPerformAssertions();
    }

    #[Test]
    #[DataProvider('dataProviderForValidValues')]
    public function testGetValue(string $value)
    {
        // Given
        $nonemptyString = new NonemptyString($value);

        // When
        $obtainedValue = $nonemptyString->getValue();

        // Then
        $this->assertSame($value, $obtainedValue);
    }

    #[Test]
    #[DataProvider('dataProviderForValidValues')]
    public function testDebugInfo(string $value)
    {
        // Given
        $nonemptyString = new NonemptyString($value);

        // When
        $debugInfo = $nonemptyString->__debugInfo();

        // Then
        $this->assertSame($value, $debugInfo['value']);
    }

    public static function dataProviderForValidValues(): array
    {
        return [
            [' '],
            ['a'],
            ['1'],
            ['nonempty string'],
            ['_'],
            ['日本語'],
            ['()'],
            ['[]'],
            ['{}'],
            ['`\~'],
            ['<>'],
            ['.'],
            [','],
            ['/'],
            ['Hello, world!'],
        ];
    }

    #[Test]
    #[DataProvider('dataProviderForValidValuesStringRepresentation')]
    public function testStringRepresentation(string $value, string $expected)
    {
        // Given
        $nonemptyString = new NonemptyString($value);

        // When
        $stringRepresentation = (string) $nonemptyString;

        // Then
        $this->assertSame($expected, $stringRepresentation);
    }

    public static function dataProviderForValidValuesStringRepresentation(): array
    {
        return [
            [' ', ' '],
            ['a', 'a'],
            ['1', '1'],
            ['nonempty string', 'nonempty string'],
            ['_', '_'],
            ['日本語', '日本語'],
            ['()', '()'],
            ['[]', '[]'],
            ['{}', '{}'],
            ['`\~', '`\~'],
            ['<>', '<>'],
            ['.', '.'],
            [',', ','],
            ['/', '/'],
            ['Hello, world!', 'Hello, world!'],
        ];
    }

    #[Test]
    #[DataProvider('dataProviderForValidValuesJsonStringRepresentation')]
    public function testJsonSerialization(string $value, string $expected)
    {
        // Given
        $nonemptyString = new NonemptyString($value);

        // When
        $jsonSerialization = \json_encode($nonemptyString);

        // Then
        $this->assertSame($expected, $jsonSerialization);
    }

    public static function dataProviderForValidValuesJsonStringRepresentation(): array
    {
        return [
            [' ', '" "'],
            ['a', '"a"'],
            ['1', '"1"'],
            ['nonempty string', '"nonempty string"'],
            ['_', '"_"'],
            ['日本語', '"\u65e5\u672c\u8a9e"'],
            ['()', '"()"'],
            ['[]', '"[]"'],
            ['{}', '"{}"'],
            ['`\~', '"`\\\~"'],
            ['<>', '"<>"'],
            ['.', '"."'],
            [',', '","'],
            ['/', '"\/"'],
            ['Hello, world!', '"Hello, world!"'],
        ];
    }

    #[Test]
    #[DataProvider('dataProviderForInvalidValues')]
    public function testInvalidValue(string $value)
    {
        // Then
        $this->expectException(StrongTypeException::class);

        // When
        $nonemptyString = new NonemptyString($value);
    }

    public static function dataProviderForInvalidValues(): array
    {
        return [
            [''],
        ];
    }
}
