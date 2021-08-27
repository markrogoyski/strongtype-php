<?php

declare(strict_types=1);

namespace StrongType\Tests\String;

use StrongType\Exception\StrongTypeException;
use StrongType\String\AlphanumericString;

class AlphanumericStringTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @test         Valid value
     * @dataProvider dataProviderForValidValues
     * @param        string $value
     */
    public function testValidValue(string $value)
    {
        // When
        $alphanumericString = new AlphanumericString($value);

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
        $alphanumericString = new AlphanumericString($value);

        // When
        $obtainedValue = $alphanumericString->getValue();

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
        $alphanumericString = new AlphanumericString($value);

        // When
        $debugInfo = $alphanumericString->__debugInfo();

        // Then
        $this->assertSame($value, $debugInfo['value']);
    }

    public function dataProviderForValidValues(): array
    {
        return [
            ['a'],
            ['1'],
            ['1234567890'],
            ['abcdefghijklmnopqrstuvwxyz'],
            ['ABCDEFGHIJKLMNOPQRSTUVWXYZ'],
            ['abc123'],
            ['456xyz'],
            ['ABC123'],
            ['456XYZ'],
            ['a1B2'],
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
        $alphanumericString = new AlphanumericString($value);

        // When
        $stringRepresentation = (string) $alphanumericString;

        // Then
        $this->assertSame($expected, $stringRepresentation);
    }

    public function dataProviderForValidValuesStringRepresentation(): array
    {
        return [
            ['a', 'a'],
            ['1', '1'],
            ['1234567890', '1234567890'],
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
        $alphanumericString = new AlphanumericString($value);

        // When
        $jsonSerialization = json_encode($alphanumericString);

        // Then
        $this->assertSame($expected, $jsonSerialization);
    }

    public function dataProviderForValidValuesJsonStringRepresentation(): array
    {
        return [
            ['a', '"a"'],
            ['1', '"1"'],
            ['1234567890', '"1234567890"'],
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
        $alphanumericString = new AlphanumericString($value);
    }

    public function dataProviderForInvalidValues(): array
    {
        return [
            [''],
            [' '],
            ['hello, world'],
            ['alpha string'],
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
