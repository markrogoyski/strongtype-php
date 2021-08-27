<?php

declare(strict_types=1);

namespace StrongType\Tests\String;

use StrongType\Exception\StrongTypeException;
use StrongType\String\UppercaseAlphaString;

class UppercaseAlphaStringTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @test         Valid value
     * @dataProvider dataProviderForValidValues
     * @param        string $value
     */
    public function testValidValue(string $value)
    {
        // When
        $uppercaseAlphaString = new UppercaseAlphaString($value);

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
        $uppercaseAlphaString = new UppercaseAlphaString($value);

        // When
        $obtainedValue = $uppercaseAlphaString->getValue();

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
        $uppercaseAlphaString = new UppercaseAlphaString($value);

        // When
        $debugInfo = $uppercaseAlphaString->__debugInfo();

        // Then
        $this->assertSame($value, $debugInfo['value']);
    }

    public function dataProviderForValidValues(): array
    {
        return [
            ['A'],
            ['ABCDEFGHIJKLMNOPQRSTUVWXYZ'],
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
        $uppercaseAlphaString = new UppercaseAlphaString($value);

        // When
        $stringRepresentation = (string) $uppercaseAlphaString;

        // Then
        $this->assertSame($expected, $stringRepresentation);
    }

    public function dataProviderForValidValuesStringRepresentation(): array
    {
        return [
            ['A', 'A'],
            ['ABCDEFGHIJKLMNOPQRSTUVWXYZ', 'ABCDEFGHIJKLMNOPQRSTUVWXYZ'],
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
        $uppercaseAlphaString = new UppercaseAlphaString($value);

        // When
        $jsonSerialization = json_encode($uppercaseAlphaString);

        // Then
        $this->assertSame($expected, $jsonSerialization);
    }

    public function dataProviderForValidValuesJsonStringRepresentation(): array
    {
        return [
            ['A', '"A"'],
            ['ABCDEFGHIJKLMNOPQRSTUVWXYZ', '"ABCDEFGHIJKLMNOPQRSTUVWXYZ"'],
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
        $uppercaseAlphaString = new UppercaseAlphaString($value);
    }

    public function dataProviderForInvalidValues(): array
    {
        return [
            [''],
            [' '],
            ['hello, world'],
            ['alpha string'],
            ['abcdefghijklmnopqrstuvwxyz'],
            ['abc123'],
            ['456xyz'],
            ['ABC123'],
            ['456XYZ'],
            ['a1B2'],
            ['1'],
            ['2'],
            ['3'],
            ['4'],
            ['5'],
            ['6'],
            ['7'],
            ['8'],
            ['9'],
            ['0'],
            ['`'],
            ['~'],
            ['!'],
            ['@'],
            ['#'],
            ['$'],
            ['%'],
            ['^'],
            ['&'],
            ['*'],
            ['('],
            [')'],
            ['-'],
            ['_'],
            ['='],
            ['+'],
            ['['],
            ['{'],
            [']'],
            ['}'],
            ['\\'],
            ['|'],
            [';'],
            [':'],
            ['\''],
            ['"'],
            [','],
            ['<'],
            ['.'],
            ['>'],
            ['/'],
            ['?'],
        ];
    }
}
