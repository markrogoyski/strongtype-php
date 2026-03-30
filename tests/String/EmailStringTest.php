<?php

declare(strict_types=1);

namespace StrongType\Tests\String;

use StrongType\Exception\StrongTypeException;
use StrongType\String\EmailString;

class EmailStringTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @test         Valid value
     * @dataProvider dataProviderForValidValues
     * @param        string $value
     */
    public function testValidValue(string $value)
    {
        // When
        $emailString = new EmailString($value);

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
        $emailString = new EmailString($value);

        // When
        $obtainedValue = $emailString->getValue();

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
        $emailString = new EmailString($value);

        // When
        $debugInfo = $emailString->__debugInfo();

        // Then
        $this->assertSame($value, $debugInfo['value']);
    }

    public function dataProviderForValidValues(): array
    {
        return [
            ['test@example.com'],
            ['user.name@domain.org'],
            ['user+tag@example.co.uk'],
            ['a@b.cc'],
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
        $emailString = new EmailString($value);

        // When
        $stringRepresentation = (string) $emailString;

        // Then
        $this->assertSame($expected, $stringRepresentation);
    }

    public function dataProviderForValidValuesStringRepresentation(): array
    {
        return [
            ['test@example.com', 'test@example.com'],
            ['user.name@domain.org', 'user.name@domain.org'],
            ['user+tag@example.co.uk', 'user+tag@example.co.uk'],
            ['a@b.cc', 'a@b.cc'],
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
        $emailString = new EmailString($value);

        // When
        $jsonSerialization = json_encode($emailString);

        // Then
        $this->assertSame($expected, $jsonSerialization);
    }

    public function dataProviderForValidValuesJsonStringRepresentation(): array
    {
        return [
            ['test@example.com', '"test@example.com"'],
            ['user.name@domain.org', '"user.name@domain.org"'],
            ['user+tag@example.co.uk', '"user+tag@example.co.uk"'],
            ['a@b.cc', '"a@b.cc"'],
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
        $emailString = new EmailString($value);
    }

    public function dataProviderForInvalidValues(): array
    {
        return [
            [''],
            ['notanemail'],
            ['@domain.com'],
            ['user@'],
            ['user@.com'],
            ['user@domain'],
            ['@'],
            ['hello, world'],
        ];
    }
}
