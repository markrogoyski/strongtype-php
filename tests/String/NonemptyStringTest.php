<?php

declare(strict_types=1);

namespace StrongType\Tests\String;

use StrongType\Exception\StrongTypeException;
use StrongType\String\NonemptyString;

class NonemptyStringTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @test         Valid value
     * @dataProvider dataProviderForValidValues
     * @param        string $value
     */
    public function testValidValue(string $value)
    {
        // When
        $nonemptyString = new NonemptyString($value);

        // Then
        $this->expectNotToPerformAssertions();
    }

    /**
     * @test         Get value
     * @dataProvider dataProviderForValidValues
     * @param        string $value
     */
    public function testGetValue(string $value)
    {
        // Given
        $nonemptyString = new NonemptyString($value);

        // When
        $obtainedValue = $nonemptyString->getValue();

        // Then
        $this->assertSame($value, $obtainedValue);
    }

    /**
     * @test         Debug info
     * @dataProvider dataProviderForValidValues
     * @param        string $value
     */
    public function testDebugInfo(string $value)
    {
        // Given
        $nonemptyString = new NonemptyString($value);

        // When
        $debugInfo = $nonemptyString->__debugInfo();

        // Then
        $this->assertSame($value, $debugInfo['value']);
    }

    public function dataProviderForValidValues(): array
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

    /**
     * @test         String representation
     * @dataProvider dataProviderForValidValuesStringRepresentation
     * @param        string $value
     * @param        string $expected
     */
    public function testStringRepresentation(string $value, string $expected)
    {
        // Given
        $nonemptyString = new NonemptyString($value);

        // When
        $stringRepresentation = (string) $nonemptyString;

        // Then
        $this->assertSame($expected, $stringRepresentation);
    }

    public function dataProviderForValidValuesStringRepresentation(): array
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

    /**
     * @test         JSON serialization
     * @dataProvider dataProviderForValidValuesJsonStringRepresentation
     * @param        string $value
     * @param        string $expected
     */
    public function testJsonSerialization(string $value, string $expected)
    {
        // Given
        $nonemptyString = new NonemptyString($value);

        // When
        $jsonSerialization = json_encode($nonemptyString);

        // Then
        $this->assertSame($expected, $jsonSerialization);
    }

    public function dataProviderForValidValuesJsonStringRepresentation(): array
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

    /**
     * @test         Invalid value
     * @dataProvider dataProviderForInvalidValues
     * @param        string $value
     */
    public function testInvalidValue(string $value)
    {
        // Then
        $this->expectException(StrongTypeException::class);

        // When
        $nonemptyString = new NonemptyString($value);
    }

    public function dataProviderForInvalidValues(): array
    {
        return [
            [''],
        ];
    }
}
