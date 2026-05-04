<?php

declare(strict_types=1);

namespace StrongType\Tests\String;

use StrongType\Exception\StrongTypeException;
use StrongType\String\BinaryString;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;

class BinaryStringTest extends \PHPUnit\Framework\TestCase
{
    #[Test]
    #[DataProvider('dataProviderForValidValues')]
    public function testValidValue(string $value)
    {
        // When
        $binaryString = new BinaryString($value);

        // Then
        $this->expectNotToPerformAssertions();
    }

    #[Test]
    #[DataProvider('dataProviderForValidValues')]
    public function testGetValue(string $value)
    {
        // Given
        $binaryString = new BinaryString($value);

        // When
        $obtainedValue = $binaryString->getValue();

        // Then
        $this->assertSame($value, $obtainedValue);
    }

    #[Test]
    #[DataProvider('dataProviderForValidValues')]
    public function testDebugInfo(string $value)
    {
        // Given
        $binaryString = new BinaryString($value);

        // When
        $debugInfo = $binaryString->__debugInfo();

        // Then
        $this->assertSame($value, $debugInfo['value']);
    }

    public static function dataProviderForValidValues(): array
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

    #[Test]
    #[DataProvider('dataProviderForValidValuesStringRepresentation')]
    public function testStringRepresentation(string $value, string $expected)
    {
        // Given
        $binaryString = new BinaryString($value);

        // When
        $stringRepresentation = (string) $binaryString;

        // Then
        $this->assertSame($expected, $stringRepresentation);
    }

    public static function dataProviderForValidValuesStringRepresentation(): array
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

    #[Test]
    #[DataProvider('dataProviderForValidValuesJsonStringRepresentation')]
    public function testJsonSerialization(string $value, string $expected)
    {
        // Given
        $binaryString = new BinaryString($value);

        // When
        $jsonSerialization = \json_encode($binaryString);

        // Then
        $this->assertSame($expected, $jsonSerialization);
    }

    public static function dataProviderForValidValuesJsonStringRepresentation(): array
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

    #[Test]
    #[DataProvider('dataProviderForInvalidValues')]
    public function testInvalidValue(string $value)
    {
        // Then
        $this->expectException(StrongTypeException::class);

        // When
        $binaryString = new BinaryString($value);
    }

    public static function dataProviderForInvalidValues(): array
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
            ["1010\n"],
        ];
    }
}
