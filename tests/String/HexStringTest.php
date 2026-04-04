<?php

declare(strict_types=1);

namespace StrongType\Tests\String;

use StrongType\Exception\StrongTypeException;
use StrongType\String\HexString;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;

class HexStringTest extends \PHPUnit\Framework\TestCase
{
    #[Test]
    #[DataProvider('dataProviderForValidValues')]
    public function testValidValue(string $value)
    {
        // When
        $hexString = new HexString($value);

        // Then
        $this->expectNotToPerformAssertions();
    }

    #[Test]
    #[DataProvider('dataProviderForValidValues')]
    public function testGetValue(string $value)
    {
        // Given
        $hexString = new HexString($value);

        // When
        $obtainedValue = $hexString->getValue();

        // Then
        $this->assertSame($value, $obtainedValue);
    }

    #[Test]
    #[DataProvider('dataProviderForValidValues')]
    public function testDebugInfo(string $value)
    {
        // Given
        $hexString = new HexString($value);

        // When
        $debugInfo = $hexString->__debugInfo();

        // Then
        $this->assertSame($value, $debugInfo['value']);
    }

    public static function dataProviderForValidValues(): array
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

    #[Test]
    #[DataProvider('dataProviderForValidValuesStringRepresentation')]
    public function testStringRepresentation(string $value, string $expected)
    {
        // Given
        $hexString = new HexString($value);

        // When
        $stringRepresentation = (string) $hexString;

        // Then
        $this->assertSame($expected, $stringRepresentation);
    }

    public static function dataProviderForValidValuesStringRepresentation(): array
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

    #[Test]
    #[DataProvider('dataProviderForValidValuesJsonStringRepresentation')]
    public function testJsonSerialization(string $value, string $expected)
    {
        // Given
        $hexString = new HexString($value);

        // When
        $jsonSerialization = \json_encode($hexString);

        // Then
        $this->assertSame($expected, $jsonSerialization);
    }

    public static function dataProviderForValidValuesJsonStringRepresentation(): array
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

    #[Test]
    #[DataProvider('dataProviderForInvalidValues')]
    public function testInvalidValue(string $value)
    {
        // Then
        $this->expectException(StrongTypeException::class);

        // When
        $hexString = new HexString($value);
    }

    public static function dataProviderForInvalidValues(): array
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
