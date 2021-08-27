<?php

declare(strict_types=1);

namespace StrongType\Tests\String;

use StrongType\Exception\StrongTypeException;
use StrongType\String\AlphaString;

class AlphaStringTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @test         Valid value
     * @dataProvider dataProviderForValidValues
     * @param        string $value
     */
    public function testValidValue(string $value)
    {
        // When
        $alphaString = new AlphaString($value);

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
        $alphaString = new AlphaString($value);

        // When
        $obtainedValue = $alphaString->getValue();

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
        $alphaString = new AlphaString($value);

        // When
        $debugInfo = $alphaString->__debugInfo();

        // Then
        $this->assertSame($value, $debugInfo['value']);
    }

    public function dataProviderForValidValues(): array
    {
        return [
            ['a'],
            ['abcdefghijklmnopqrstuvwxyz'],
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
        $alphaString = new AlphaString($value);

        // When
        $stringRepresentation = (string) $alphaString;

        // Then
        $this->assertSame($expected, $stringRepresentation);
    }

    public function dataProviderForValidValuesStringRepresentation(): array
    {
        return [
            ['a', 'a'],
            ['abcdefghijklmnopqrstuvwxyz', 'abcdefghijklmnopqrstuvwxyz'],
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
        $alphaString = new AlphaString($value);

        // When
        $jsonSerialization = json_encode($alphaString);

        // Then
        $this->assertSame($expected, $jsonSerialization);
    }

    public function dataProviderForValidValuesJsonStringRepresentation(): array
    {
        return [
            ['a', '"a"'],
            ['abcdefghijklmnopqrstuvwxyz', '"abcdefghijklmnopqrstuvwxyz"'],
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
        $alphaString = new AlphaString($value);
    }

    public function dataProviderForInvalidValues(): array
    {
        return [
            [''],
            [' '],
            ['hello, world'],
            ['alpha string'],
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
