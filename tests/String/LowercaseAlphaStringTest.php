<?php

declare(strict_types=1);

namespace StrongType\Tests\String;

use StrongType\Exception\StrongTypeException;
use StrongType\String\LowercaseAlphaString;

class LowercaseAlphaStringTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @test         Valid value
     * @dataProvider dataProviderForValidValues
     * @param        string $value
     */
    public function testValidValue(string $value)
    {
        // When
        $lowercaseAlphaString = new LowercaseAlphaString($value);

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
        $lowercaseAlphaString = new LowercaseAlphaString($value);

        // When
        $obtainedValue = $lowercaseAlphaString->getValue();

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
        $lowercaseAlphaString = new LowercaseAlphaString($value);

        // When
        $debugInfo = $lowercaseAlphaString->__debugInfo();

        // Then
        $this->assertSame($value, $debugInfo['value']);
    }

    public function dataProviderForValidValues(): array
    {
        return [
            ['a'],
            ['abcdefghijklmnopqrstuvwxyz'],
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
        $lowercaseAlphaString = new LowercaseAlphaString($value);

        // When
        $stringRepresentation = (string) $lowercaseAlphaString;

        // Then
        $this->assertSame($expected, $stringRepresentation);
    }

    public function dataProviderForValidValuesStringRepresentation(): array
    {
        return [
            ['a', 'a'],
            ['abcdefghijklmnopqrstuvwxyz', 'abcdefghijklmnopqrstuvwxyz'],
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
        $lowercaseAlphaString = new LowercaseAlphaString($value);

        // When
        $jsonSerialization = json_encode($lowercaseAlphaString);

        // Then
        $this->assertSame($expected, $jsonSerialization);
    }

    public function dataProviderForValidValuesJsonStringRepresentation(): array
    {
        return [
            ['a', '"a"'],
            ['abcdefghijklmnopqrstuvwxyz', '"abcdefghijklmnopqrstuvwxyz"'],
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
        $lowercaseAlphaString = new LowercaseAlphaString($value);
    }

    public function dataProviderForInvalidValues(): array
    {
        return [
            [''],
            [' '],
            ['hello, world'],
            ['alpha string'],
            ['ABCDEFGHIJKLMNOPQRSTUVWXYZ'],
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
