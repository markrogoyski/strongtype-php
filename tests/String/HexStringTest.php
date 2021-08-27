<?php

declare(strict_types=1);

namespace StrongType\Tests\String;

use StrongType\Exception\StrongTypeException;
use StrongType\String\HexString;

class HexStringTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @test         Valid value
     * @dataProvider dataProviderForValidValues
     * @param        string $value
     */
    public function testValidValue(string $value)
    {
        // When
        $hexString = new HexString($value);

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
        $hexString = new HexString($value);

        // When
        $obtainedValue = $hexString->getValue();

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
        $hexString = new HexString($value);

        // When
        $debugInfo = $hexString->__debugInfo();

        // Then
        $this->assertSame($value, $debugInfo['value']);
    }

    public function dataProviderForValidValues(): array
    {
        return [
            ['a'],
            ['5'],
            ['A'],
            ['0'],
            ['AF493C'],
            ['abcdef'],
            ['ABCDEF'],
            ['1234567890'],
            ['abcdef1234567890'],
            ['1234567890abcdef'],
            ['ABCDEF1234567890'],
            ['1234567890ABCDEF'],
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
        $hexString = new HexString($value);

        // When
        $stringRepresentation = (string) $hexString;

        // Then
        $this->assertSame($expected, $stringRepresentation);
    }

    public function dataProviderForValidValuesStringRepresentation(): array
    {
        return [
            ['a', 'a'],
            ['5', '5'],
            ['A', 'A'],
            ['0', '0'],
            ['AF493C', 'AF493C'],
            ['abcdef', 'abcdef'],
            ['ABCDEF', 'ABCDEF'],
            ['1234567890', '1234567890'],
            ['abcdef1234567890', 'abcdef1234567890'],
            ['1234567890abcdef', '1234567890abcdef'],
            ['ABCDEF1234567890', 'ABCDEF1234567890'],
            ['1234567890ABCDEF', '1234567890ABCDEF'],
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
        $hexString = new HexString($value);

        // When
        $jsonSerialization = json_encode($hexString);

        // Then
        $this->assertSame($expected, $jsonSerialization);
    }

    public function dataProviderForValidValuesJsonStringRepresentation(): array
    {
        return [
            ['a', '"a"'],
            ['5', '"5"'],
            ['A', '"A"'],
            ['0', '"0"'],
            ['AF493C', '"AF493C"'],
            ['abcdef', '"abcdef"'],
            ['ABCDEF', '"ABCDEF"'],
            ['1234567890', '"1234567890"'],
            ['abcdef1234567890', '"abcdef1234567890"'],
            ['1234567890abcdef', '"1234567890abcdef"'],
            ['ABCDEF1234567890', '"ABCDEF1234567890"'],
            ['1234567890ABCDEF', '"1234567890ABCDEF"'],
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
        $hexString = new HexString($value);
    }

    public function dataProviderForInvalidValues(): array
    {
        return [
            [''],
            [' '],
            ['hello, world'],
            ['alpha string'],
            ['456xyz'],
            ['456XYZ'],
            ['ghijklmnopqrstuvwxyz'],
            ['GHIJKLMNOPQRSTUVWXYZ'],
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
