<?php

declare(strict_types=1);

namespace StrongType\Tests\String;

use StrongType\Exception\StrongTypeException;
use StrongType\String\BinaryString;

class BinaryStringTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @test         Valid value
     * @dataProvider dataProviderForValidValues
     * @param        string $value
     */
    public function testValidValue(string $value)
    {
        // When
        $binaryString = new BinaryString($value);

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
        $binaryString = new BinaryString($value);

        // When
        $obtainedValue = $binaryString->getValue();

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
        $binaryString = new BinaryString($value);

        // When
        $debugInfo = $binaryString->__debugInfo();

        // Then
        $this->assertSame($value, $debugInfo['value']);
    }

    public function dataProviderForValidValues(): array
    {
        return [
            ['0'],
            ['1'],
            ['001'],
            ['100'],
            ['101'],
            ['111'],
            ['10101010101010101'],
            ['11111111111111111'],
            ['00000000000000000'],
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
        $binaryString = new BinaryString($value);

        // When
        $stringRepresentation = (string) $binaryString;

        // Then
        $this->assertSame($expected, $stringRepresentation);
    }

    public function dataProviderForValidValuesStringRepresentation(): array
    {
        return [
            ['0', '0'],
            ['1', '1'],
            ['001', '001'],
            ['100', '100'],
            ['101', '101'],
            ['111', '111'],
            ['10101010101010101', '10101010101010101'],
            ['11111111111111111', '11111111111111111'],
            ['00000000000000000', '00000000000000000'],
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
        $binaryString = new BinaryString($value);

        // When
        $jsonSerialization = json_encode($binaryString);

        // Then
        $this->assertSame($expected, $jsonSerialization);
    }

    public function dataProviderForValidValuesJsonStringRepresentation(): array
    {
        return [
            ['0', '"0"'],
            ['1', '"1"'],
            ['001', '"001"'],
            ['100', '"100"'],
            ['101', '"101"'],
            ['111', '"111"'],
            ['10101010101010101', '"10101010101010101"'],
            ['11111111111111111', '"11111111111111111"'],
            ['00000000000000000', '"00000000000000000"'],
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
        $binaryString = new BinaryString($value);
    }

    public function dataProviderForInvalidValues(): array
    {
        return [
            [''],
            [' '],
            ['2'],
            ['3'],
            ['4'],
            ['5'],
            ['6'],
            ['7'],
            ['8'],
            ['9'],
            ['a'],
            ['b'],
            ['c'],
            ['abcdefghijklmnopqrstuvwxyz'],
            ['ABCDEFGHIJKLMNOPQRSTUVWXYZ'],
            ['a1B2'],
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
